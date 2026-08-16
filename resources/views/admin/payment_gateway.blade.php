@extends('admin.template')
@section('title', $paymentGatewayHeading ?? __('messages.admin.payment_gateway.title'))
@section('main')
<style type="text/css">
	.tooltip-custom {
		display: inline-block;
		position: relative;
		vertical-align: middle;
		line-height: 16px;
		margin: -5px 0 -3px;
		padding: 4px 0;
	}
	.tooltip-custom .icon {
		background: url("../images/seller_settings.png") no-repeat;
		background-size: 120px 200px;
	}
	.tooltip-custom .icon {
		display: block;
		width: 14px;
		height: 14px;
		background-position: -95px -150px !important;
		opacity: 0.4;
	}
	.tooltip-custom em {
		font-weight: normal;
		font-size: 12px;
		padding: 2px 10px 10px;
		width: 200px;
		white-space: normal;
		line-height: 16px;
		top: 22px;
		text-align: left;
		margin-left: -100px !important;
		left: 50%;
		background: var(--color-as-2c3239);
		border-radius: 0;
		color: var(--color-white);
		font-style: normal;
		position: absolute;
		display: none;
		z-index: 1;
	}
	.tooltip-custom em::after{
		-moz-border-bottom-colors: none;
		-moz-border-left-colors: none;
		-moz-border-right-colors: none;
		-moz-border-top-colors: none;
		border-color: var(--color-as-2c3239) transparent transparent;
		border-image: none;
		border-style: solid;
		border-width: 3px;
		top: -6px;
		content: "";
		left: 50%;
		margin-left: -3px;
		position: absolute;
		transform: rotate(180deg);
	}
	.tooltip-custom:hover em {
		display: block !important;
	}
	.box-body
	{
		padding: 0;
	}
	.payment-gateway-page .content-header,
	.payment-gateway-page .breadcrumb {
		display: none;
	}
	.payment-gateway-page .content,
	.payment-gateway-page .content-wrapper {
		padding: 0;
		margin: 0;
		background: transparent;
		min-height: auto;
	}
	.payment-gateway-page .box {
		background: var(--color-white);
		border: 0;
		border-radius: 0;
		box-shadow: 0 18px 45px var(--color-as-rgba-15-23-42-0-08);
		overflow: hidden;
	}
	.payment-gateway-page .box-header {
		padding: 24px;
		border-bottom: 1px solid var(--color-as-rgba-148-163-184-0-22);
		background: linear-gradient(135deg, var(--color-as-rgba-20-184-166-0-12), var(--color-as-rgba-37-99-235-0-10));
	}
	.payment-gateway-page .box-title {
		font-weight: 700;
		color: var(--color-as-0f172a);
	}
	.payment-gateway-page .box-body {
		padding: 18px 24px;
	}
	.payment-gateway-page .form-group {
		display: grid;
		grid-template-columns: minmax(180px, 260px) 1fr;
		gap: 16px;
		align-items: center;
		margin-bottom: 18px;
	}
	.payment-gateway-page .control-label {
		text-align: left;
		font-weight: 600;
		color: var(--color-as-334155);
	}
	.payment-gateway-page .col-sm-3,
	.payment-gateway-page .col-md-7 {
		width: auto;
		max-width: none;
		padding: 0;
		margin: 0;
	}
	.payment-gateway-page .col-sm-offset-1 {
		margin-left: 0;
	}
	.payment-gateway-page .form-control,
	.payment-gateway-page select {
		border-radius: 0;
		border-color: var(--color-as-dbe3ef);
		min-height: 42px;
	}
	.payment-gateway-page .box-footer {
		padding: 24px;
		background: var(--color-as-f8fafc);
		border-top: 1px solid var(--color-as-rgba-148-163-184-0-22);
	}
	@media (max-width: 768px) {
		.payment-gateway-page .form-group {
			grid-template-columns: 1fr;
		}
	}
</style>
<div class="payment-gateway-page">
<div class="content-wrapper">
	<section class="content-header">
		<h1> {{ __('messages.blade_static.payment_gateway_ec48c5e8') }} </h1>
		<ol class="breadcrumb">
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"> <i class="fa fa-dashboard"></i> {{ __('messages.blade_static.home_70f8bb9a') }} </a>
			</li>
			<li>
				<a href="{{ url(LOGIN_USER_TYPE.'/payment_gateway') }}"> {{ __('messages.blade_static.payment_gateway_ec48c5e8') }} </a>
			</li>
			<li class="active"> {{ __('messages.blade_static.edit_5301648d') }} </li>
		</ol>
	</section>
	<section class="content">
		<div class="row">
			<div class="col-md-12">
				<div class="box box-info">
					<div class="box-header with-border">
						<h3 class="box-title"> {{ __('messages.blade_static.payment_gateway_form_5973ef24') }} </h3>
					</div>
					{!! Form::open(['url' => $paymentGatewayFormAction ?? 'admin/payment_gateway', 'class' => 'form-horizontal', 'id' => 'payment_gateway_form']) !!}
					<div class="box-body">
						<span class="text-danger">{{ __('messages.blade_static.fields_are_mandatory_27683489') }}</span>
                 
						<!-- Paypal Section Start -->
						<div class="box-body">
							<div class="form-group" ng-init="paypal_enabled={{ old('paypal_enabled',payment_gateway('is_enabled','Paypal')) }}">
								<label for="input_paypal_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_paypal_enabled_c5716cef') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paypal_enabled', array('0' => 'No', '1' => 'Yes'), '', ['class' => 'form-control', 'id' => 'input_paypal_enabled', 'ng-model' => 'paypal_enabled']) !!}
									<span class="text-danger">{{ $errors->first('paypal_enabled') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paypal_mode" class="col-sm-3 control-label">{{ __('messages.blade_static.paypal_mode_8f9a2407') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paypal_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('paypal_mode',payment_gateway('mode','Paypal')), ['class' => 'form-control', 'id' => 'input_paypal_mode']) !!}
									<span class="text-danger">{{ $errors->first('paypal_mode') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paypal_id" class="col-sm-3 control-label">{{ __('messages.blade_static.paypal_id_73b3bd1b') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_id', old('paypal_id',demoPaymentGateway('paypal_id','Paypal')), ['class' => 'form-control', 'id' => 'input_paypal_id', 'placeholder' => 'PayPal Id']) !!}
									<span class="text-danger">{{ $errors->first('paypal_id') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paypal_client" class="col-sm-3 control-label">{{ __('messages.blade_static.paypal_client_id_b3886bdc') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_client', old('paypal_client',demoPaymentGateway('client','Paypal')), ['class' => 'form-control', 'id' => '', 'placeholder' => 'PayPal Client']) !!}
									<span class="text-danger">{{ $errors->first('paypal_client') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paypal_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.paypal_secret_0e16b5c3') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_secret', old('paypal_secret',demoPaymentGateway('secret','Paypal')), ['class' => 'form-control', 'id' => '', 'placeholder' => 'PayPal Secret']) !!}
									<span class="text-danger">{{ $errors->first('paypal_secret') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="accessToken" class="col-sm-3 control-label"> {{ __('messages.blade_static.paypal_access_token_f7284acd') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paypal_access_token', old('paypal_access_token',demoPaymentGateway('access_token','Paypal')), ['class' => 'form-control', 'id' => 'accessToken', 'placeholder' => 'PayPal Access Token']) !!}
									<span class="text-danger">{{ $errors->first('paypal_access_token') }}</span>
								</div>
							</div>
						</div>
						<!-- Paypal Section End -->
						<!-- Stripe Section Start -->
						<div class="box-body" ng-init="stripe_enabled={{ old('stripe_enabled',payment_gateway('is_enabled','Stripe')) }}">
							<div class="form-group">
								<label for="input_stripe_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_stripe_enabled_bec806fd') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('stripe_enabled', array('0' => 'No', '1' => 'Yes'), old('stripe_enabled',payment_gateway('is_enabled','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_enabled','ng-model' => 'stripe_enabled']) !!}
									<span class="text-danger">{{ $errors->first('stripe_enabled') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_publish_key" class="col-sm-3 control-label"> {{ __('messages.blade_static.stripe_key_83265fcd') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_publish_key', old('stripe_publish_key',demoPaymentGateway('publish','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_publish_key', 'placeholder' => 'Stripe Key']) !!}
									<span class="text-danger">{{ $errors->first('stripe_publish_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_secret_key" class="col-sm-3 control-label"> {{ __('messages.blade_static.stripe_secret_bc034d13') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_secret_key', old('stripe_secret_key',demoPaymentGateway('secret','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_secret_key', 'placeholder' => 'Stripe Secret']) !!}
									<span class="text-danger">{{ $errors->first('stripe_secret_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_api_version" class="col-sm-3 control-label"> {{ __('messages.blade_static.stripe_api_version_c33cb325') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('stripe_api_version', old('stripe_api_version',payment_gateway('api_version','Stripe')), ['class' => 'form-control', 'id' => 'input_stripe_api_version', 'placeholder' => 'Stripe API Version']) !!}
									<span class="text-danger">{{ $errors->first('stripe_api_version') }}</span>
								</div>
							</div>
						</div>
					</div>
					<!-- Stripe Section End -->
					  <!-- Paytm Section Start -->
                  <div class="box-body" ng-init="paytm_enabled={{ old('paytm_enabled',payment_gateway('is_enabled','Paytm')) }}">
							<div class="form-group">
								<label for="input_paytm_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_paytm_enabled_b9f8026b') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paytm_enabled', array('0' => 'No', '1' => 'Yes'), old('paytm_enabled',payment_gateway('is_enabled','Paytm')), ['class' => 'form-control', 'id' => 'input_paytm_enabled','ng-model' => 'paytm_enabled']) !!}
									<span class="text-danger">{{ $errors->first('paytm_enabled') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_mode" class="col-sm-3 control-label">{{ __('messages.blade_static.paytm_mode_e8934b19') }} <em ng-show="paytm_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('paytm_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('paytm_mode',payment_gateway('mode','Paytm')), ['class' => 'form-control', 'id' => 'input_paytm_mode']) !!}
									<span class="text-danger">{{ $errors->first('paytm_mode') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paytm_merchant" class="col-sm-3 control-label"> {{ __('messages.blade_static.paytm_merchant_1e492705') }} <em ng-show="paytm_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paytm_merchant', old('paytm_merchant',demoPaymentGateway('paytm_merchant','Paytm')), ['class' => 'form-control', 'id' => 'input_paytm_merchant', 'placeholder' => 'Paytm']) !!}
									<span class="text-danger">{{ $errors->first('paytm_merchant') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.paytm_secret_92daa947') }} <em ng-show="paytm_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('paytm_secret', old('paytm_secret',demoPaymentGateway('paytm_secret','Paytm')), ['class' => 'form-control', 'id' => 'input_paytm_secret', 'placeholder' => 'Paytm Secret']) !!}
									<span class="text-danger">{{ $errors->first('paytm_secret') }}</span>
								</div>
							</div>
					</div>
					<!-- Paytm Section End -->
					
					
					 <!-- FlutterWave Section Start -->
                  <div class="box-body" ng-init="flutterwave_enabled={{ old('flutterwave_enabled',payment_gateway('is_enabled','Flutterwave')) }}">
							<div class="form-group">
								<label for="input_flutterwave_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_flutterwave_enabled_76f229c2') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('flutterwave_enabled', array('0' => 'No', '1' => 'Yes'), old('flutterwave_enabled',payment_gateway('is_enabled','Flutterwave')), ['class' => 'form-control', 'id' => 'input_paytm_enabled','ng-model' => 'paytm_enabled']) !!}
									<span class="text-danger">{{ $errors->first('paytm_enabled') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_mode" class="col-sm-3 control-label">{{ __('messages.blade_static.flutterwave_mode_e5daa31f') }} <em ng-show="flutterwave_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('flutterwave_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('flutterwave_mode',payment_gateway('mode','Flutterwave')), ['class' => 'form-control', 'id' => 'input_flutterwave_mode']) !!}
									<span class="text-danger">{{ $errors->first('flutterwave_mode') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paytm_merchant" class="col-sm-3 control-label"> {{ __('messages.blade_static.flutterwave_public_key_75b10948') }} <em ng-show="flutterwave_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('flutterwave_public_key', old('flutterwave_public_key',demoPaymentGateway('public_key','Flutterwave')), ['class' => 'form-control', 'id' => 'input_flutterwave_public_key', 'placeholder' => 'Flutterwave']) !!}
									<span class="text-danger">{{ $errors->first('flutterwave_public_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.flutterwave_secret_key_aebcd164') }} <em ng-show="flutterwave_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('flutterwave_secret_key', old('flutterwave_secret_key',demoPaymentGateway('secret_key','Flutterwave')), ['class' => 'form-control', 'id' => 'input_flutterwave_secret', 'placeholder' => 'Flutterwave Secret']) !!}
									<span class="text-danger">{{ $errors->first('flutterwave_secret_key') }}</span>
								</div>
							</div>
							
								<div class="form-group">
								<label for="input_paytm_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.flutterwave_encryption_key_80734bf1') }} <em ng-show="flutterwave_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('flutterwave_encryption_key', old('flutterwave_encryption_key',demoPaymentGateway('encryption_key','Flutterwave')), ['class' => 'form-control', 'id' => 'input_flutterwave_encryption', 'placeholder' => 'Flutterwave Encryption Key']) !!}
									<span class="text-danger">{{ $errors->first('flutterwave_encryption_key') }}</span>
								</div>
							</div>
					</div>
					<!-- FlutterWave Section End -->
					
							<!-- Mpesa Section Start -->
						<div class="box-body" ng-init="mpesa_enabled={{ old('mpesa_enabled',payment_gateway('is_enabled','Mpesa')) }}">
							<div class="form-group">
								<label for="input_stripe_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_mpesa_enabled_ba4e333e') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('mpesa_enabled', array('0' => 'No', '1' => 'Yes'), old('mpesa_enabled',payment_gateway('is_enabled','Mpesa')), ['class' => 'form-control', 'id' => 'input_mpesa_enabled','ng-model' => 'mpesa_enabled']) !!}
									<span class="text-danger">{{ $errors->first('mpesa_enabled') }}</span>
								</div>
							</div>
								<div class="form-group">
								<label for="input_paypal_mode" class="col-sm-3 control-label">{{ __('messages.blade_static.mpesa_mode_92f7cebb') }} <em ng-show="paypal_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('mpesa_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('mpesa_mode',payment_gateway('mode','Mpesa')), ['class' => 'form-control', 'id' => 'input_mpesa_mode']) !!}
									<span class="text-danger">{{ $errors->first('mpesa_mode') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_publish_key" class="col-sm-3 control-label"> {{ __('messages.blade_static.consumer_key_eeb0c3e8') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('consumer_key', old('consumer_key',demoPaymentGateway('consumer_key','Mpesa')), ['class' => 'form-control', 'id' => 'input_consumer_key', 'placeholder' => 'Consumer Key']) !!}
									<span class="text-danger">{{ $errors->first('consumer_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_secret_key" class="col-sm-3 control-label"> {{ __('messages.blade_static.consumer_secret_f26b7db5') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('consumer_secret', old('consumer_secret',demoPaymentGateway('consumer_secret','Mpesa')), ['class' => 'form-control', 'id' => 'input_consumer_secret', 'placeholder' => 'Consumer Secret']) !!}
									<span class="text-danger">{{ $errors->first('consumer_secret') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_stripe_api_version" class="col-sm-3 control-label"> {{ __('messages.blade_static.callback_url_f6f13896') }} <em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('callback_url', old('callback_url',demoPaymentGateway('callback_url','Mpesa')), ['class' => 'form-control', 'id' => 'input_callback_url', 'placeholder' => 'Callback Url']) !!}
									<span class="text-danger">{{ $errors->first('callback_url') }}</span>
								</div>
							</div>
								<div class="form-group">
								<label for="input_stripe_api_version" class="col-sm-3 control-label"> {{ __('messages.blade_static.business_shortcode_d5636725') }}<em ng-show="stripe_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('business_shortcode', old('business_shortcode',demoPaymentGateway('business_shortcode','Mpesa')), ['class' => 'form-control', 'id' => 'input_business_shortcode', 'placeholder' => 'Business Shortcode']) !!}
									<span class="text-danger">{{ $errors->first('business_shortcode') }}</span>
								</div>
							</div>
						</div>
					
					<!-- Mpesa Section End -->
					
					 <!-- Mollie Section Start
                  <div class="box-body" ng-init="mollie_enabled={{ old('mollie_enabled',payment_gateway('is_enabled','Mollie')) }}">
							<div class="form-group">
								<label for="input_paytm_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_mollie_enabled_4f65b246') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('mollie_enabled', array('0' => 'No', '1' => 'Yes'), old('mollie_enabled',payment_gateway('is_enabled','Mollie')), ['class' => 'form-control', 'id' => 'input_mollie_enabled','ng-model' => 'mollie_enabled']) !!}
									<span class="text-danger">{{ $errors->first('mollie_enabled') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_mode" class="col-sm-3 control-label">{{ __('messages.blade_static.mollie_mode_ffa09ca4') }} <em ng-show="mollie_enabled == '1'" class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('mollie_mode', array('sandbox' => 'Sandbox', 'live' => 'Live'), old('mollie_mode',payment_gateway('mode','Mollie')), ['class' => 'form-control', 'id' => 'input_mollie_mode']) !!}
									<span class="text-danger">{{ $errors->first('mollie_mode') }}</span>
								</div>
							</div>

							<div class="form-group">
								<label for="input_paytm_merchant" class="col-sm-3 control-label"> {{ __('messages.blade_static.mollie_api_key_1bda3cbe') }} <em ng-show="mollie_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('mollie_key', old('mollie_key',demoPaymentGateway('api_key','Mollie')), ['class' => 'form-control', 'id' => 'input_mollie_merchant', 'placeholder' => 'Mollie Api Key']) !!}
									<span class="text-danger">{{ $errors->first('mollie_key') }}</span>
								</div>
							</div>
							<div class="form-group">
								<label for="input_paytm_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.mollie_partner_id_6fddfae1') }} <em ng-show="mollie_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('mollie_partner', old('mollie_partner',demoPaymentGateway('partner_id','Mollie')), ['class' => 'form-control', 'id' => 'input_mollie_partner', 'placeholder' => 'Partner ID']) !!}
									<span class="text-danger">{{ $errors->first('mollie_partner') }}</span>
								</div>
							</div>
							
							<div class="form-group">
								<label for="input_paytm_secret" class="col-sm-3 control-label"> {{ __('messages.blade_static.mollie_profile_id_a8f8b747') }} <em ng-show="mollie_enabled == '1'" class="text-danger">*</em></label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::text('mollie_profile', old('mollie_profile',demoPaymentGateway('profile_id','Mollie')), ['class' => 'form-control', 'id' => 'input_mollie_profile', 'placeholder' => 'Profile ID']) !!}
									<span class="text-danger">{{ $errors->first('mollie_profile') }}</span>
								</div>
							</div>
							
					</div>
					<!-- Mollie Section End -->
					
					<!-- Braintree Section Start -->
					<div class="box-body" ng-init="bt_enabled={{ old('bt_enabled',payment_gateway('is_enabled','Braintree')) }}">
						<div class="form-group">
								<label for="input_bt_enabled" class="col-sm-3 control-label">{{ __('messages.blade_static.is_braintree_enabled_377b2ae2') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									{!! Form::select('bt_enabled', array('0' => 'No', '1' => 'Yes'), old('bt_enabled',payment_gateway('is_enabled','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_enabled','ng-model' => 'bt_enabled']) !!}
									<span class="text-danger">{{ $errors->first('bt_enabled') }}</span>
								</div>
							</div>
						<div class="form-group">
							<label for="input_mode" class="col-sm-3 control-label"> {{ __('messages.blade_static.payment_mode_23b35c41') }} <em ng-show="bt_enabled == '1'" class="text-danger">*</em> </label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::select('bt_mode', array('sandbox' => 'Sandbox', 'production' => 'Production'), old('bt_mode'
								,payment_gateway('mode','Braintree')), ['class' => 'form-control', 'id' => 'input_mode']) !!}
								<span class="text-danger">{{ $errors->first('mode') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_merchant_id" class="col-sm-3 control-label"> {{ __('messages.blade_static.braintree_merchant_id_15dbee6e') }} <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_merchant_id', old('bt_merchant_id',demoPaymentGateway('merchant_id','Braintree')), ['class' => 'form-control', 'id' => 'input_merchant_id', 'placeholder' => 'Merchant ID']) !!}
								<span class="text-danger">{{ $errors->first('bt_merchant_id') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_merchant_id" class="col-sm-3 control-label">
								{{ __('messages.blade_static.braintree_merchant_account_id_0a2a2e9e') }}
								<span class="tooltip-custom"><i class="icon"></i> 
									<em style="margin-left: -108px;">
										{{ __('messages.blade_static.merchant_account_id_is_a_unique_identifier_for_a_specifi_630cff6b') }}
									</em>
								</span>
							</label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('merchant_account_id', old('merchant_account_id', demoPaymentGateway('merchant_account_id','Braintree')), ['class'=>'form-control', 'id'=>'input_merchant_account_id', 'placeholder'=>'Braintree Merchant Account Id']) !!}
								<small class="description" style="color: var(--color-as-9da1ab);">
					                {{ __('messages.blade_static.for_default_account_id_leave_it_as_empty_e7d2d877') }}
					            </small>
							</div>
						</div>

						<div class="form-group">
							<label for="input_bt_public" class="col-sm-3 control-label"> {{ __('messages.blade_static.braintree_public_key_1eb46702') }} <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_public_key', old('bt_public_key',demoPaymentGateway('public_key','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_public', 'placeholder' => 'Public Key']) !!}
								<span class="text-danger">{{ $errors->first('bt_public_key') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_bt_private_key" class="col-sm-3 control-label"> {{ __('messages.blade_static.braintree_private_key_d766508c') }} <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('bt_private_key', old('bt_private_key',demoPaymentGateway('private_key','Braintree')), ['class' => 'form-control', 'id' => 'input_bt_private_key', 'placeholder' => 'Private Key']) !!}
								<span class="text-danger">{{ $errors->first('bt_private_key') }}</span>
							</div>
						</div>
						<div class="form-group">
							<label for="input_tokenization_key" class="col-sm-3 control-label">
								{{ __('messages.blade_static.braintree_tokenization_key_8966b117') }}
								<span class="tooltip-custom"><i class="icon"></i> 
									<em style="margin-left: -108px;">
										{{ __('messages.blade_static.manage_the_ways_you_authorize_requests_to_braintree_for_8532de6e') }}
									</em>
								</span>
								<em class="text-danger">*</em>
							</label>
							<div class="col-md-7 col-sm-offset-1">
								{!! Form::text('tokenization_key', old('tokenization_key',demoPaymentGateway('tokenization_key','Braintree')), ['class'=>'form-control', 'id'=>'input_tokenization_key', 'placeholder'=>'Braintree Tokenization Key']) !!}
								<span class="text-danger">{{ $errors->first('tokenization_key') }}</span>
							</div>
						</div>
					</div>
					<!-- Braintree Section End -->

					@if(!empty($generic_gateways))
					<div class="box-body">
						@foreach($generic_gateways as $gateway_key => $gateway_site)
						<div class="panel panel-default" style="margin: 15px;">
							<div class="panel-heading"><strong>{{ $gateway_site }}</strong></div>
							<div class="panel-body">
								<div class="form-group">
									<label class="col-sm-3 control-label">Is {{ $gateway_site }} Enabled</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::select($gateway_key.'_enabled', ['0' => 'No', '1' => 'Yes'], old($gateway_key.'_enabled', payment_gateway('is_enabled', $gateway_site) ?: '0'), ['class' => 'form-control']) !!}
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ $gateway_site }} Mode</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::select($gateway_key.'_mode', ['sandbox' => 'Sandbox', 'live' => 'Live'], old($gateway_key.'_mode', payment_gateway('mode', $gateway_site) ?: 'sandbox'), ['class' => 'form-control']) !!}
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ $gateway_site }} Public Key</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::text($gateway_key.'_public_key', old($gateway_key.'_public_key', demoPaymentGateway('public_key', $gateway_site)), ['class' => 'form-control', 'placeholder' => $gateway_site.' Public Key']) !!}
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ $gateway_site }} Secret Key</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::text($gateway_key.'_secret_key', old($gateway_key.'_secret_key', demoPaymentGateway('secret_key', $gateway_site)), ['class' => 'form-control', 'placeholder' => $gateway_site.' Secret Key']) !!}
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ $gateway_site }} Checkout URL</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::text($gateway_key.'_checkout_url', old($gateway_key.'_checkout_url', demoPaymentGateway('checkout_url', $gateway_site)), ['class' => 'form-control', 'placeholder' => 'https://...']) !!}
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-3 control-label">{{ $gateway_site }} Webhook Secret</label>
									<div class="col-md-7 col-sm-offset-1">
										{!! Form::text($gateway_key.'_webhook_secret', old($gateway_key.'_webhook_secret', demoPaymentGateway('webhook_secret', $gateway_site)), ['class' => 'form-control', 'placeholder' => $gateway_site.' Webhook Secret']) !!}
									</div>
								</div>
							</div>
						</div>
						@endforeach
					</div>
					@endif

					<div class="form-group">
							<label for="is_web_payment" class="col-sm-3 control-label"> {{ __('messages.blade_static.web_payment_595b236f') }} <em ng-show="bt_enabled == '1'" class="text-danger">*</em></label>
							<div class="col-md-7 col-sm-offset-1">
								<input type="checkbox" name="is_web_payment" id="is_web_payment" value="1" {{old('is_web_payment',payment_gateway('is_web_payment','Common')) ==1 ? 'checked':''}} > 
							</div>
						</div>

					<!-- Payout Methods Section Start -->
						<div class="box-body">
							<div class="form-group">
								<label for="input_payout_methods" class="col-sm-3 control-label"> {{ __('messages.blade_static.payout_methods_9d7a0720') }} <em class="text-danger">*</em> </label>
								<div class="col-md-7 col-sm-offset-1">
									@foreach(PAYOUT_METHODS as $payout_method)
									<div ng-init="payout_method_{{ $payout_method['key'] }}={{ isPayoutEnabled($payout_method['key']) }}">
										<input type="checkbox" name="payout_methods[]" id="payout_method-{{ $payout_method['key'] }}" value="{{ $payout_method['key'] }}" ng-checked="{{ isPayoutEnabled($payout_method['key']) }}"> <label for="payout_method-{{ $payout_method['key'] }}" ng-model="payout_method_{{ $payout_method['key'] }}"> {{ $payout_method["value"] }} </label>
									</div>										
									@endforeach
								</div>
							</div>
					</div>
					<!-- Payout Methods Section End -->
					
					<div class="box-footer text-center">
						<button type="submit" class="btn btn-info" name="submit" value="submit">{{ __('messages.blade_static.submit_2dacf659') }}</button>
						<button type="reset" class="btn btn-default"> {{ __('messages.blade_static.cancel_77dfd213') }} </button>
					</div>
				</div>
			</div>
			{!! Form::close() !!}
		</div>
	</div>
</section>
</div>
</div>
@endsection

