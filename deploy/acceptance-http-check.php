<?php

/** Observed HTTP acceptance against the isolated nginx/FPM stack; no seeded JWTs. */
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
app(\App\Services\Corporate\CorporateAcceptanceProvisioner::class)->assertIsolated((string) config('corporate_acceptance.password'));
if (!config('otp.demo_mode') || config('mail.mailers.smtp.host') !== 'mailpit') {
    throw new RuntimeException('Acceptance requires isolated demo OTP and the non-relaying Mailpit inbox.');
}

$client = new \GuzzleHttp\Client(['base_uri' => 'http://nginx', 'http_errors' => false, 'timeout' => 20]);
$checks = 0;
$expect = function (bool $condition, string $label) use (&$checks): void {
    if (!$condition) {
        throw new RuntimeException('Acceptance failed: '.$label);
    }
    $checks++;
    echo 'PASS '.$label.PHP_EOL;
};
$json = function (string $method, string $path, array $options = []) use ($client): array {
    $options['headers']['Accept'] = 'application/json';
    for ($attempt = 0; ; $attempt++) {
        $response = $client->request($method, $path, $options);
        if ($response->getStatusCode() !== 429 || $attempt >= 2) {
            break;
        }
        // Real rate limits remain enabled. Only retry requests rejected before
        // their controller runs, using the server's bounded retry interval.
        $delay = (int) $response->getHeaderLine('Retry-After');
        if ($delay < 1 || $delay > 60) {
            break;
        }
        echo 'Waiting '.$delay.' seconds for the real API rate limit.'.PHP_EOL;
        sleep($delay);
    }
    return [(int) $response->getStatusCode(), json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR), $response];
};
$tokens = [];
foreach ([['700000110', 'Njeri', 'Rider', 'member'], ['700000111', 'Amina', 'Rider', 'manager'],
    ['700000112', 'Otieno', 'Rider', 'finance'], ['700000120', 'Fatuma', 'Rider', 'member'],
    ['700000121', 'Baraka', 'Rider', 'manager'], ['700000122', 'Wanjiru', 'Rider', 'finance'],
    ['700000130', 'Kamau', 'Rider', 'personal'], ['700000140', 'David', 'Driver', 'driver'],
    ['700000141', 'Brian', 'Driver', 'driver']] as [$phone, $first, $type, $role]) {
    $query = ['mobile_number' => $phone, 'country_code' => 'KE', 'user_type' => $type, 'is_login' => '1', 'language' => 'en'];
    [$status, $issued] = $json('GET', '/api/numbervalidation', ['query' => $query]);
    $expect($status === 200 && ($issued['status_code'] ?? null) === '1', $first.' actual OTP issuance');
    [$status, $login] = $json('GET', '/api/otp_verification', ['query' => $query + ['otp' => '123456', 'device_type' => '1', 'device_id' => 'isolated-acceptance-http']]);
    $expect($status === 200 && ($login['status_code'] ?? null) === '1' && !empty($login['access_token']) && ($login['first_name'] ?? null) === $first, $first.' actual OTP/JWT login');
    $tokens[$phone] = $login['access_token'];
    $options = ['headers' => ['Authorization' => 'Bearer '.$login['access_token']]];
    [$status, $profile, $response] = $json('GET', '/api/corporate/profile', $options);
    $expect(str_contains($response->getHeaderLine('Cache-Control'), 'no-store'), $first.' private corporate response');
    if ($role === 'driver') {
        $expect($status === 403, $first.' denied rider finance access');
        continue;
    }
    $expect($status === 200 && ($profile['status_code'] ?? null) === '1', $first.' verified corporate profile');
    if ($role === 'personal') {
        $expect(($profile['corporate'] ?? null) === null && ($profile['default_billing']['mode'] ?? null) === 'personal', 'Personal rider remains unaffiliated');
        continue;
    }
    $expect(($profile['corporate']['role'] ?? null) === $role && ($profile['default_billing']['collection_required'] ?? true) === false, $first.' server-derived role and company billing');
    [$status] = $json('GET', '/api/corporate/users', $options);
    $expect($status === ($role === 'manager' ? 200 : 403), $first.' membership permission');
    [$status] = $json('GET', '/api/corporate/invoices', $options);
    $expect($status === ($role === 'finance' ? 200 : 403), $first.' invoice permission');
    [$status, $dashboard] = $json('GET', '/api/corporate/dashboard', $options);
    $expect($status === 200 && ($dashboard['dashboard']['scope'] ?? null) === ($role === 'member' ? 'self' : 'account'), $first.' dashboard scope');
}

// Exercise real CSRF/session/password authentication, not actingAs or a test guard.
foreach ([['Admin', 'qa_njeri', '/admin/login', '/admin/companies/1/corporate-accounts', 'Mwangaza Workspaces Kenya'],
    ['Company', 'operator1@acceptance.lalataxi.test', '/signin_company', '/company/saas-dispatch/accounts', 'Mwangaza Workspaces Kenya'],
    ['Company', 'operator2@acceptance.lalataxi.test', '/signin_company', '/company/saas-dispatch/accounts', 'Tawi Coast Supplies']] as [$type, $username, $loginPath, $accountPath, $employer]) {
    $jar = new \GuzzleHttp\Cookie\CookieJar;
    $page = $client->get($loginPath, ['cookies' => $jar]);
    $expect($page->getStatusCode() === 200, $type.' '.$employer.' login page');
    preg_match('/name="_token"[^>]*value="([^"]+)"/', (string) $page->getBody(), $match);
    $expect(!empty($match[1]), $type.' real CSRF token');
    $login = $client->post('/admin/authenticate', ['cookies' => $jar, 'allow_redirects' => false, 'form_params' => [
        '_token' => html_entity_decode($match[1]), 'user_type' => $type, 'username' => $username, 'password' => config('corporate_acceptance.password'),
    ]]);
    $expect($login->getStatusCode() === 302 && str_contains($login->getHeaderLine('Location'), '/dashboard'), $type.' actual password/session login');
    $page = $client->get($accountPath, ['cookies' => $jar, 'allow_redirects' => false]);
    $expect($page->getStatusCode() === 200 && str_contains((string) $page->getBody(), $employer), $type.' authenticated corporate management');
    if ($type === 'Company') {
        $foreign = $client->get('/admin/companies/1/corporate-accounts', ['cookies' => $jar, 'allow_redirects' => false]);
        $expect(in_array($foreign->getStatusCode(), [302, 403, 404], true), $employer.' denied admin tenant entry');
    }
}

echo 'Observed '.$checks.' HTTP acceptance checks; no finance settlement was performed.'.PHP_EOL;
