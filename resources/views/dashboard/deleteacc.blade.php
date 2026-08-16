<title>{{ __('messages.blade_static.delete_profile_a8ec88b7') }}</title>
@extends('template_dashboard')
@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;" ng-controller="facebook_account_kit">
	<div class="separated--bottom  text--left" style="padding:0px 15px;">
		<h1 class="cls_profiletitle">{{trans('messages.header.profile')}}</h1>
	</div>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
            
                <strong>Delete {{ @Auth::user()->first_name}} {{ @Auth::user()->last_name}}'s account?</strong></div>
                <div class="card-body">
                <form action="delete" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="form-group">
                    <label for="name">{{ __('messages.blade_static.account_email_6df43912') }}</label>
                    <input type="text" name ="email" value="{{ @Auth::user()->email}}" class="form-control" readonly>
                    <div class="form-group">
                    <div class="text-centre">
                    <p></p>
                    <button type="button" data_userid="{{ @Auth::user()->id}}" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                        Delete
                    </button>
                    </div>
                    </form>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">{{ __('messages.blade_static.are_you_sure_410bf23a') }}</h5>
        <form action="{{ route('deleteacc', ['user' => @Auth::user()->id]) }}" method="POST">
        <!-- Form::open(['method' => 'DELETE', 'route' => ['deleteacc.destroy', @Auth::user()->id]]) }} -->

        
        {{method_field('delete')}}
        {{csrf_field()}}
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.blade_static.close_bbfa773e') }}">
          <span aria-hidden="true">{{ __('messages.blade_static.text_77d7c977') }}</span>
        </button>
      </div>

      <div class="modal-body">
        {{ __('messages.blade_static.are_you_sure_you_want_to_permanetly_delete_your_account_6c5a46ec') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('messages.blade_static.no_cancel_bd5f41d6') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('messages.blade_static.yes_delete_my_account_55e8cae7') }}</button>
      </div>
      </form>
    </div>
  </div>
</div>
</main>
@stop