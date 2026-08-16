@extends('admin.template')

@section('title-sub', __('messages.admin.manage_notifications_page.title_sub'))
@section('pagetitle', __('messages.admin.manage_notifications_page.title'))

@section('css')
    <style>
        .template-shell {
            color: var(--bs-body-color, inherit);
        }

        .template-surface {
            background: var(--bs-card-bg, var(--bs-body-bg, #fff));
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, .125));
            border-radius: 22px;
            box-shadow: 0 14px 30px rgba(16, 67, 79, .08);
            padding: 22px;
        }

        .template-head {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .template-title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-size: 36px;
            font-weight: 800;
            margin: 0;
        }

        .template-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 22px;
        }

        .template-tab {
            background: var(--bs-card-bg, var(--bs-body-bg, #fff));
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, .125));
            border-radius: 12px;
            color: var(--bs-body-color, inherit);
            font-weight: 700;
            padding: 10px 16px;
            text-decoration: none;
        }

        .template-tab.is-active {
            background: #31c8b1;
            border-color: #31c8b1;
            color: #fff;
        }

        .template-section {
            background: var(--bs-card-bg, var(--bs-body-bg, #fff));
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, .125));
            border-radius: 20px;
            margin-bottom: 24px;
            overflow: hidden;
        }

        .template-section__head {
            border-bottom: 1px solid var(--bs-border-color, rgba(0, 0, 0, .125));
            padding: 18px 22px 16px;
        }

        .template-section__title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-size: 26px;
            font-weight: 800;
            margin: 0 0 5px;
        }

        .template-section__caption {
            color: var(--bs-secondary-color, #6c757d);
            font-size: 13px;
        }

        .template-grid {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            padding: 22px;
        }

        .template-item {
            background: var(--bs-tertiary-bg, rgba(127, 127, 127, .08));
            border: 1px solid var(--bs-border-color, rgba(0, 0, 0, .125));
            border-radius: 18px;
            padding: 16px;
        }

        .template-item__head {
            align-items: center;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 14px;
        }

        .template-item__title {
            color: var(--bs-heading-color, var(--bs-body-color, inherit));
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 4px;
        }

        .template-item__desc {
            color: var(--bs-secondary-color, #6c757d);
            font-size: 12px;
        }

        .template-switches {
            display: flex;
            gap: 12px;
        }

        .template-switch {
            align-items: center;
            color: var(--bs-secondary-color, #6c757d);
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 6px;
        }

        .template-placeholders {
            background: var(--bs-body-bg, rgba(127, 127, 127, .04));
            border: 1px dashed var(--bs-border-color, rgba(0, 0, 0, .125));
            border-radius: 14px;
            color: var(--bs-secondary-color, #6c757d);
            font-size: 12px;
            margin-top: 12px;
            padding: 12px;
        }

        .template-placeholders code {
            color: var(--bs-primary, #0d6efd);
            margin-right: 8px;
        }

        @media (max-width: 991px) {
            .template-grid {
                grid-template-columns: 1fr;
            }

            .template-head {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }
        }
    </style>
@endsection

@section('main')
<div id="layout-wrapper" class="template-shell">
    <div class="template-surface">
        <div class="template-head">
            <div>
                <h1 class="template-title">{{ __('messages.admin.manage_notifications_page.heading') }}</h1>
                <div class="text-muted">{{ __('messages.admin.manage_notifications_page.subtitle') }}</div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ url(LOGIN_USER_TYPE.'/send_message') }}" class="btn btn-outline-primary">{{ __('messages.admin.manage_notifications_page.send_notification') }}</a>
                <a href="{{ url(LOGIN_USER_TYPE === 'company' ? 'company/api_credentials' : 'admin/api_credentials') }}" class="btn btn-light">{{ __('messages.admin.manage_notifications_page.firebase_configuration') }}</a>
            </div>
        </div>

        @php
            $categories = [
                'scheduled_trip' => __('messages.admin.manage_notifications_page.category_scheduled_trip'),
                'regular_trip' => __('messages.admin.manage_notifications_page.category_regular_trip'),
                'parcel' => __('messages.admin.manage_notifications_page.category_parcel'),
                'driver_registration' => __('messages.admin.manage_notifications_page.category_driver_registration'),
                'rider_registration' => __('messages.admin.manage_notifications_page.category_rider_registration'),
                'other' => __('messages.admin.manage_notifications_page.category_other'),
            ];
        @endphp

        <div class="template-tabs">
            @foreach($categories as $key => $label)
                <a href="{{ url(LOGIN_USER_TYPE.'/notifications?category='.$key) }}" class="template-tab {{ $activeCategory === $key ? 'is-active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('admin.notifications.update') }}">
            {{ csrf_field() }}

            @foreach(($templatesByCategory[$activeCategory] ?? []) as $section => $templates)
                <div class="template-section">
                    <div class="template-section__head">
                        <h2 class="template-section__title">{{ $section }}</h2>
                        <div class="template-section__caption">{{ __('messages.admin.manage_notifications_page.section_caption', ['section' => strtolower($section)]) }}</div>
                    </div>

                    <div class="template-grid">
                        @foreach($templates as $template)
                            <div class="template-item">
                                <div class="template-item__head">
                                    <div>
                                        <div class="template-item__title">{{ $template->label }}</div>
                                        <div class="template-item__desc">{{ $template->description }}</div>
                                    </div>
                                    <div class="template-switches">
                                        <label class="template-switch">
                                            <input type="checkbox" name="templates[{{ $template->event_key }}][is_active]" value="1" {{ $template->is_active ? 'checked' : '' }}>
                                            {{ __('messages.admin.manage_notifications_page.active') }}
                                        </label>
                                        <label class="template-switch">
                                            <input type="checkbox" name="templates[{{ $template->event_key }}][push_enabled]" value="1" {{ $template->push_enabled ? 'checked' : '' }}>
                                            {{ __('messages.admin.manage_notifications_page.push') }}
                                        </label>
                                        <label class="template-switch">
                                            <input type="checkbox" name="templates[{{ $template->event_key }}][sms_enabled]" value="1" {{ $template->sms_enabled ? 'checked' : '' }}>
                                            {{ __('messages.admin.manage_notifications_page.sms') }}
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.admin.manage_notifications_page.push_title_label') }}</label>
                                    <input type="text" class="form-control" name="templates[{{ $template->event_key }}][push_title]" value="{{ $template->push_title }}">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('messages.admin.manage_notifications_page.push_message_label') }}</label>
                                    <textarea class="form-control" rows="4" name="templates[{{ $template->event_key }}][push_message]">{{ $template->push_message }}</textarea>
                                </div>

                                <div>
                                    <label class="form-label">{{ __('messages.admin.manage_notifications_page.sms_message_label') }}</label>
                                    <textarea class="form-control" rows="3" name="templates[{{ $template->event_key }}][sms_message]">{{ $template->sms_message }}</textarea>
                                </div>

                                @if(!empty($template->placeholders))
                                    <div class="template-placeholders">
                                        @foreach($template->placeholders as $placeholder)
                                            <code>{{ '{'.$placeholder.'}' }}</code>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">{{ __('messages.admin.manage_notifications_page.submit') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
