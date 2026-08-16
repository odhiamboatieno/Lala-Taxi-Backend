@extends('admin.template')

@section('title-sub', __('messages.admin.manage_emails_page.email_settings'))
@section('pagetitle', __('messages.admin.manage_emails_page.email_settings'))

@section('main')
<div id="layout-wrapper" ng-controller="email_settings" ng-cloak ng-init="email_driver='{{ old('driver', $settings['driver']) }}'">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-1">{{ __('messages.admin.manage_emails_page.email_settings') }}</h5>
                    <p class="text-muted mb-0">{{ __('messages.blade_static.configure_outbound_mail_delivery_sender_identity_and_pro_76fd4954') }}</p>
                </div>
                <div class="card-body">
                    {!! Form::open(['url' => 'admin/email_settings', 'class' => 'row g-4']) !!}
                        <div class="col-12">
                            <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="input_driver">{{ __('messages.admin.manage_emails_page.form.driver') }} <em class="text-danger">*</em></label>
                            {!! Form::select('driver', [
                                'smtp' => 'SMTP',
                                'sendmail' => 'Sendmail',
                                'mailgun' => 'Mailgun',
                                'mandrill' => 'Mandrill',
                                'ses' => 'SES',
                                'sparkpost' => 'Sparkpost',
                            ], old('driver', $settings['driver']), [
                                'class' => 'form-select',
                                'id' => 'input_driver',
                                'ng-model' => 'email_driver'
                            ]) !!}
                            <span class="text-danger">{{ $errors->first('driver') }}</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label" for="input_host">{{ __('messages.admin.manage_emails_page.form.host') }} <em class="text-danger">*</em></label>
                            {!! Form::text('host', old('host', $settings['host']), ['class' => 'form-control', 'id' => 'input_host']) !!}
                            <span class="text-danger">{{ $errors->first('host') }}</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="input_port">{{ __('messages.admin.manage_emails_page.form.port') }} <em class="text-danger">*</em></label>
                            {!! Form::text('port', old('port', $settings['port']), ['class' => 'form-control', 'id' => 'input_port']) !!}
                            <span class="text-danger">{{ $errors->first('port') }}</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="input_from_address">{{ __('messages.admin.manage_emails_page.form.from_address') }} <em class="text-danger">*</em></label>
                            {!! Form::text('from_address', old('from_address', $settings['from_address']), ['class' => 'form-control', 'id' => 'input_from_address']) !!}
                            <span class="text-danger">{{ $errors->first('from_address') }}</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="input_from_name">{{ __('messages.admin.manage_emails_page.form.form_name') }} <em class="text-danger">*</em></label>
                            {!! Form::text('from_name', old('from_name', $settings['from_name']), ['class' => 'form-control', 'id' => 'input_from_name']) !!}
                            <span class="text-danger">{{ $errors->first('from_name') }}</span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label" for="input_encryption">{{ __('messages.admin.manage_emails_page.form.encryption') }} <em class="text-danger">*</em></label>
                            {!! Form::text('encryption', old('encryption', $settings['encryption']), ['class' => 'form-control', 'id' => 'input_encryption']) !!}
                            <span class="text-danger">{{ $errors->first('encryption') }}</span>
                        </div>

                        <div class="col-md-8" ng-hide="email_driver == 'mailgun'">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="input_username">{{ __('messages.admin.manage_emails_page.form.username') }} <em class="text-danger">*</em></label>
                                    {!! Form::text('username', old('username', $settings['username']), ['class' => 'form-control', 'id' => 'input_username']) !!}
                                    <span class="text-danger">{{ $errors->first('username') }}</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="input_password">{{ __('messages.admin.manage_emails_page.form.password') }} <em class="text-danger">*</em></label>
                                    {!! Form::text('password', old('password', $settings['password']), ['class' => 'form-control', 'id' => 'input_password']) !!}
                                    <span class="text-danger">{{ $errors->first('password') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8" ng-show="email_driver == 'mailgun'">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="input_domain">{{ __('messages.blade_static.domain_9b10914d') }} <em class="text-danger">*</em></label>
                                    {!! Form::text('domain', old('domain', $settings['domain']), ['class' => 'form-control', 'id' => 'input_domain']) !!}
                                    <span class="text-danger">{{ $errors->first('domain') }}</span>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="input_secret">{{ __('messages.blade_static.secret_key_2969a186') }} <em class="text-danger">*</em></label>
                                    {!! Form::text('secret', old('secret', $settings['secret']), ['class' => 'form-control', 'id' => 'input_secret']) !!}
                                    <span class="text-danger">{{ $errors->first('secret') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="{{ url('admin/dashboard') }}" class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">{{ __('messages.admin.submit') }}</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
