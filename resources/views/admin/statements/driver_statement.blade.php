@extends('admin.template')

@section('title-sub', 'Driver Statements')
@section('pagetitle', 'Driver Statements')

@section('css')
<style>
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--color-as-rgba-255-255-255-0-7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 6px;
    }
    .card-stat {
        transition: transform 0.2s ease;
    }
    .card-stat:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 20px var(--color-as-rgba-0-0-0-0-08);
    }
</style>
@endsection

@section('main')
<div id="layout-wrapper" ng-controller="statements">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                        <div>
                            <h5 class="card-title mb-1">{{ __('messages.blade_static.driver_statements_1619a38f') }}</h5>
                            <p class="text-muted mb-0">{{ $count_text }}</p>
                        </div>
                        <span class="badge bg-light text-dark border">Driver ID: {{ $driver_id }}</span>
                    </div>

                    <div class="row g-4 mb-5">
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card card-stat border-0 shadow-sm bg-gradient-primary text-white">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $overall_earning }}</h3>
                                    <p class="mb-0">{{ LOGIN_USER_TYPE == 'company' ? __('messages.admin.manage_statements.table.earnings') : __('messages.admin.manage_statements.total_amount_received') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card card-stat border-0 shadow-sm bg-gradient-info text-white">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $overall_commission }}</h3>
                                    <p class="mb-0">{{ LOGIN_USER_TYPE == 'company' ? __('messages.admin.manage_statements.table.admin_commission') : __('messages.admin.manage_statements.total_earnings') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card card-stat border-0 shadow-sm bg-gradient-success text-white">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $overall_rides }}</h3>
                                    <p class="mb-0">{{ __('messages.admin.manage_statements.completed_rides') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-lg-6 col-md-6">
                            <div class="card card-stat border-0 shadow-sm bg-gradient-warning text-white">
                                <div class="card-body">
                                    <h3 class="mb-1">{{ $cancelled_rides }}</h3>
                                    <p class="mb-0">{{ __('messages.admin.manage_statements.cancelled_rides') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="driver_id" value="{{ $driver_id }}">

                    <div class="position-relative">
                        <div ng-if="isLoading" class="loading-overlay">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">{{ __('messages.blade_static.loading_b04ba49f') }}</span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="driver_statement_table" class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th ng-repeat="column in table_columns">@{{ column.title }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr ng-repeat="statement in statements">
                                        <td ng-repeat="column in table_columns">
                                            <span ng-if="column.name != 'action'">@{{ statement[column.data] || '-' }}</span>
                                            <span ng-if="column.name == 'action'">
                                                <a href="@{{ statement[column.data] }}" class="btn btn-sm btn-primary">{{ __('messages.blade_static.view_trip_details_89480175') }}</a>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr ng-if="statements.length === 0 && !isLoading">
                                        <td colspan="8" class="text-center text-muted py-4">{{ __('messages.blade_static.no_statements_found_for_this_driver_7dffc0f6') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4" ng-if="totalPages > 1">
                            <statements-pagination></statements-pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="{{ url('css/buttons.dataTables.css') }}">
    <script src="{{ url('js/dataTables.buttons.js') }}"></script>
    <script src="{{ url('js/buttons.server-side.js') }}"></script>
    <script>
        app.controller('statements', function($scope, $http) {
            let bookingId       = "{{ __('messages.admin.manage_statements.table.booking_id') }}";
            let tripDetails     = "{{ __('messages.admin.manage_statements.table.trip_details') }}";
            let pickupLocation  = "{{ __('messages.admin.manage_statements.table.pickup_location') }}";
            let dropLocation    = "{{ __('messages.admin.manage_statements.table.drop_location') }}";
            let adminCommission = "{{ __('messages.admin.manage_statements.table.admin_commission') }}";
            let statusText      = "{{ __('messages.admin.manage_statements.table.status') }}";
            let earnedText      = "{{ __('messages.admin.manage_statements.table.earned') }}";
            let datedOn         = "{{ __('messages.admin.manage_statements.table.dated_on') }}";

            $scope.table_columns = [
                { data: 'id', name: 'id', title: bookingId },
                { data: 'pickup_location', name: 'pickup_location', title: pickupLocation },
                { data: 'drop_location', name: 'drop_location', title: dropLocation },
                { data: 'action', name: 'action', title: tripDetails },
                { data: 'commission', name: 'commission', title: adminCommission },
                { data: 'created_at', name: 'created_at', title: datedOn },
                { data: 'status', name: 'status', title: statusText },
                { data: 'total_amount', name: 'total_amount', title: earnedText }
            ];

            $scope.isLoading = true;
            $scope.statements = [];
            $scope.totalPages = 0;
            $scope.currentPage = 1;
            $scope.range = [];
            $scope.offset = 3;

            $scope.generatepageNumber = function(pagination) {
                if (!pagination.to) return [];
                let from = pagination.current_page - $scope.offset;
                if (from < 1) from = 1;
                let to = from + ($scope.offset * 2);
                if (to >{{ __('messages.blade_static.pagination_last_page_to_pagination_last_page_let_pagesar_bf31e3e4') }} <= to; page++) {
                    pagesArray.push(page);
                }
                return pagesArray;
            };

            $scope.getStatmentData = function(pageNumber = 1) {
                $scope.isLoading = true;
                let url = '{{ url(LOGIN_USER_TYPE . '/driver_statement') }}';
                let data = { driver: $('#driver_id').val() };

                $http.post(url + '?page=' + pageNumber, data)
                    .then(function(response) {
                        $scope.statements = response.data.data || [];
                        $scope.totalPages = response.data.last_page || 1;
                        $scope.currentPage = response.data.current_page || 1;
                        $scope.range = $scope.generatepageNumber(response.data);
                    })
                    .finally(function() {
                        $scope.isLoading = false;
                    });
            };

            angular.element(document).ready(function() {
                $scope.getStatmentData();
            });
        });
    </script>
@endpush
