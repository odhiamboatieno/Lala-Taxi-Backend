@extends('admin.template')

@section('title', 'Overall Statements | FabKin Admin & Dashboards Template')
@section('title-sub', 'Earnings Overview')
@section('pagetitle', 'Statements')

@section('css')
    <!-- Datepicker CSS -->
    <link rel="stylesheet" href="assets/libs/air-datepicker/air-datepicker.css">

    <style>
        .loading-overlay {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
            border-radius: 8px;
        }
        .stat-card {
            transition: all 0.25s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.12) !important;
        }
        .filter-form .form-group {
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        .filter-form .btn {
            margin-top: 1.8rem;
        }
        @media (max-width: 992px) {
            .filter-form {
                flex-direction: column !important;
            }
            .filter-form .form-group {
                width: 100%;
                margin-right: 0;
            }
        }
    </style>
@endsection

@section('main')
    <!-- begin::App -->
    <div id="layout-wrapper" ng-controller="statements" ng-cloak>

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                            <h5 class="card-title mb-0">
                                {{ __('messages.admin.manage_statements.overall') }}
                                <small class="text-muted ms-2">{{ __('messages.admin.manage_statements.statements') }}</small>
                            </h5>

                            <!-- Optional: refresh / export buttons -->
                            <!-- <button class="btn btn-sm btn-outline-secondary" ng-click="refreshData()">{{ __('messages.blade_static.refresh_56e3badc') }}</button> -->
                        </div>

                        <!-- Filter Form -->
                        <form id="custom_statement" class="filter-form d-flex flex-wrap align-items-end mb-5" role="form">
                            <div class="form-group">
                                <label for="filter_by" class="form-label">
                                    {{ __('messages.admin.manage_statements.filter_by') }}
                                </label>
                                <select ng-model="filter_by" 
                                        ng-init="filter_by = filter_by || 'overall'"
                                        class="form-select" 
                                        name="filter_by" 
                                        id="filter_by">
                                    <option value="overall">{{ __('messages.admin.manage_statements.overall') }}</option>
                                    <option value="daily">{{ __('messages.admin.manage_statements.today') }}</option>
                                    <option value="weekly">{{ __('messages.admin.manage_statements.weekly') }}</option>
                                    <option value="monthly">{{ __('messages.admin.manage_statements.monthly') }}</option>
                                    <option value="yearly">{{ __('messages.admin.manage_statements.yearly') }}</option>
                                    <option value="custom">{{ __('messages.admin.manage_statements.custom') }}</option>
                                </select>
                            </div>

                            <div class="form-group" ng-if="filter_by === 'custom'">
                                <label for="from_date" class="form-label">
                                    {{ __('messages.admin.manage_statements.from_date') }}
                                </label>
                                <input type="text" 
                                       class="form-control air-datepicker" 
                                       name="from_date" 
                                       id="from_date" 
                                       placeholder="{{ __('messages.blade_static.from_date_6b7587a9') }}" 
                                       autocomplete="off">
                            </div>

                            <div class="form-group" ng-if="filter_by === 'custom'">
                                <label for="to_date" class="form-label">
                                    {{ __('messages.admin.manage_statements.to_date') }}
                                </label>
                                <input type="text" 
                                       class="form-control air-datepicker" 
                                       name="to_date" 
                                       id="to_date" 
                                       placeholder="{{ __('messages.blade_static.to_date_642cced3') }}" 
                                       autocomplete="off">
                            </div>

                            <div class="form-group">
                                <button type="submit" 
                                        class="btn btn-primary">
                                    {{ __('messages.admin.manage_statements.search') }}
                                </button>
                            </div>
                        </form>

                        <!-- Stats Cards -->
                        <div class="row g-4 mb-5">
                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card stat-card bg-gradient-primary text-white shadow">
                                    <div class="card-body">
                                        <h3 class="mb-1">@{{ overall_earning }}</h3>
                                        <p class="mb-0">
                                            {{ LOGIN_USER_TYPE == 'company' 
                                                ? __('messages.admin.manage_statements.table.earnings') 
                                                : __('messages.admin.manage_statements.total_amount_received') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card stat-card bg-gradient-info text-white shadow">
                                    <div class="card-body">
                                        <h3 class="mb-1">@{{ overall_commission }}</h3>
                                        <p class="mb-0">
                                            {{ LOGIN_USER_TYPE == 'company' 
                                                ? __('messages.admin.manage_statements.table.admin_commission') 
                                                : __('messages.admin.manage_statements.total_earnings') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card stat-card bg-gradient-success text-white shadow">
                                    <div class="card-body">
                                        <h3 class="mb-1">@{{ overall_rides }}</h3>
                                        <p class="mb-0">{{ __('messages.admin.manage_statements.completed_rides') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-lg-6 col-md-6">
                                <div class="card stat-card bg-gradient-warning text-white shadow">
                                    <div class="card-body">
                                        <h3 class="mb-1">@{{ cancelled_rides }}</h3>
                                        <p class="mb-0">{{ __('messages.admin.manage_statements.cancelled_rides') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="position-relative">
                            <div ng-if="isLoading" class="loading-overlay">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">{{ __('messages.blade_static.loading_b04ba49f') }}</span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="statement_table" class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th ng-repeat="column in table_columns">@{{ column.title }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="statement in statements">
                                            <td ng-repeat="column in table_columns">
                                                <span ng-if="column.name != 'action'">
                                                    @{{ statement[column.data] || '—' }}
                                                </span>
                                                <span ng-if="column.name == 'action'">
                                                    <a href="@{{ statement[column.data] }}" 
                                                       class="btn btn-sm btn-primary">
                                                        {{ __('messages.admin.manage_statements.view_trip_details') }}
                                                    </a>
                                                </span>
                                            </td>
                                        </tr>
                                        <tr ng-if="statements.length === 0 && !isLoading">
                                            <td colspan="8" class="text-center text-muted py-5">
                                                {{ __('messages.admin.no_records_found') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-end mt-4" ng-if="totalPages > 1">
                                <statements-pagination></statements-pagination>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- End row -->

    </div><!-- End layout-wrapper -->
@endsection

@push('scripts')
<link rel="stylesheet" href="{{ url('css/buttons.dataTables.css') }}">
<script src="{{ url('js/dataTables.buttons.js') }}"></script>
<script src="{{ url('js/buttons.server-side.js') }}"></script>

<script>

app.controller('statements', ['$scope', '$http', function($scope, $http) {
	$scope.count_text = "Overall Statement";
	$scope.isLoading = true;

	$('#custom_statement').on('submit', function(event) {
		$scope.getStatmentData();
		event.preventDefault();
	});

	$scope.getStatementCounts = function() {
		$http.post(APP_URL+"/{{LOGIN_USER_TYPE}}/get_statement_counts", { from_dates: $('#from_date').val(), to_dates: $('#to_date').val(), filter_type: $('#filter_by').val() }).then(function( response ) {
			$scope.count_text=response.data.count_text;
			$scope.overall_earning=response.data.overall_earning;
			$scope.overall_commission=response.data.overall_commission;
			$scope.overall_rides=response.data.total_rides;
			$scope.cancelled_rides=response.data.cancelled_rides;
			setTimeout(() => $('button[type="submit"]').prop('disabled', false) , 0);
			$scope.isLoading = false;
		});
	};

	$scope.statements = [];
	$scope.totalPages = 0;
	$scope.currentPage = 1;
	$scope.range = [];
	$scope.offset = 3;

	$scope.generatepageNumber = function(pagination) {
		if (!pagination.to) {
			return [];
		}
		let from = pagination.current_page - $scope.offset;
		if (from < 1) {
			from = 1;
		}
		let to = from + ($scope.offset * 2);
		if (to >= pagination.last_page) {
			to = pagination.last_page;
		}
		let pagesArray = [];
		for (let page = from; page <= to; page++) {
			pagesArray.push(page);
		}
		return pagesArray;
	};

	$scope.getStatmentData = function(pageNumber = 1) {
		$scope.isLoading = true;
		let url = '{{ url(LOGIN_USER_TYPE."/statement_all")  }}';
		let data = {
			filter_type : $('#filter_by').val(),
			from_dates : $('#from_date').val(),
			to_dates : $('#to_date').val(),
		};

		$http.post(url+'?page='+pageNumber,data).then(function(result) {
			let response = result.data;
			$scope.getStatementCounts();
			$scope.statements   = response.data;
			$scope.totalPages   = response.last_page;
			$scope.currentPage  = response.current_page;

			$scope.range = $scope.generatepageNumber(response);
		});
	};

	let bookingId = "{{__('messages.admin.manage_statements.table.booking_id')}}";
	let companyName = "{{__('messages.admin.manage_statements.table.company_name')}}";
	let pickupLocation = "{{__('messages.admin.manage_statements.table.pickup_location')}}";
	let dropLocation = "{{__('messages.admin.manage_statements.table.drop_location')}}";
	let adminCommission = "{{__('messages.admin.manage_statements.table.admin_commission')}}";
	let datedOn = "{{__('messages.admin.manage_statements.table.dated_on')}}";
	let adminPayoutStatus = "{{__('messages.admin.manage_statements.table.admin_payout_status')}}";
	let tripDetails = "{{__('messages.admin.manage_statements.table.trip_details')}}";
	let Status = "{{__('messages.admin.manage_statements.table.status')}}";
	let Earned = "{{__('messages.admin.manage_statements.table.earned')}}";

	$scope.table_columns = [{data: 'id', name: 'id', title: bookingId}];
	if(LOGIN_USER_TYPE == 'admin') {
		$scope.table_columns.push({data: 'company_name', name: 'company_name', title: companyName});
	}
	$scope.table_columns.push(
		{data: 'pickup_location',name: 'pickup_location',title: pickupLocation},
		{data: 'drop_location',name: 'drop_location',title:dropLocation},
		{data: 'action',name: 'action',title: tripDetails,orderable: false,searchable: false},
		{data: 'commission',name: 'commission',title: adminCommission,searchable: false},
		{data: 'admin_payout_status',name: 'admin_payout_status',title: adminPayoutStatus,orderable: false,searchable: false},
		{data: 'created_at',name: 'created_at',title: datedOn},
		{data: 'status',name: 'status',title: Status},
		{data: 'total_amount',name: 'driver_payout',title: Earned}
	);
	
	$(document).ready(function() {
		$scope.getStatmentData();
		$( "#filter_by").change(function() {
			var value = $("#filter_by option:selected").val();
			if(value =='orders') {
				$('#from_date').datepicker('option', 'maxDate', '')
				$('#from_date').datepicker('refresh');
				$('#to_date').datepicker('option', 'maxDate', '')
				$('#to_date').datepicker('refresh');
			}
			else {
				var nowTemp = new Date();
				var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
				var checkin = $('#from_date').datepicker({
					minDate: '-1',
					forceParse: false,
					multidate: false,
					autoclose: true
				}).on('changeDate', function(ev) {
					if (ev.date.valueOf() > checkout.datepicker("getDate").valueOf() || !checkout.datepicker("getDate").valueOf()) {
						var newDate = new Date(ev.date);
						newDate.setDate(newDate.getDate() + 1);
						checkout.datepicker("update", newDate);
					}
					var newDate = new Date(ev.date);
					newDate.setDate(newDate.getDate() + 1);
					checkout.datepicker("setStartDate", newDate);
					$('#to_date')[0].focus();
				});
				var checkout = $('#to_date').datepicker({
					minDate: '0',
					forceParse: false,
					beforeShowDay: function(date) {
						if (!checkin.datepicker("getDate").valueOf()) {
							return date.valueOf() >= new Date().valueOf();
						}
						return date.valueOf() > checkin.datepicker("getDate").valueOf();
					},
					autoclose: true
				});
			}
		});
	});
}]);
</script>
@endpush
