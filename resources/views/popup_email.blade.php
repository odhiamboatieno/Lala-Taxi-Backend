<input type="hidden" value="{{Session::get('pop_email')}}" id="popup_mail">
<div class="modal fade cls_loadpop" id="cls_loadpop" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" ng-controller="sent_mail_to_user">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header border-0">
        
        <h5 class="modal-title" id="exampleModalLabel">{{ __('messages.blade_static.get_in_touch_with_us_for_cabme_details_4198a14a') }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('messages.blade_static.close_bbfa773e') }}">
          <span aria-hidden="true"></span>
        </button>
      </div>
      <div class="modal-body">
        <h1 class="succes_msg d-none alert alert-success"> {{ __('messages.blade_static.mail_successfully_sent_47e1c9c4') }} </h1>
        <form id="popup_mails">
      <div class="form-group row">
        <label for="input_name" class="col-sm-3 col-form-label">{{ __('messages.blade_static.name_709a2322') }}<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" class="form-control" name="name" id="input_name" placeholder="{{ __('messages.blade_static.name_709a2322') }}">
        </div>
      </div>
      <div class="form-group row align-items-center cls_cusradio">
        <label class="col-sm-3 col-form-label" for="formGroupExampleInput2">{{ __('messages.blade_static.choose_type_17aaed36') }}<em class="text-danger">*</em></label>
        <div class="col-sm-9 p-sm-0">
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="email_type" value="email" type="radio" id="customRadioInline1" name="choose_type" class="custom-control-input choose_type" checked>
          <label class="custom-control-label" for="customRadioInline1">{{ __('messages.blade_static.email_84add5b2') }}</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="skipe_type" value="skype"  type="radio" id="customRadioInline2" name="choose_type" class="custom-control-input choose_type">
          <label class="custom-control-label" for="customRadioInline2">{{ __('messages.blade_static.skype_9810c496') }}</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
          <input data-class="whatsapp_type" value="whatsapp"  type="radio" id="customRadioInline3" name="choose_type" class="custom-control-input choose_type">
          <label class="custom-control-label" for="customRadioInline3">{{ __('messages.blade_static.whatsapp_086ea71f') }}</label>
        </div>
      </div>
      </div>
      <div class="form-group row email_type">
        <label for="email_type" class="col-sm-3 col-form-label">{{ __('messages.blade_static.email_84add5b2') }}<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" name="input_email" class="form-control" id="email_type" placeholder="{{ __('messages.blade_static.email_84add5b2') }}">
        </div>
      </div>
      <div class="form-group row skipe_type d-none">
        <label for="skipe_id" class="col-sm-3 col-form-label">{{ __('messages.blade_static.skype_id_c186d5f7') }}<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <input type="text" name="input_skipe" class="form-control" id="skipe_type" placeholder="{{ __('messages.blade_static.skype_id_c8484acf') }}">
        </div>
      </div>
      <div class="form-group row whatsapp_type d-none">
        <label for="phoe_number" class="col-sm-3 col-form-label">{{ __('messages.blade_static.whatsapp_086ea71f') }}<em class="text-danger">*</em></label>
        <div class="col-sm-3 mb-2" style="padding-right: 0px;">
          {!! Form::select('pop_country_code', $country_lists, @$default_country_phone_code, ['class' => 'form-control', 'id' => 'pop_country_code', 'placeholder' => 'Country' , 'style' => '-webkit-appearance: auto;']) !!}
        </div>
        <div class="col-sm-6">
          <input type="text" class="form-control" name="pop_phone_number" id="pop_phone_number" placeholder="{{ __('messages.blade_static.phone_number_ab25d61b') }}">
        </div>
      </div>
      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label">{{ __('messages.blade_static.message_68f4145f') }}<em class="text-danger">*</em></label>
        <div class="col-sm-9">
          <textarea name="pop_message" id="pop_message" class="form-control" rows="6" style="resize: none;"></textarea>
          <small style="display: block;" id="passwordHelpInline" class="text-muted">
         {{ __('messages.blade_static.for_more_assistance_our_specialist_will_get_back_to_you_7b1003c6') }}
        </small>
        </div>
      </div>

      <div class="form-group row">
        <label for="inputEmail3" class="col-sm-3 col-form-label"></label>
        <div class="col-sm-9">
          <div id="recaptcha_id"></div>
          @if ($errors->has('g-recaptcha-response'))  
           <span class="text-danger">
             {{ $errors->first('g-recaptcha-response') }}
           </span> 
          @endif
        </div>
      </div>
    </form>
      </div>
      <div class="modal-footer border-0">
        <button type="button" id="submit_forms" class="btn btn--primary" disabled="disabled">{{ __('messages.blade_static.submit_2dacf659') }}</button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  var verifyCallback = function(response) {
    $('#submit_forms').removeAttr('disabled');
  };

  var onloadCallback = function() {
    grecaptcha.render('recaptcha_id', {
      'sitekey' : "{{api_credentials('site_key','Recaptcha')}}",
      'callback' : verifyCallback,
      'theme' : 'light'
    });
  };

</script>
