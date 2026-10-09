<?php

/** Actual Customer registration and employer membership HTTP flow; isolated staging only. */
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app(\App\Services\Corporate\CorporateAcceptanceProvisioner::class)->assertIsolated((string) config('corporate_acceptance.password'));
if (config('mail.mailers.smtp.host') !== 'mailpit') throw new RuntimeException('Non-relaying acceptance SMTP required.');

$client = new \GuzzleHttp\Client(['base_uri' => 'http://nginx', 'http_errors' => false, 'timeout' => 20, 'allow_redirects' => false]);
$checks = 0;
$expect = function (bool $condition, string $label) use (&$checks): void {
    if (!$condition) throw new RuntimeException('Membership acceptance failed: '.$label);
    $checks++;
    echo 'PASS '.$label.PHP_EOL;
};
$csrf = function ($page): string {
    if (!preg_match('/name="_token"[^>]*value="([^"]+)"/', (string) $page->getBody(), $match)) throw new RuntimeException('Real CSRF token missing.');
    return html_entity_decode($match[1]);
};
$email = 'zawadi.karimi@acceptance.lalataxi.test';
$rider = \App\Models\User::where('email', $email)->where('user_type', 'Rider')->first();
if (!$rider) {
    $registered = $client->get('/api/register', ['query' => ['auth_type' => 'email', 'user_type' => 'Rider',
        'country_code' => 'KE', 'mobile_number' => '700000150', 'first_name' => 'Zawadi', 'last_name' => 'Karimi',
        'email_id' => $email, 'password' => config('corporate_acceptance.password'), 'gender' => 'female',
        'device_type' => 'Android', 'device_id' => 'acceptance-only', 'language' => 'en']]);
    $payload = json_decode((string) $registered->getBody(), true, 512, JSON_THROW_ON_ERROR);
    $expect($registered->getStatusCode() === 200 && ($payload['status_code'] ?? '') === '1', 'Actual Customer API registration');
    // Never log the response: it contains the newly issued JWT.
    unset($payload);
    $rider = \App\Models\User::where('email', $email)->where('user_type', 'Rider')->firstOrFail();
}
$expect($rider->company_id === null && $rider->status === 'Active' && !$rider->corporate_account_id
    && $rider->country_code === '254' && $rider->mobile_number === '700000150', 'Unchanged normal Customer registration identity');
$account = \Modules\SaaSDispatch\Entities\CompanyCorporateAccount::where('account_name', 'Mwangaza Workspaces Kenya')->firstOrFail();
$expect((int) $account->company_id === saas_platform_company_id(), 'Fictional platform-owned employer');

$login = function (string $local) use ($client, $csrf, $expect): \GuzzleHttp\Cookie\CookieJar {
    $jar = new \GuzzleHttp\Cookie\CookieJar;
    $page = $client->get('/corporate/login', ['cookies' => $jar]);
    $result = $client->post('/corporate/login', ['cookies' => $jar, 'form_params' => ['_token' => $csrf($page),
        'email' => $local.'@acceptance.lalataxi.test', 'password' => config('corporate_acceptance.password')]]);
    $expect($result->getStatusCode() === 302 && str_ends_with($result->getHeaderLine('Location'), '/corporate'), $local.' real portal login');
    return $jar;
};
$foreign = $login('baraka');
$page = $client->get('/corporate/members', ['cookies' => $foreign]);
$denied = $client->post('/corporate/members', ['cookies' => $foreign, 'headers' => ['Accept' => 'application/json'],
    'form_params' => ['_token' => $csrf($page), 'country_code' => '254', 'mobile_number' => '700000150', 'role' => 'member']]);
$expect($denied->getStatusCode() === 422 && !$rider->fresh()->corporate_account_id, 'Other operator cannot claim a platform Customer');
$client->post('/corporate/logout', ['cookies' => $foreign, 'form_params' => ['_token' => $csrf($page)]]);

$admin = $login('amina');
$page = $client->get('/corporate/members', ['cookies' => $admin]);
$token = $csrf($page);
$input = ['_token' => $token, 'country_code' => '254', 'mobile_number' => '0700000150', 'role' => 'member'];
$before = $account->audits()->where('reference', 'user:'.$rider->id)->count();
try {
    foreach ([1, 2] as $attempt) {
        $result = $client->post('/corporate/members', ['cookies' => $admin, 'form_params' => $input]);
        $expect($result->getStatusCode() === 302 && str_ends_with($result->getHeaderLine('Location'), '/corporate/members'), 'Actual member form submission '.$attempt);
        $expect((int) $rider->fresh()->corporate_account_id === (int) $account->id && $rider->fresh()->corporate_role === 'member', 'Saved own-employer affiliation '.$attempt);
    }
    $expect($account->audits()->where('reference', 'user:'.$rider->id)->count() === $before + 1, 'Repeated assignment is idempotent');
    $expect($rider->fresh()->company_id === null && $rider->fresh()->mobile_number === '700000150', 'Dispatch owner and phone remain unchanged');
    $directory = $client->get('/corporate/members', ['cookies' => $admin]);
    $expect(str_contains((string) $directory->getBody(), 'Zawadi Karimi'), 'Real employee directory shows saved Customer');
} finally {
    // Remove only this explicitly named fictional membership through the real
    // authorized HTTP flow; keep the registered Customer and audit history.
    if ((int) $rider->fresh()->corporate_account_id === (int) $account->id) {
        $removed = $client->post('/corporate/members/'.$rider->id, ['cookies' => $admin,
            'form_params' => ['_token' => $token, '_method' => 'DELETE']]);
        $expect($removed->getStatusCode() === 302 && !$rider->fresh()->corporate_account_id, 'Real member removal retains Customer');
    }
    $client->post('/corporate/logout', ['cookies' => $admin, 'form_params' => ['_token' => $token]]);
}
echo 'PASS '.$checks.' isolated registration/membership HTTP checks; no trips, invoices or production writes.'.PHP_EOL;
