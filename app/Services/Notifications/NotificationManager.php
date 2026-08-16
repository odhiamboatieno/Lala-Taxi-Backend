<?php

namespace App\Services\Notifications;

use App\Contracts\SMSInterface;
use App\Http\Helper\RequestHelper;
use App\Models\Company;
use App\Models\NotificationCampaign;
use App\Models\NotificationDispatchLog;
use App\Models\NotificationTemplate;
use App\Models\Trips;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class NotificationManager
{
    protected $catalog;
    protected $requestHelper;
    protected $smsGateway;

    public function __construct(
        NotificationTemplateCatalog $catalog,
        RequestHelper $requestHelper,
        SMSInterface $smsGateway
    ) {
        $this->catalog = $catalog;
        $this->requestHelper = $requestHelper;
        $this->smsGateway = $smsGateway;
    }

    public function definitions(): array
    {
        return $this->catalog->definitions();
    }

    public function syncTemplatesForScope(int $companyId = 0): Collection
    {
        if (! Schema::hasTable('notification_templates')) {
            return collect();
        }

        $rows = [];
        foreach ($this->definitions() as $definition) {
            $rows[] = NotificationTemplate::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'event_key' => $definition['event_key'],
                ],
                [
                    'category' => $definition['category'],
                    'section' => $definition['section'] ?? null,
                    'audience' => $definition['audience'] ?? null,
                    'label' => $definition['label'],
                    'description' => $definition['description'] ?? null,
                    'push_title' => $definition['push_title'] ?? $definition['label'],
                    'push_message' => $definition['push_message'] ?? '',
                    'sms_message' => $definition['sms_message'] ?? ($definition['push_message'] ?? ''),
                    'placeholders' => $definition['placeholders'] ?? [],
                ]
            );
        }

        return collect($rows);
    }

    public function groupedTemplates(int $companyId = 0): array
    {
        $this->syncTemplatesForScope($companyId);

        return NotificationTemplate::query()
            ->where('company_id', $companyId)
            ->orderBy('category')
            ->orderBy('section')
            ->orderBy('id')
            ->get()
            ->groupBy('category')
            ->map(function ($templates) {
                return $templates->groupBy('section');
            })
            ->all();
    }

    public function placeholdersByCategory(int $companyId = 0): array
    {
        return collect($this->definitions())
            ->groupBy('category')
            ->map(function ($definitions) {
                return collect($definitions)
                    ->flatMap(function ($definition) {
                        return $definition['placeholders'] ?? [];
                    })
                    ->unique()
                    ->values()
                    ->all();
            })
            ->all();
    }

    public function resolveTemplate(string $eventKey, int $companyId = 0): ?NotificationTemplate
    {
        if (! Schema::hasTable('notification_templates')) {
            return null;
        }

        $this->syncTemplatesForScope(0);

        if ($companyId > 0) {
            $this->syncTemplatesForScope($companyId);
        }

        return NotificationTemplate::query()
            ->where('event_key', $eventKey)
            ->whereIn('company_id', [$companyId, 0])
            ->orderByDesc('company_id')
            ->first();
    }

    public function sendEvent(string $eventKey, $recipient, array $context = [], array $payload = [], array $options = []): array
    {
        $companyId = $this->resolveCompanyId($recipient, $context);
        $template = $this->resolveTemplate($eventKey, $companyId);
        $definition = $this->catalog->byKey()[$eventKey] ?? [];
        $variables = $this->buildVariables($recipient, $context, $options);

        $pushTitle = $this->replacePlaceholders($template->push_title ?? ($definition['push_title'] ?? $definition['label'] ?? 'Notification'), $variables);
        $pushMessage = $this->replacePlaceholders($template->push_message ?? ($definition['push_message'] ?? ''), $variables);
        $smsMessage = $this->replacePlaceholders($template->sms_message ?? ($definition['sms_message'] ?? $pushMessage), $variables);

        $channels = $options['channels'] ?? [
            'push' => $template ? (bool) $template->push_enabled && (bool) $template->is_active : true,
            'sms' => $template ? (bool) $template->sms_enabled && (bool) $template->is_active : false,
        ];

        $mergedPayload = array_merge($payload, [
            'custom_message' => [
                'title' => $pushTitle,
                'push_title' => $pushTitle,
                'message_data' => $pushMessage,
                'image' => $options['image_url'] ?? null,
            ],
        ]);

        $result = [
            'push' => null,
            'sms' => null,
            'push_title' => $pushTitle,
            'push_message' => $pushMessage,
            'sms_message' => $smsMessage,
        ];

        if (! empty($channels['push']) && $pushMessage !== '') {
            $result['push'] = $this->sendPush($recipient, $pushTitle, $mergedPayload);
            $this->logDispatch([
                'campaign_id' => $options['campaign_id'] ?? null,
                'notification_template_id' => optional($template)->id,
                'event_key' => $eventKey,
                'company_id' => $companyId,
                'channel' => 'push',
                'recipient_type' => $this->recipientType($recipient),
                'recipient_id' => $recipient->id ?? null,
                'recipient_name' => $this->recipientName($recipient),
                'recipient_contact' => $recipient->device_id ?? null,
                'status' => $result['push']['status'] ? 'sent' : 'failed',
                'subject' => $pushTitle,
                'message' => $pushMessage,
                'response' => $result['push']['response'] ?? null,
                'error_message' => $result['push']['error'] ?? null,
                'sent_at' => now(),
            ]);
        }

        if (! empty($channels['sms']) && $smsMessage !== '' && $this->recipientPhone($recipient) !== '') {
            $result['sms'] = $this->sendSms($recipient, $smsMessage);
            $this->logDispatch([
                'campaign_id' => $options['campaign_id'] ?? null,
                'notification_template_id' => optional($template)->id,
                'event_key' => $eventKey,
                'company_id' => $companyId,
                'channel' => 'sms',
                'recipient_type' => $this->recipientType($recipient),
                'recipient_id' => $recipient->id ?? null,
                'recipient_name' => $this->recipientName($recipient),
                'recipient_contact' => $this->recipientPhone($recipient),
                'status' => ! empty($result['sms']['status']) ? 'sent' : 'failed',
                'subject' => $pushTitle,
                'message' => $smsMessage,
                'response' => isset($result['sms']) ? json_encode($result['sms']) : null,
                'error_message' => $result['sms']['message'] ?? null,
                'sent_at' => now(),
            ]);
        }

        return $result;
    }

    public function sendCampaign(NotificationCampaign $campaign, $recipient): array
    {
        $companyId = (int) $campaign->company_id;
        $variables = $this->buildVariables($recipient, [
            'campaign' => $campaign,
        ], [
            'title' => $campaign->title,
            'messageBody' => $campaign->message,
        ]);

        $title = $this->replacePlaceholders($campaign->title, $variables);
        $message = $this->replacePlaceholders($campaign->message, $variables);
        $imageUrl = $campaign->image_path ? asset(ltrim($campaign->image_path, '/')) : null;

        $channels = [
            'push' => in_array($campaign->channel, ['push', 'both']),
            'sms' => in_array($campaign->channel, ['sms', 'both']),
        ];

        return $this->sendEvent('other.admin_message', $recipient, [
            'company' => $companyId > 0 ? Company::find($companyId) : null,
        ], [], [
            'campaign_id' => $campaign->id,
            'channels' => $channels,
            'image_url' => $imageUrl,
            'title' => $title,
            'messageBody' => $message,
            'businessName' => $this->campaignBusinessName($campaign),
        ]);
    }

    public function processCampaign(NotificationCampaign $campaign): NotificationCampaign
    {
        $recipients = $this->campaignRecipients($campaign);
        $campaign->status = 'processing';
        $campaign->save();

        $success = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            $result = $this->sendCampaign($campaign, $recipient);
            $pushOk = $result['push'] === null || ! empty($result['push']['status']);
            $smsOk = $result['sms'] === null || ! empty($result['sms']['status']);
            if ($pushOk && $smsOk) {
                $success++;
            } else {
                $failed++;
            }
        }

        $campaign->last_sent_at = now();
        $campaign->last_recipient_count = $recipients->count();
        $campaign->successful_count += $success;
        $campaign->failed_count += $failed;

        if ($campaign->recurrence === 'none') {
            $campaign->status = $failed > 0 ? 'completed_with_errors' : 'completed';
            $campaign->is_active = false;
            $campaign->next_run_at = null;
        } else {
            $campaign->status = $failed > 0 ? 'active_with_errors' : 'active';
            $campaign->next_run_at = $this->nextRunAt($campaign);
        }

        $campaign->save();

        return $campaign;
    }

    public function campaignRecipients(NotificationCampaign $campaign): Collection
    {
        $userScope = (int) $campaign->company_id > 0 ? (int) $campaign->company_id : null;

        if ($campaign->audience_type === 'Company' && $userScope === null) {
            $query = Company::query()->where('status', 'Active')->where('id', '!=', 1);
            if ($campaign->target_mode === 'specific' && ! empty($campaign->recipient_ids)) {
                $query->whereIn('id', $this->extractCompanyRecipientIds($campaign->recipient_ids));
            }
            return $query->get();
        }

        $query = User::query()->where('status', 'Active');

        if ($userScope !== null) {
            $query->where('company_id', $userScope);
        }

        if ($campaign->audience_type !== 'all') {
            $query->where('user_type', $campaign->audience_type);
        }

        if ($campaign->target_mode === 'specific' && ! empty($campaign->recipient_ids)) {
            $query->whereIn('id', $this->extractUserRecipientIds($campaign->recipient_ids, $campaign->audience_type));
        }

        $users = $query->get();

        if ($campaign->audience_type === 'all' && $userScope === null) {
            return $users->concat(
                $campaign->target_mode === 'specific'
                    ? Company::query()->whereIn('id', $this->extractCompanyRecipientIds($campaign->recipient_ids ?? []))->where('status', 'Active')->get()
                    : Company::query()->where('status', 'Active')->where('id', '!=', 1)->get()
            )->values();
        }

        return $users;
    }

    protected function buildVariables($recipient, array $context = [], array $overrides = []): array
    {
        $trip = $context['trip'] ?? null;
        $schedule = $context['schedule'] ?? optional($trip)->ride_request?->schedule_ride;
        $company = $context['company'] ?? $this->extractCompany($recipient, $trip);
        $driver = $context['driver'] ?? optional($trip)->driver;
        $customer = $context['customer'] ?? $context['rider'] ?? optional($trip)->users;
        $parcelRequest = $context['parcel_request'] ?? optional($trip)->ride_request?->parcelRequest;

        return array_merge([
            'siteName' => defined('SITE_NAME') ? SITE_NAME : config('app.name', 'Platform'),
            'businessName' => $company ? $company->name : (defined('SITE_NAME') ? SITE_NAME : config('app.name', 'Platform')),
            'companyName' => $company ? $company->name : (defined('SITE_NAME') ? SITE_NAME : config('app.name', 'Platform')),
            'tripId' => optional($trip)->id ?? $context['trip_id'] ?? '',
            'parcelId' => optional($parcelRequest)->id ?? '',
            'vehicleCategory' => optional(optional($trip)->car_type)->car_name ?? optional(optional($driver)->vehicle)->vehicle_name ?? '',
            'vehicleName' => optional(optional($driver)->vehicle)->vehicle_name ?? optional(optional($trip)->car_type)->car_name ?? '',
            'pickupLocation' => optional($trip)->pickup_location ?? optional($schedule)->pickup_location ?? $context['pickup_location'] ?? '',
            'dropOffLocation' => optional($trip)->drop_location ?? optional($schedule)->drop_location ?? $context['drop_location'] ?? '',
            'scheduledDateTime' => $schedule ? trim(($schedule->schedule_date ?? '').' '.($schedule->schedule_time ?? '')) : '',
            'sendTime' => now()->format('d M Y h:i a'),
            'paidAmount' => $this->moneyValue(optional($trip)->total_fare ?? $context['paid_amount'] ?? null, $trip),
            'amountToPay' => $this->moneyValue($context['amount_to_pay'] ?? optional($trip)->total_fare ?? null, $trip),
            'approximateAmount' => $this->moneyValue(optional($trip)->total_fare ?? $context['approximate_amount'] ?? null, $trip),
            'tipsAmount' => $this->moneyValue(optional($trip)->tips ?? $context['tips_amount'] ?? null, $trip),
            'walletAmount' => $this->moneyValue($context['wallet_amount'] ?? null, $trip),
            'methodName' => optional($trip)->payment_mode ?? $context['method_name'] ?? '',
            'customerName' => $this->userName($customer),
            'riderName' => $this->userName($customer),
            'driverName' => $this->userName($driver),
            'recipientName' => optional($parcelRequest)->recipient_name ?? $this->recipientName($recipient),
            'messageBody' => $overrides['messageBody'] ?? ($context['messageBody'] ?? ''),
            'title' => $overrides['title'] ?? ($context['title'] ?? ''),
        ], $overrides);
    }

    protected function extractCompany($recipient, $trip = null): ?Company
    {
        if ($recipient instanceof Company) {
            return $recipient;
        }

        if ($trip && $trip->driver && $trip->driver->company) {
            return $trip->driver->company;
        }

        if ($recipient instanceof User && $recipient->company) {
            return $recipient->company;
        }

        return null;
    }

    protected function sendPush($recipient, string $pushTitle, array $payload): array
    {
        if (empty($recipient->device_id)) {
            return ['status' => false, 'error' => 'Missing device id'];
        }

        try {
            $sent = $this->requestHelper->SendPushNotification($recipient, [
                'push_title' => $pushTitle,
                'data' => $payload,
                'change_title' => 1,
            ]);

            if (! $sent) {
                return ['status' => false, 'error' => 'Push delivery failed'];
            }

            return ['status' => true, 'response' => 'sent'];
        } catch (\Throwable $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    protected function sendSms($recipient, string $message): array
    {
        try {
            return $this->smsGateway->send($this->recipientPhone($recipient), $message);
        } catch (\Throwable $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function logDispatch(array $payload): void
    {
        if (! Schema::hasTable('notification_dispatch_logs')) {
            return;
        }

        NotificationDispatchLog::create($payload);
    }

    protected function resolveCompanyId($recipient, array $context = []): int
    {
        if (! empty($context['company_id'])) {
            return (int) $context['company_id'];
        }

        if (! empty($context['company']) && $context['company'] instanceof Company) {
            return (int) $context['company']->id;
        }

        if (! empty($context['trip']) && $context['trip'] instanceof Trips) {
            if (! empty($context['trip']->company_id)) {
                return (int) $context['trip']->company_id;
            }

            if ($context['trip']->driver && $context['trip']->driver->company_id) {
                return (int) $context['trip']->driver->company_id;
            }
        }

        if ($recipient instanceof Company) {
            return (int) $recipient->id;
        }

        if ($recipient instanceof User && ! empty($recipient->company_id) && (int) $recipient->company_id !== 1) {
            return (int) $recipient->company_id;
        }

        return 0;
    }

    protected function recipientType($recipient): string
    {
        if ($recipient instanceof Company) {
            return 'Company';
        }

        return $recipient->user_type ?? 'User';
    }

    protected function recipientName($recipient): string
    {
        if ($recipient instanceof Company) {
            return $recipient->name;
        }

        return $this->userName($recipient);
    }

    protected function userName($user): string
    {
        if (! $user) {
            return '';
        }

        if ($user instanceof Company) {
            return $user->name;
        }

        $name = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));

        return $name !== '' ? $name : ($user->email ?? 'User');
    }

    protected function recipientPhone($recipient): string
    {
        return $recipient->phone_number ?? $recipient->mobile_number ?? '';
    }

    protected function replacePlaceholders(?string $text, array $variables): string
    {
        $text = (string) $text;

        foreach ($variables as $key => $value) {
            $value = is_scalar($value) ? (string) $value : '';
            $text = str_replace(['{{'.$key.'}}', '{'.$key.'}'], $value, $text);
        }

        return trim($text);
    }

    protected function moneyValue($amount, $trip = null): string
    {
        if ($amount === null || $amount === '') {
            return '';
        }

        $currency = optional($trip)->currency_code ?: (defined('SITE_DEFAULT_CURRENCY') ? SITE_DEFAULT_CURRENCY : '$');

        return $currency.' '.number_format((float) $amount, 2);
    }

    protected function nextRunAt(NotificationCampaign $campaign): ?Carbon
    {
        $base = $campaign->last_sent_at ?: $campaign->next_run_at ?: $campaign->scheduled_for ?: now();
        $next = Carbon::parse($base);

        switch ($campaign->recurrence) {
            case 'daily':
                return $next->addDay();
            case 'weekly':
                return $next->addWeek();
            case 'monthly':
                return $next->addMonth();
            case 'semiannual':
                return $next->addMonths(6);
            default:
                return null;
        }
    }

    protected function campaignBusinessName(NotificationCampaign $campaign): string
    {
        if ((int) $campaign->company_id > 0) {
            $company = Company::find($campaign->company_id);
            if ($company) {
                return $company->name;
            }
        }

        return defined('SITE_NAME') ? SITE_NAME : config('app.name', 'Platform');
    }

    protected function extractUserRecipientIds(array $tokens, string $audienceType = 'all'): array
    {
        return collect($tokens)
            ->map(function ($token) {
                $parts = explode('-', (string) $token, 2);
                if (count($parts) !== 2) {
                    return null;
                }

                return [
                    'type' => $parts[0],
                    'id' => (int) $parts[1],
                ];
            })
            ->filter()
            ->filter(function ($row) use ($audienceType) {
                if ($row['type'] === 'Company') {
                    return false;
                }

                return $audienceType === 'all' || $row['type'] === $audienceType;
            })
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    protected function extractCompanyRecipientIds(array $tokens): array
    {
        return collect($tokens)
            ->map(function ($token) {
                $parts = explode('-', (string) $token, 2);
                if (count($parts) !== 2 || $parts[0] !== 'Company') {
                    return null;
                }

                return (int) $parts[1];
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
