@extends('admin.template')

@section('main')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" ng-controller="reports">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        {{ __('messages.blade_static.trips_d82b7e45') }}
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> {{ __('messages.blade_static.home_70f8bb9a') }}</a></li>
        <li><a href="{{ url(LOGIN_USER_TYPE.'/trips') }}">{{ __('messages.blade_static.trips_d82b7e45') }}</a></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border" style="height: 54px;">
              <!-- <h3 class="box-title">{{ __('messages.blade_static.trips_form_e8dbbb9e') }}</h3> -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-1 control-label">{{ __('messages.blade_static.from_3f66052a') }}</label>
                  <div class="col-sm-2">
                  <input type="text" ng-model="from" ng-change="report(from, to)" class="form-control date" placeholder="{{ __('messages.blade_static.from_date_6b7587a9') }}">
                  </div>
                  <label class="col-sm-1 control-label">{{ __('messages.blade_static.to_ae79ea1e') }}</label>
                  <div class="col-sm-2">
                  <input type="text" ng-model="to" ng-change="report(from, to)" class="form-control date" placeholder="{{ __('messages.blade_static.to_date_642cced3') }}">
                  </div>
                 
                </div>
            </div>
            
            <div class="box-body print_area" id="trips" ng-show="reservations_report.length">
            <div class="text-center"><h4>Trips Report (@{{ from }} - @{{ to }})</h4></div>
              <table class="table">
                <thead>
                  <th>{{ __('messages.blade_static.id_474ae526') }}</th>
                  <th>{{ __('messages.blade_static.from_location_e1dee3f1') }}</th>
                  <th>{{ __('messages.blade_static.to_location_232dc136') }}</th>
                  <th>{{ __('messages.blade_static.date_eb9a4bc1') }}</th>
                  <th>{{ __('messages.blade_static.driver_name_29722199') }}</th>
                  <th>{{ __('messages.blade_static.rider_name_e00281cb') }}</th>
                  <th>{{ __('messages.blade_static.fare_fccb9084') }}</th>
                  <th>{{ __('messages.blade_static.vehicle_details_ef6dde72') }}</th>
                  <th>{{ __('messages.blade_static.status_bae7d5be') }}</th>
                  <th>{{ __('messages.blade_static.created_at_5db1542e') }}</th>
                  <th>{{ __('messages.blade_static.action_97c89a4d') }}</th>
                </thead>
                <tbody>
                  <tr ng-repeat="item in reservations_report">
                    <td>@{{ item.id }}</td>
                    <td>@{{ item.pickup_location }}</td>
                    <td>@{{ item.drop_location }}</td>
                    <td>@{{ item.begin_trip }}</td>
                    <td>@{{ item.driver_name }}</td>
                    <td>@{{ item.rider_name }}</td>
                    <td><span ng-bind-html="item.total_amount"></span></td>
                    <td>@{{ item.car_name }}</td>
                    <td>@{{ item.status }}</td>
                    <td>@{{ item.created_at }}</td>
                    <td ><a href="{{url('admin/view_trips')}}/@{{item.id}}"><i class="glyphicon glyphicon-edit"></i></a></td>
                  </tr>
                </tbody>
              </table>
              <br>
            </div>
            <div class="text-center" id="print_footer" ng-show="users_report.length || rooms_report.length || reservations_report.length">
              <a class="btn btn-success" id="export" href="{{ url('admin/trips/export') }}/@{{ from }}/@{{ to }}"><i class="fa fa-file-excel-o"></i> {{ __('messages.blade_static.export_f3e4fadb') }}</a>
              <button class="btn btn-info" ng-click="print(category)"><i class="fa fa-print"></i> {{ __('messages.blade_static.print_5b221e9c') }}</button>
            </div>
            <br>
          </div>
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <style type="text/css">
  @media print {
  body * {
    visibility: hidden;
  }
  .print_area * {
    visibility: visible;
  }
  .print_area {
    position: absolute;
    left: 0;
    top: 0;
  }
}
</style>
@stop