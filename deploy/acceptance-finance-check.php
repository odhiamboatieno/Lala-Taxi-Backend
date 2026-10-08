<?php

/** Real retained QA charges, real domain services/PDF/SMTP; never production. */
require __DIR__.'/acceptance-http-check.php';

use App\Models\Company;
use App\Models\Trips;
use App\Services\Corporate\CorporateInvoiceDelivery;
use App\Services\Corporate\CorporateInvoiceGenerator;
use App\Services\Corporate\CorporateInvoiceLifecycle;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

$generator = app(CorporateInvoiceGenerator::class);
$lifecycle = app(CorporateInvoiceLifecycle::class);
$mailpit = new \GuzzleHttp\Client(['base_uri' => 'http://mailpit:8025', 'timeout' => 20]);
$rejected = function (callable $operation, string $label) use ($expect): void {
    try {
        $operation();
    } catch (ValidationException|\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
        $expect(true, $label);
        return;
    }
    $expect(false, $label);
};
$gatewayCount = DB::table('payment_transactions')->count();
foreach ([['Mwangaza Workspaces Kenya', '700000112', '700000140'], ['Tawi Coast Supplies', '700000122', '700000141']] as [$name, $finance, $driver]) {
    $account = CompanyCorporateAccount::where('account_name', $name)->firstOrFail();
    $expect(str_ends_with($account->contact_email, '@acceptance.lalataxi.test'), 'Invoice recipient is a fictional isolated inbox address');
    $charge = Trips::where('corporate_account_id', $account->id)->where('billing_status', 'unbilled')->whereNotNull('billing_finalized_at')->firstOrFail();
    $periodStart = CarbonImmutable::parse($charge->completed_at)->setTimezone($account->billing_timezone)->startOfMonth();
    $periodEnd = $periodStart->addMonth();
    $owner = Company::findOrFail($account->company_id);
    $foreignOwner = Company::where('id', '!=', $owner->id)->firstOrFail();
    // HTTP password/session authentication was checked above. These domain
    // operations run in an explicit company-owner context, not actingAs/mocks.
    app()->instance('request', \Illuminate\Http\Request::create('/company/saas-dispatch/accounts'));
    auth('company')->setUser($owner);
    if ($periodEnd->isFuture()) {
        $rejected(fn () => $generator->generate($account, $periodStart->toDateString(), false, $owner), 'Real-time generation rejects the still-open billing period');
    }
    // Only this isolated CLI process advances its clock to close the month.
    // The server/DB/device clocks and retained trip timestamps are untouched.
    // This is controlled-clock finance acceptance, not a natural month-end run.
    $clock = $periodEnd->addHour();
    Carbon::setTestNow($clock);
    CarbonImmutable::setTestNow($clock);
    try {
        $invoice = $generator->generate($account, $periodStart->toDateString(), false, $owner);
        $expect($invoice !== null && $invoice->status === 'Draft' && $invoice->items->isNotEmpty(), 'Saved actual charges generate a retained trip-backed invoice');
        $expect($generator->generate($account, $periodStart->toDateString(), false, $owner) === null, 'Repeated generation cannot allocate the same trip twice');
        $itemCount = $invoice->items->count();
        $rejected(fn () => $lifecycle->send($invoice, $foreignOwner), 'Another operator cannot issue the invoice');
        $voided = $lifecycle->void($invoice, $owner, 'Isolated acceptance verifies retained void/re-invoice');
        $expect($voided->status === 'Void' && $voided->items()->count() === $itemCount, 'Voiding retains the original invoice and items');
        $invoice = $generator->generate($account, $periodStart->toDateString(), false, $owner);
        $expect($invoice->id !== $voided->id && $invoice->revision === $voided->revision + 1, 'Released charges receive a new invoice revision');
        $invoice = $lifecycle->send($invoice, $owner);
        $lifecycle->send($invoice, $owner);
        $outbox = DB::table('company_corporate_invoice_deliveries')->where('invoice_id', $invoice->id)->sole();
        $expect(app(CorporateInvoiceDelivery::class)->deliver($outbox->id), 'Real PDF renders and SMTP transport accepts invoice');
        $expect(!app(CorporateInvoiceDelivery::class)->deliver($outbox->id), 'Delivered outbox retry sends no second message');
        $expect(DB::table('company_corporate_invoice_deliveries')->where('id', $outbox->id)->value('status') === 'delivered', 'Durable delivery evidence is retained');
        $inbox = json_decode((string) $mailpit->get('/api/v1/messages')->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $message = collect($inbox['messages'])->first(fn ($message) => str_contains($message['Subject'], $invoice->invoice_number));
        $expect($message !== null, 'Invoice is observed in non-relaying Mailpit');
        $detail = json_decode((string) $mailpit->get('/api/v1/message/'.$message['ID'])->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $expect(collect($detail['Attachments'] ?? [])->contains(fn ($attachment) => ($attachment['ContentType'] ?? '') === 'application/pdf' && ($attachment['Size'] ?? 0) > 100), 'Observed SMTP message contains the generated PDF attachment');
        [$status, $visible] = $json('GET', '/api/corporate/invoices/'.$invoice->id, ['headers' => ['Authorization' => 'Bearer '.$tokens[$finance]]]);
        $expect($status === 200 && ($visible['status_code'] ?? null) === '1', 'Finance JWT can read the actually issued invoice');
        [$status, $pending] = $json('GET', '/api/get_driver_checkout_summary', ['headers' => ['Authorization' => 'Bearer '.$tokens[$driver]], 'query' => ['trip_id' => $charge->id, 'user_type' => 'Driver']]);
        $expect($status === 200 && ($pending['payment_status'] ?? null) === 'Deferred' && (float) ($pending['withdrawable_payout'] ?? -1) === 0.0, 'Issued but unpaid company invoice is not withdrawable');
        $rejected(fn () => $lifecycle->settle($invoice, $owner, '0.01', 'KES', $clock->toDateString(), 'QA-wrong-amount'), 'Incorrect settlement amount is rejected');
        $reference = 'QA-LOCAL-SETTLEMENT-'.$invoice->id;
        $paid = $lifecycle->settle($invoice, $owner, $invoice->total_amount, $invoice->currency_code, $clock->toDateString(), $reference);
        $lifecycle->settle($invoice, $owner, $invoice->total_amount, $invoice->currency_code, $clock->toDateString(), $reference);
        $expect($paid->status === 'Paid' && DB::table('company_corporate_settlements')->where('invoice_id', $invoice->id)->count() === 1, 'Exact QA company settlement is idempotent');
        $expect($charge->fresh()->payment_status === 'Completed' && $charge->fresh()->billing_status === 'paid', 'Company settlement alone releases the saved charge');
        [$status, $settled] = $json('GET', '/api/get_driver_checkout_summary', ['headers' => ['Authorization' => 'Bearer '.$tokens[$driver]], 'query' => ['trip_id' => $charge->id, 'user_type' => 'Driver']]);
        $expect($status === 200 && ($settled['payment_status'] ?? null) === 'Completed' && (float) ($settled['withdrawable_payout'] ?? 0) > 0, 'Driver JWT sees settled earnings become withdrawable');
        $rejected(fn () => $lifecycle->void($paid, $owner, 'Invalid paid void'), 'Paid invoice cannot be voided');
    } finally {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
    }
}
$expect(DB::table('payment_transactions')->count() === $gatewayCount, 'Corporate finance creates no personal gateway transactions');
echo 'Observed '.$checks.' authentication and controlled-clock finance checks; QA finance evidence is retained.'.PHP_EOL;
