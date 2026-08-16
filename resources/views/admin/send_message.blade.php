@extends('admin.template')

@section('title-sub', __('messages.admin.send_message_page.send_notification'))
@section('pagetitle', __('messages.admin.send_message_page.send_notification'))

@section('css')
    <link rel="stylesheet" href="{{ url('css/selectize.default.css') }}">
    <style>
        .notify-shell {
            color: var(--bs-body-color, inherit);
        }

        .notify-panel {
            background: var(--bs-card-bg, var(--bs-body-bg, var(--color-white)));
            border: 1px solid var(--bs-border-color, var(--color-as-rgba-0-0-0-0-125));
            border-radius: 0;
            box-shadow: 0 14px 30px var(--color-as-rgba-16-67-79-0-08);
            margin-bottom: 24px;
            overflow: hidden;
        }

        .notify-panel__head {
            align-items: center;
            border-bottom: 1px solid var(--bs-border-color, var(--color-as-rgba-0-0-0-0-125));
            display: flex;
            justify-content: space-between;
            padding: 20px 22px 18px;
        }

        .notify-panel__title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-size: 30px;
            font-weight: 800;
            margin: 0;
        }

        .notify-panel__caption {
            color: var(--bs-secondary-color, var(--color-as-6c757d));
            font-size: 14px;
            margin-top: 6px;
        }

        .notify-alert {
            background: var(--bs-warning-bg-subtle, var(--color-as-fff3cd));
            border: 1px solid var(--bs-warning-border-subtle, var(--color-as-f4df99));
            border-radius: 0;
            color: var(--bs-warning-text-emphasis, var(--color-as-8b6b10));
            margin: 20px 22px 0;
            padding: 12px 16px;
        }

        .notify-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: minmax(0, 1.8fr) minmax(280px, .85fr);
            padding: 18px 22px 24px;
        }

        .notify-card {
            background: var(--bs-card-bg, var(--bs-body-bg, var(--color-white)));
            border: 1px solid var(--bs-border-color, var(--color-as-rgba-0-0-0-0-125));
            border-radius: 0;
            padding: 20px;
        }

        .notify-card__title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .notify-card__caption {
            color: var(--bs-secondary-color, var(--color-as-6c757d));
            font-size: 13px;
            margin-bottom: 18px;
        }

        .notify-stat {
            background: var(--bs-tertiary-bg, var(--color-as-rgba-127-127-127-0-08));
            border: 1px solid var(--bs-border-color, var(--color-as-rgba-0-0-0-0-125));
            border-radius: 0;
            margin-bottom: 14px;
            padding: 16px 18px;
        }

        .notify-stat small {
            color: var(--bs-secondary-color, var(--color-as-6c757d));
            display: block;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .notify-stat strong {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            display: block;
            font-size: 28px;
            font-weight: 800;
            line-height: 1;
        }

        .notify-upload {
            align-items: center;
            border: 2px dashed var(--bs-border-color, var(--color-as-rgba-0-0-0-0-125));
            border-radius: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 216px;
            padding: 18px;
            text-align: center;
        }

        .notify-upload__preview {
            border-radius: 0;
            margin-top: 14px;
            max-height: 120px;
            object-fit: cover;
            width: 100%;
        }

        .notify-history__toolbar {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .notify-history__search {
            display: flex;
            gap: 10px;
            max-width: 420px;
            width: 100%;
        }

        .notify-history__thumb {
            border-radius: 0;
            height: 44px;
            object-fit: cover;
            width: 74px;
        }

        .notify-history__title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-weight: 700;
        }

        .notify-history__meta {
            color: var(--bs-secondary-color, var(--color-as-6c757d));
            font-size: 12px;
            margin-top: 3px;
        }

        .notify-status {
            border-radius: 0;
            display: inline-flex;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            padding: 5px 10px;
            text-transform: uppercase;
        }

        .notify-status--completed,
        .notify-status--active {
            background: var(--color-as-rgba-50-199-170-0-14);
            color: var(--color-as-11907c);
        }

        .notify-status--scheduled,
        .notify-status--queued,
        .notify-status--processing {
            background: var(--color-as-rgba-73-163-255-0-14);
            color: var(--color-as-2b78c7);
        }

        .notify-status--paused,
        .notify-status--completed_with_errors,
        .notify-status--active_with_errors {
            background: var(--color-as-rgba-255-186-73-0-16);
            color: var(--color-as-cb8212);
        }

        .notify-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .notify-actions form {
            margin: 0;
        }

        @media (max-width: 991px) {
            .notify-grid {
                grid-template-columns: 1fr;
            }

            .notify-history__toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .notify-history__search {
                max-width: none;
            }
        }
    </style>
@endsection

@section('main')
<div id="layout-wrapper" class="notify-shell">
    <div class="notify-panel">
        <div class="notify-panel__head">
            <div>
                <h1 class="notify-panel__title">{{ __('messages.admin.send_message_page.send_notification') }}</h1>
                <div class="notify-panel__caption">{{ __('messages.admin.send_message_page.build_campaigns_desc') }}</div>
            </div>
            <a href="{{ url(LOGIN_USER_TYPE.'/notifications') }}" class="btn btn-outline-primary">{{ __('messages.admin.send_message_page.notification_management') }}</a>
        </div>

        <div class="notify-alert">
            {{ __('messages.admin.send_message_page.setup_templates_alert') }}
        </div>

        <div class="notify-grid">
            <div class="notify-card">
                <div class="notify-card__title">{{ __('messages.admin.send_message_page.send_notification') }}</div>
                <div class="notify-card__caption">{{ __('messages.admin.send_message_page.manually_send_desc') }}</div>

                <form id="campaign-form" method="POST" action="{{ url(LOGIN_USER_TYPE.'/send_message') }}" enctype="multipart/form-data" class="row g-4">
                    {{ csrf_field() }}

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.admin.send_message_page.title') }} <em class="text-danger">*</em></label>
                        <input type="text" name="title" class="form-control" maxlength="100" value="{{ old('title') }}" placeholder="{{ __('messages.admin.send_message_page.title_placeholder') }}">
                        <span class="text-danger">{{ $errors->first('title') }}</span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.admin.send_message_page.targeted_user') }} <em class="text-danger">*</em></label>
                        <select name="user_type" id="campaign-user-type" class="form-select">
                            @if(LOGIN_USER_TYPE != 'company')
                                <option value="all" {{ old('user_type', 'all') === 'all' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.all_users') }}</option>
                                <option value="Rider" {{ old('user_type') === 'Rider' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.customers') }}</option>
                                <option value="Driver" {{ old('user_type') === 'Driver' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.drivers') }}</option>
                                <option value="Company" {{ old('user_type') === 'Company' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.companies') }}</option>
                            @else
                                <option value="Driver" selected>{{ __('messages.admin.send_message_page.drivers') }}</option>
                            @endif
                        </select>
                        @if(LOGIN_USER_TYPE == 'company')
                            <input type="hidden" name="user_type" value="Driver">
                        @endif
                        <span class="text-danger">{{ $errors->first('user_type') }}</span>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.admin.send_message_page.audience_mode') }} <em class="text-danger">*</em></label>
                        <select name="to" id="campaign-target-mode" class="form-select">
                            <option value="to_all" {{ old('to', 'to_all') === 'to_all' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.all_users') }}</option>
                            <option value="to_specific" {{ old('to') === 'to_specific' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.specific_users') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.admin.send_message_page.channel') }} <em class="text-danger">*</em></label>
                        <select name="message_type" class="form-select">
                            <option value="push" {{ old('message_type', 'push') === 'push' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.push_notification') }}</option>
                            <option value="sms" {{ old('message_type') === 'sms' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.sms') }}</option>
                            <option value="both" {{ old('message_type') === 'both' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.push_plus_sms') }}</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">{{ __('messages.admin.send_message_page.schedule_type') }} <em class="text-danger">*</em></label>
                        <select name="schedule_type" id="campaign-schedule-type" class="form-select">
                            <option value="now" {{ old('schedule_type', 'now') === 'now' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.send_now') }}</option>
                            <option value="later" {{ old('schedule_type') === 'later' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.schedule_later') }}</option>
                        </select>
                    </div>

                    <div class="col-12" id="specific-users-box" style="{{ old('to') === 'to_specific' ? '' : 'display:none;' }}">
                        <label class="form-label">{{ LOGIN_USER_TYPE == 'company' ? __('messages.admin.send_message_page.drivers') : __('messages.admin.send_message_page.users') }} <em class="text-danger">*</em></label>
                        <input type="text" id="input-tags3" name="users" value="{{ old('users') }}">
                        <span class="text-danger d-block mt-2">{{ $errors->first('users') }}</span>
                    </div>

                    <div class="col-12">
                        <label class="form-label">{{ __('messages.admin.send_message_page.description') }} <em class="text-danger">*</em></label>
                        <textarea name="message" class="form-control" rows="5" maxlength="500" placeholder="{{ __('messages.admin.send_message_page.description_placeholder') }}">{{ old('message') }}</textarea>
                        <span class="text-danger">{{ $errors->first('message') }}</span>
                    </div>

                    <div class="col-md-6" id="scheduled-for-box" style="{{ old('schedule_type') === 'later' ? '' : 'display:none;' }}">
                        <label class="form-label">{{ __('messages.admin.send_message_page.scheduled_for') }} <em class="text-danger">*</em></label>
                        <input type="datetime-local" name="scheduled_for" class="form-control" value="{{ old('scheduled_for') ? \Carbon\Carbon::parse(old('scheduled_for'))->format('Y-m-d\TH:i') : '' }}">
                        <span class="text-danger">{{ $errors->first('scheduled_for') }}</span>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">{{ __('messages.admin.send_message_page.recurrence') }} <em class="text-danger">*</em></label>
                        <select name="recurrence" class="form-select">
                            <option value="none" {{ old('recurrence', 'none') === 'none' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.one_time_only') }}</option>
                            <option value="daily" {{ old('recurrence') === 'daily' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.every_day') }}</option>
                            <option value="weekly" {{ old('recurrence') === 'weekly' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.every_week') }}</option>
                            <option value="monthly" {{ old('recurrence') === 'monthly' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.every_month') }}</option>
                            <option value="semiannual" {{ old('recurrence') === 'semiannual' ? 'selected' : '' }}>{{ __('messages.admin.send_message_page.every_6_months') }}</option>
                        </select>
                    </div>

                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ url(LOGIN_USER_TYPE.'/send_message') }}" class="btn btn-light">{{ __('messages.admin.send_message_page.reset') }}</a>
                        <button type="submit" class="btn btn-primary">{{ __('messages.admin.send_message_page.submit') }}</button>
                    </div>
                </form>
            </div>

            <div class="notify-card">
                <div class="notify-card__title">{{ __('messages.admin.send_message_page.audience_snapshot') }}</div>
                <div class="notify-card__caption">{{ __('messages.admin.send_message_page.audience_snapshot_desc') }}</div>

                <div class="notify-stat">
                    <small>{{ __('messages.admin.send_message_page.drivers') }}</small>
                    <strong>{{ $recipientStats['drivers'] ?? 0 }}</strong>
                </div>

                @if(LOGIN_USER_TYPE != 'company')
                    <div class="notify-stat">
                        <small>{{ __('messages.admin.send_message_page.customers') }}</small>
                        <strong>{{ $recipientStats['riders'] ?? 0 }}</strong>
                    </div>

                    <div class="notify-stat">
                        <small>{{ __('messages.admin.send_message_page.companies') }}</small>
                        <strong>{{ $recipientStats['companies'] ?? 0 }}</strong>
                    </div>
                @endif

                <div class="notify-card__title mt-4">{{ __('messages.admin.send_message_page.image') }}</div>
                <div class="notify-card__caption">{{ __('messages.admin.send_message_page.image_desc') }}</div>
                <div class="notify-upload">
                    <label for="campaign-image" class="btn btn-outline-primary btn-sm">{{ __('messages.admin.send_message_page.click_to_upload') }}</label>
                    <input form="campaign-form" type="file" id="campaign-image" name="image" class="d-none">
                    <div class="text-muted small mt-2">{{ __('messages.admin.send_message_page.image_formats') }}</div>
                    <img id="campaign-image-preview" class="notify-upload__preview d-none" alt="{{ __('messages.admin.send_message_page.campaign_preview') }}">
                </div>
                <div class="text-danger mt-2">{{ $errors->first('image') }}</div>
            </div>
        </div>
    </div>

    <div class="notify-panel">
        <div class="notify-panel__head">
            <div>
                <h2 class="notify-panel__title" style="font-size:32px;">{{ __('messages.admin.send_message_page.notification_history') }}</h2>
                <div class="notify-panel__caption">{{ __('messages.admin.send_message_page.notification_history_desc') }}</div>
            </div>
        </div>

        <div class="p-4">
            <div class="notify-history__toolbar">
                <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/send_message') }}" class="notify-history__search">
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="{{ __('messages.admin.send_message_page.search') }}">
                    <button type="submit" class="btn btn-primary">{{ __('messages.admin.send_message_page.search') }}</button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.admin.send_message_page.sl') }}</th>
                            <th>{{ __('messages.admin.send_message_page.image') }}</th>
                            <th>{{ __('messages.admin.send_message_page.title') }}</th>
                            <th>{{ __('messages.admin.send_message_page.description') }}</th>
                            <th>{{ __('messages.admin.send_message_page.targeted_user') }}</th>
                            <th>{{ __('messages.admin.send_message_page.status') }}</th>
                            <th class="text-end">{{ __('messages.admin.send_message_page.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $index => $campaign)
                            @php
                                $statusClass = in_array($campaign->status, ['completed', 'active']) ? 'notify-status--completed' : (in_array($campaign->status, ['scheduled', 'queued', 'processing']) ? 'notify-status--scheduled' : 'notify-status--paused');
                            @endphp
                            <tr>
                                <td>{{ $campaigns->firstItem() + $index }}</td>
                                <td>
                                    @if($campaign->image_path)
                                        <img src="{{ asset($campaign->image_path) }}" alt="{{ $campaign->title }}" class="notify-history__thumb">
                                    @else
                                        <div class="notify-history__thumb d-flex align-items-center justify-content-center small" style="background: var(--bs-tertiary-bg, var(--color-as-f8f9fa)); color: var(--bs-secondary-color, var(--color-as-6c757d));">{{ __('messages.admin.send_message_page.no_image') }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="notify-history__title">{{ $campaign->title }}</div>
                                    <div class="notify-history__meta">
                                        {{ strtoupper($campaign->channel) }} • {{ strtoupper($campaign->audience_type) }}
                                        @if($campaign->scheduled_for)
                                            • {{ $campaign->scheduled_for->format('d M Y h:i a') }}
                                        @endif
                                    </div>
                                </td>
                                <td style="min-width:260px;">{{ \Illuminate\Support\Str::limit($campaign->message, 110) }}</td>
                                <td>
                                    {{ $campaign->target_mode === 'specific' ? __('messages.admin.send_message_page.specific').' '.strtolower($campaign->audience_type) : ($campaign->audience_type === 'all' ? __('messages.admin.send_message_page.all_users') : $campaign->audience_type) }}
                                    <div class="notify-history__meta">{{ $campaign->dispatch_logs_count }} {{ __('messages.admin.send_message_page.delivery_logs') }}</div>
                                </td>
                                <td>
                                    <span class="notify-status {{ $statusClass }}">{{ str_replace('_', ' ', $campaign->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="notify-actions">
                                        <form method="POST" action="{{ route('admin.send_message.toggle', ['id' => $campaign->id]) }}">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">{{ $campaign->is_active ? __('messages.admin.send_message_page.pause') : __('messages.admin.send_message_page.resume') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.send_message.resend', ['id' => $campaign->id]) }}">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-outline-primary">{{ __('messages.admin.send_message_page.resend') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.send_message.delete', ['id' => $campaign->id]) }}" onsubmit="return confirm('Delete this campaign?');">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('messages.admin.send_message_page.delete') }}</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">{{ __('messages.admin.send_message_page.no_campaigns_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($campaigns->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $campaigns->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ url('js/selectize.js') }}"></script>
<script>
    $(function () {
        var $targetMode = $('#campaign-target-mode');
        var $scheduleType = $('#campaign-schedule-type');
        var $specificUsers = $('#specific-users-box');
        var $scheduledFor = $('#scheduled-for-box');
        var $fileInput = $('#campaign-image');
        var $preview = $('#campaign-image-preview');
        var $selectizeInput = $('#input-tags3').selectize({
            plugins: ['remove_button'],
            delimiter: ',',
            create: false
        });

        function toggleSpecificUsers() {
            $specificUsers.toggle($targetMode.val() === 'to_specific');
        }

        function toggleSchedule() {
            $scheduledFor.toggle($scheduleType.val() === 'later');
        }

        function loadUsers() {
            if ($targetMode.val() !== 'to_specific') {
                return;
            }

            var userType = '{{ LOGIN_USER_TYPE }}' === 'company' ? 'Driver' : $('#campaign-user-type').val();
            var selectize = $selectizeInput[0].selectize;
            selectize.disable();

            $.ajax({
                type: 'POST',
                url: APP_URL + '/{{ LOGIN_USER_TYPE }}/get_send_users',
                data: {
                    type: userType,
                    _token: '{{ csrf_token() }}'
                },
                dataType: 'json',
                success: function (resultData) {
                    selectize.clearOptions();
                    $.each(resultData, function (key, value) {
                        selectize.addOption({
                            value: value.user_type + '-' + value.id,
                            text: value.first_name + ' - ' + value.mobile_number
                        });
                    });
                    selectize.enable();
                }
            });
        }

        $targetMode.on('change', function () {
            toggleSpecificUsers();
            loadUsers();
        });

        $('#campaign-user-type').on('change', loadUsers);
        $scheduleType.on('change', toggleSchedule);

        $fileInput.on('change', function (event) {
            var file = event.target.files[0];
            if (!file) {
                $preview.addClass('d-none').attr('src', '');
                return;
            }

            var reader = new FileReader();
            reader.onload = function (e) {
                $preview.removeClass('d-none').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        });

        toggleSpecificUsers();
        toggleSchedule();

        if ($targetMode.val() === 'to_specific') {
            loadUsers();
        }
    });
</script>
@endpush
