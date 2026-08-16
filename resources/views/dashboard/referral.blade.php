<title>{{ __('messages.blade_static.referrals_2b0e3a3c') }}</title>
@extends('template_dashboard')
@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;" ng-controller="user" ng-init="apply_referral='{{ $apply_referral }}'">
	<div class=" separated--bottom  text--left" ng-cloak style="padding:0px 15px;">
		<h1 class="flush-h1 flush" ng-if="apply_referral == 1"> @lang('messages.referrals.referral_header',['amount' => $rider_referral_amount]) </h1>
		<h1 class="flush-h1 flush" ng-if="apply_referral != 1"> @lang('messages.referrals.referral_not_available') </h1>
	</div>
	<div class="" style="padding:0px 15px;" ng-init="referral_code='{{ $result->referral_code }}'" ng-cloak>
		<div class="parter-info separated--bottom" style="padding: 0px 0px 15px;" ng-if="apply_referral == 1">
			<input type="hidden" name="user_id" value="{{ @Auth::user()->id}}" id="user_id">
			<h4 class="flush-h3"> {{trans('messages.referrals.referral_code')}} : <span class="font-weight-bold" style="font-weight: bold;color: var(--color-as-1fbad6)"> @{{ referral_code }} </span> </h4>
		</div>
		<!-- <div class="parter-info separated--bottom" style="padding: 0px 0px 15px;">
			<h2 class="flush-h2" style="text-align: center;"> {{trans('messages.referrals.previous_referrals')}} </h2>
		</div> -->
		<div class="parter-info separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding: 25px 0px 15px;"  ng-init="all_referral_details={{ $all_referral_details }};currentPage=all_referral_details.current_page;totalPages=all_referral_details.last_page;">
			<div id="no-more-tables">
				<table class="col-sm-12 table-bordered table-striped table-condensed cf">
					<thead class="cf">
						<tr>
							<th class="width-60"> @lang('messages.referrals.rider_name')  </th>
							<th class="hide-sm"> @lang('messages.referrals.total_trips')  </th>
							<th class="hide-sm"> @lang('messages.referrals.trips_remaining')  </th>
							<th class="hide-sm"> @lang('messages.referrals.total_days')  </th>
							<th class="hide-sm"> @lang('messages.referrals.days_remaining')  </th>
							<th class="width-20"> @lang('messages.referrals.earnable_amount')  </th>
							<th class="width-20"> @lang('messages.referrals.status') </th>
						</tr>
					</thead>
					<tbody class="all-trips-table" ng-repeat="referral_data in all_referral_details.data">
						<tr class="trip-expand__origin collapsed" ng-cloak>
							<td data-title="{{ __('messages.blade_static.pickup_a51122c1') }}">@{{ referral_data.referred_user_name }}</td>
							<td class="hide-sm" data-title="{{ __('messages.blade_static.driver_b5c7d8b5') }}">@{{ referral_data.trips }}</td>
							<td data-title="{{ __('messages.blade_static.fare_fccb9084') }}"><span ></span>&nbsp;@{{ referral_data.remaining_trips }} </td>
							<td data-title="{{ __('messages.blade_static.car_bc47a3dc') }}">@{{ referral_data.days }}</td>
							<td data-title="{{ __('messages.blade_static.car_bc47a3dc') }}">@{{ referral_data.remaining_days }}</td>
							<td data-title="{{ __('messages.blade_static.city_4271627f') }}" class="hide-sm">@{{ referral_data.earnable_amount }}</td>
							<td class="hide-sm" data-title="{{ __('messages.blade_static.payment_method_f383f6a2') }}">
								<span class="soft-half--sides">@{{ referral_data.trans_payment_status }}</span>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div style="padding:25px;">
				<div class="pagination-buttons-container row-space-8 float--right" ng-cloak>
					<div class="results_count pagination inline-group btn-group btn-group--bordered" style="float: right;margin-top: 20px;">
						<div class="inline-group__item" ng-show="totalPages > 1">
							<referral-pagination></referral-pagination>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</div>
</main>
@stop