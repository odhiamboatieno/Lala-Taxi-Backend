<?php

/** Real browser-session/CSRF/SMTP portal acceptance. Never run against production. */
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app(\App\Services\Corporate\CorporateAcceptanceProvisioner::class)->assertIsolated((string) config('corporate_acceptance.password'));
if (config('mail.mailers.smtp.host') !== 'mailpit') throw new RuntimeException('Non-relaying acceptance SMTP required.');

$client = new \GuzzleHttp\Client(['base_uri' => 'http://nginx', 'http_errors' => false, 'timeout' => 20, 'allow_redirects' => false]);
$mail = new \GuzzleHttp\Client(['base_uri' => 'http://mailpit:8025', 'timeout' => 20]);
$checks = 0;
$expect = function (bool $condition, string $label) use (&$checks): void {
    if (!$condition) throw new RuntimeException('Portal acceptance failed: '.$label);
    $checks++;
    echo 'PASS '.$label.PHP_EOL;
};
$csrf = function ($page): string {
    if (!preg_match('/name="_token"[^>]*value="([^"]+)"/', (string) $page->getBody(), $match)) {
        throw new RuntimeException('Real CSRF token missing.');
    }
    return html_entity_decode($match[1]);
};
$admin = new \GuzzleHttp\Cookie\CookieJar;
$login = $client->get('/admin/login', ['cookies' => $admin]);
$authenticated = $client->post('/admin/authenticate', ['cookies' => $admin, 'form_params' => [
    '_token' => $csrf($login), 'user_type' => 'Admin', 'username' => 'qa_njeri', 'password' => config('corporate_acceptance.password'),
]]);
$expect($authenticated->getStatusCode() === 302, 'Actual administrator password/session login');
$accounts = \Modules\SaaSDispatch\Entities\CompanyCorporateAccount::whereIn('account_name', ['Mwangaza Workspaces Kenya', 'Tawi Coast Supplies'])->get()->keyBy('account_name');
$expect($accounts->count() === 2, 'Two provisioned fictional employers');

foreach ([['Mwangaza Workspaces Kenya', 'Amina Wanjiku', 'amina', 'admin'],
    ['Mwangaza Workspaces Kenya', 'Otieno Omondi', 'otieno', 'finance'],
    ['Mwangaza Workspaces Kenya', 'Kamau Mutua', 'kamau.viewer', 'viewer'],
    ['Tawi Coast Supplies', 'Baraka Njoroge', 'baraka', 'admin']] as [$employer, $name, $local, $role]) {
    $account = $accounts[$employer];
    $prefix = '/admin/companies/'.$account->company_id.'/corporate-accounts/accounts/'.$account->id;
    $page = $client->get($prefix, ['cookies' => $admin]);
    $expect($page->getStatusCode() === 200 && str_contains((string) $page->getBody(), 'Portal access'), $name.' admin invitation controls');
    $email = $local.'@acceptance.lalataxi.test';
    $staff = \App\Models\CorporatePortalUser::where('email', $email)->first();
    if (!$staff) {
        $invite = $client->post($prefix.'/portal-users', ['cookies' => $admin, 'form_params' => [
            '_token' => $csrf($page), 'name' => $name, 'email' => $email, 'role' => $role, 'status' => 'Active',
        ]]);
        $expect($invite->getStatusCode() === 302, $name.' actual invitation request');
        $staff = \App\Models\CorporatePortalUser::where('email', $email)->firstOrFail();
    } elseif (!$staff->password_set_at) {
        $resend = $client->post($prefix.'/portal-users/'.$staff->id.'/invite', ['cookies' => $admin, 'form_params' => ['_token' => $csrf($page)]]);
        $expect($resend->getStatusCode() === 302, $name.' pending invitation recovery request');
    }
    $expect($staff->role === $role && (int) $staff->corporate_account_id === (int) $account->id, $name.' scoped portal identity');
    if (!$staff->password_set_at) {
        $inbox = json_decode((string) $mail->get('/api/v1/messages?limit=100')->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $message = collect($inbox['messages'] ?? [])->first(fn ($message) => collect($message['To'] ?? [])->contains(fn ($to) => ($to['Address'] ?? '') === $email));
        if (!$message) throw new RuntimeException('Invitation email not observed for '.$name.'; retry the invitation from admin.');
        $detail = json_decode((string) $mail->get('/api/v1/message/'.$message['ID'])->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $body = html_entity_decode(($detail['HTML'] ?? '').' '.($detail['Text'] ?? ''));
        if (!preg_match('~https?://[^\s"<>]+/corporate/reset-password/[^\s"<>]+~', $body, $match)) throw new RuntimeException('Setup URL missing.');
        $parts = parse_url($match[0]);
        $setupPath = $parts['path'].'?'.($parts['query'] ?? '');
        parse_str($parts['query'] ?? '', $query);
        $jar = new \GuzzleHttp\Cookie\CookieJar;
        $setup = $client->get($setupPath, ['cookies' => $jar]);
        $expect($setup->getStatusCode() === 200 && ($query['email'] ?? null) === $email, $name.' real emailed setup page');
        $result = $client->post('/corporate/reset-password', ['cookies' => $jar, 'form_params' => [
            '_token' => $csrf($setup), 'token' => basename($parts['path']), 'email' => $email,
            'password' => config('corporate_acceptance.password'), 'password_confirmation' => config('corporate_acceptance.password'),
        ]]);
        $expect($result->getStatusCode() === 302 && $staff->fresh()->password_set_at !== null, $name.' single-use password activation');
    }
    $jar = new \GuzzleHttp\Cookie\CookieJar;
    $page = $client->get('/corporate/login', ['cookies' => $jar]);
    $expect($page->getStatusCode() === 200 && str_contains($page->getHeaderLine('Cache-Control'), 'no-store'), $name.' private login form');
    $result = $client->post('/corporate/login', ['cookies' => $jar, 'form_params' => [
        '_token' => $csrf($page), 'email' => $email, 'password' => config('corporate_acceptance.password'),
    ]]);
    $expect($result->getStatusCode() === 302 && str_ends_with($result->getHeaderLine('Location'), '/corporate'), $name.' real portal session login');
    $home = $client->get('/corporate', ['cookies' => $jar]);
    $expect($home->getStatusCode() === 200 && str_contains((string) $home->getBody(), $employer), $name.' own-employer dashboard');
    $expect(str_contains($home->getHeaderLine('Cache-Control'), 'no-store'), $name.' private dashboard');
    foreach (['members' => $role === 'admin', 'staff' => $role === 'admin', 'invoices' => $role !== 'viewer', 'trips' => true] as $path => $permitted) {
        $result = $client->get('/corporate/'.$path, ['cookies' => $jar]);
        $expect($result->getStatusCode() === ($permitted ? 200 : 403), $name.' '.$path.' role authorization');
    }
    $foreign = $accounts->first(fn ($candidate) => $candidate->id !== $account->id);
    $trip = $foreign->trips()->first();
    if ($trip) $expect($client->get('/corporate/trips/'.$trip->id, ['cookies' => $jar])->getStatusCode() === 404, $name.' foreign trip denied');
    // Missing CSRF must never reach the logout controller. Existing HTML handling redirects safely.
    $client->post('/corporate/logout', ['cookies' => $jar]);
    $expect($client->get('/corporate', ['cookies' => $jar])->getStatusCode() === 200, $name.' missing-CSRF logout rejected');
    $client->post('/corporate/logout', ['cookies' => $jar, 'form_params' => ['_token' => $csrf($home)]]);
    $expect($client->get('/corporate', ['cookies' => $jar])->getStatusCode() === 302, $name.' actual CSRF logout');
}
echo 'Observed '.$checks.' portal HTTP/SMTP checks; credentials and tokens were not printed; no invoice or settlement was mutated.'.PHP_EOL;
