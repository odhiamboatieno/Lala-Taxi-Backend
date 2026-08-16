@extends('admin.template')

@section('title-sub', __('messages.admin.manage_banner_page.title_edit'))
@section('pagetitle', __('messages.admin.manage_banner_page.title_edit'))

@section('main')
    <div id="layout-wrapper">
        <div class="row">
            <div class="col-xl-7">
                <div class="card card-h-100">
                    <div class="card-body">
                        {!! Form::open(['url' => LOGIN_USER_TYPE.'/edit_banner/'.$result->id, 'class' => 'row g-4', 'files' => true]) !!}
                        <div class="col-12">
                            <h6 class="mb-2 text-primary">{{ __('messages.admin.manage_banner_page.banner_content') }}</h6>
                            <p class="text-danger mb-0">{{ __('messages.admin.manage_banner_page.fields_required') }}</p>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.internal_name') }} <em class="text-danger">*</em></label>
                            {!! Form::text('name', old('name', $result->name), ['class' => 'form-control', 'placeholder' => __('messages.admin.manage_banner_page.ride_hero')]) !!}
                            <span class="text-danger">{{ $errors->first('name') }}</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.banner_type') }} <em class="text-danger">*</em></label>
                            {!! Form::select('type', ['Ride' => __('messages.admin.manage_banner_page.ride'), 'Parcel' => __('messages.admin.manage_banner_page.parcel')], old('type', $result->type ?? 'Ride'), ['class' => 'form-select']) !!}
                            <span class="text-danger">{{ $errors->first('type') }}</span>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.title') }} <em class="text-danger">*</em></label>
                            {!! Form::text('title', old('title', $result->title ?? $result->name), ['class' => 'form-control', 'placeholder' => __('messages.admin.manage_banner_page.ride_smarter')]) !!}
                            <span class="text-danger">{{ $errors->first('title') }}</span>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.subtitle') }} <em class="text-danger">*</em></label>
                            {!! Form::text('subtitle', old('subtitle', $result->subtitle), ['class' => 'form-control', 'placeholder' => __('messages.admin.manage_banner_page.book_faster')]) !!}
                            <span class="text-danger">{{ $errors->first('subtitle') }}</span>
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.optional_link') }}</label>
                            {!! Form::text('link', old('link', $result->link), ['class' => 'form-control', 'placeholder' => __('messages.admin.manage_banner_page.ride_parcel_action')]) !!}
                            <span class="text-danger">{{ $errors->first('link') }}</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.status') }} <em class="text-danger">*</em></label>
                            {!! Form::select('status', ['Active' => __('messages.admin.manage_banner_page.active'), 'Inactive' => __('messages.admin.manage_banner_page.inactive')], old('status', $result->status), ['class' => 'form-select']) !!}
                            <span class="text-danger">{{ $errors->first('status') }}</span>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">{{ __('messages.admin.manage_banner_page.image') }}</label>
                            {!! Form::file('image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                            <span class="text-danger">{{ $errors->first('image') }}</span>
                        </div>

                        <div class="col-12 d-flex align-items-center gap-3">
                            @if($result->image)
                                <img src="{{ url('images/banner/'.$result->image) }}" class="img-thumbnail" style="width: 120px; height: 90px; object-fit: cover;" alt="{{ __('messages.admin.manage_banner_page.banner_image_alt') }}">
                            @endif
                            <span class="text-muted">{{ __('messages.admin.manage_banner_page.upload_new_image') }}</span>
                        </div>

                        <div class="col-12 text-end">
                            <button type="submit" name="cancel" value="cancel" class="btn btn-light-primary">{{ __('messages.admin.manage_banner_page.cancel') }}</button>
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">{{ __('messages.admin.manage_banner_page.submit') }}</button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>

            <div class="col-xl-5">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">{{ __('messages.admin.manage_banner_page.usage') }}</h6>
                        <ul class="mb-0">
                            <li>{!! __('messages.admin.manage_banner_page.ride_description') !!}</li>
                            <li>{!! __('messages.admin.manage_banner_page.parcel_description') !!}</li>
                            <li>{{ __('messages.admin.manage_banner_page.title_subtitle_description') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
