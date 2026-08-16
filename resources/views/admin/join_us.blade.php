@extends('admin.template')

@section('main')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        {{__('messages.admin.join_us_links.join_us')}}
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> {{ __('messages.admin.home') }}</a></li>
        <li><a href="{{ url(LOGIN_USER_TYPE.'/join_us') }}">   {{__('messages.admin.join_us_links.join_us')}}</a></li>
        <li class="active">{{ __('messages.admin.edit') }}</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-12">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">   {{__('messages.admin.join_us_links.join_us_form')}}</h3>
            </div>
            <!-- /.box-header -->

            <!-- form start -->
              {!! Form::open(['url' => 'admin/join_us', 'class' => 'form-horizontal']) !!}
              <div class="box-body">
                <div class="form-group">
                  <label for="input_facebook" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.facebook')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('facebook', $result[0]->value, ['class' => 'form-control', 'id' => 'input_facebook', 'placeholder' => __('messages.admin.join_us_links.form.facebook')]) !!}
                    <span class="text-danger">{{ $errors->first('facebook') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_twitter" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.twitter')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('twitter', $result[1]->value, ['class' => 'form-control', 'id' => 'input_twitter', 'placeholder' => __('messages.admin.join_us_links.form.twitter')]) !!}
                    <span class="text-danger">{{ $errors->first('twitter') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_linkedin" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.linkedin')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('linkedin', $result[2]->value, ['class' => 'form-control', 'id' => 'input_linkedin', 'placeholder' => __('messages.admin.join_us_links.form.linkedin')]) !!}
                    <span class="text-danger">{{ $errors->first('linkedin') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_pinterest" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.pinterest')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('pinterest', $result[3]->value, ['class' => 'form-control', 'id' => 'input_pinterest', 'placeholder' => __('messages.admin.join_us_links.form.pinterest')]) !!}
                    <span class="text-danger">{{ $errors->first('pinterest') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_youtube" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.youtube')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('youtube', $result[4]->value, ['class' => 'form-control', 'id' => 'input_youtube', 'placeholder' => __('messages.admin.join_us_links.form.youtube')]) !!}
                    <span class="text-danger">{{ $errors->first('youtube') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_instagram" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.instagram')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('instagram', $result[5]->value, ['class' => 'form-control', 'id' => 'input_instagram', 'placeholder' => __('messages.admin.join_us_links.form.instagram')]) !!}
                    <span class="text-danger">{{ $errors->first('instagram') }}</span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_app_store" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.app_store_rider')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('app_store_rider', $result[6]->value, ['class' => 'form-control', 'id' => 'input_app_store', 'placeholder' => 'App store link']) !!}
                    <span class="text-danger">{{ $errors->first('app_store_rider') }}</span>
                  </div>
                </div>

                   <div class="form-group">
                  <label for="input_app_store" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.app_store_driver')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('app_store_driver', $result[7]->value, ['class' => 'form-control', 'id' => 'input_app_store', 'placeholder' => 'App store link']) !!}
                    <span class="text-danger">{{ $errors->first('app_store_driver') }}</span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_play_store" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.play_store_rider')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('play_store_rider', $result[8]->value, ['class' => 'form-control', 'id' => 'input_play_store', 'placeholder' => 'Play store link']) !!}
                    <span class="text-danger">{{ $errors->first('play_store_rider') }}</span>
                  </div>
                </div>
                    <div class="form-group">
                  <label for="input_play_store" class="col-sm-3 control-label">{{__('messages.admin.join_us_links.form.play_store_driver')}}</label>
                  <div class="col-md-7 col-sm-offset-1">
                    {!! Form::text('play_store_driver', $result[9]->value, ['class' => 'form-control', 'id' => 'input_play_store', 'placeholder' => 'Play store link']) !!}
                    <span class="text-danger">{{ $errors->first('play_store_driver') }}</span>
                  </div>
                </div>
              </div>
              <!-- /.box-body -->
              <div class="box-footer text-center">
                 <button type="submit" class="btn btn-info" name="submit" value="submit">{{ __('messages.admin.submit') }}</button>
                 <button type="submit" class="btn btn-default" name="cancel" value="cancel">{{ __('messages.admin.cancel') }}</button>
              </div>
              <!-- /.box-footer -->
            {!! Form::close() !!}
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
@stop