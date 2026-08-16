@extends('admin.template')

@section('pagetitle', 'Send Email')

@section('main')
<div id="layout-wrapper">

    <div class="row chat-wrapper g-0">

        {{--  ====  LEFT SIDEBAR – FORM CONTROLS  ====  --}}
        <div class="col-xl-3 border-end">
            <div class="app-chat-sidebar active p-5">

                <div class="d-flex align-items-center justify-content-between mb-4 d-none d-xl-flex">
                    <h5 class="mb-0">{{ __('messages.admin.manage_emails_page.send_email_form') }}</h5>
                    <a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}" class="btn btn-sm btn-outline-primary">
                        {{ __('messages.admin.home') }}
                    </a>
                </div>

                {!! Form::open(['url' => 'admin/send_email', 'class' => 'h-100 d-flex flex-column']) !!}

                <div class="flex-grow-1 overflow-auto">

                    <div class="mb-4">
                        <span class="text-danger">(*) {{ __('messages.admin.fields_are_mandatory') }}</span>
                    </div>

                    {{--  ====  TO (All / Specific)  ====  --}}
                    <div class="mb-4">
                        <label class="form-label">
                            {{ __('messages.admin.manage_emails_page.form.email_to') }} <em class="text-danger">*</em>
                        </label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input send_to" type="radio"
                                       name="to" id="to_all" value="to_all">
                                <label class="form-check-label" for="to_all">
                                    {{ __('messages.admin.manage_send_message_page.all') }}
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input send_to" type="radio"
                                       name="to" id="to_specific" value="to_specific" checked>
                                <label class="form-check-label" for="to_specific">
                                    {{ __('messages.admin.manage_send_message_page.specific_users') }}
                                </label>
                            </div>
                        </div>
                    </div>

                    {{--  ====  EMAIL ADDRESSES (AUTOCOMPLETE)  ====  --}}
                    <div class="mb-4" id="email_textbox">
                        <label class="form-label" for="input_email">
                            {{ __('messages.admin.manage_emails_page.form.email_address') }} <em class="text-danger">*</em>
                        </label>
                        <input type="text" class="form-control" id="input_email" name="email"
                               placeholder="{{ __('messages.blade_static.enter_email_addresses_of_users_02f92e83') }}" value="{{ old('email') }}" />
                        <span class="text-danger">{{ $errors->first('email') }}</span>
                        <input type="hidden" id="email_address_list" value="{{ $email_address_list }}">
                        <small class="text-muted d-block mt-1">
                            {{ __('messages.admin.manage_emails_page.notes') }}
                        </small>
                    </div>

                    {{--  ====  SUBJECT  ====  --}}
                    <div class="mb-4">
                        <label class="form-label" for="input_subject">
                            {{ __('messages.admin.manage_emails_page.form.subject') }} <em class="text-danger">*</em>
                        </label>
                        <input type="text" class="form-control" id="input_subject" name="subject"
                               placeholder="{{ __('messages.blade_static.subject_8d183dbd') }}" value="{{ old('subject') }}" />
                        <span class="text-danger">{{ $errors->first('subject') }}</span>
                    </div>

                </div>
                {{-- End flex-grow-1 --}}
            </div>
        </div>

        {{--  ====  RIGHT CONVERSATION AREA – EDITOR + BUTTONS  ====  --}}
        <div class="col-xl-9">
            <div class="h-100 d-flex flex-column">

                {{-- Header --}}
                <div class="p-5 border-bottom text-center">
                    <h5 class="mb-0">{{ __('messages.admin.manage_emails_page.send_email') }}</h5>
                </div>

                {{-- Scrollable Area (empty) --}}
                <div class="flex-grow-1 p-5 chat-conversation" data-simplebar>
                     {{--  ====  SUBJECT  ====  --}}
                    <div class="mb-4">
                        <label class="form-label" for="input_subject">
                            {{ __('messages.admin.manage_emails_page.form.subject') }} <em class="text-danger">*</em>
                        </label>
                        <input type="text" class="form-control" id="input_subject" name="subject"
                               placeholder="{{ __('messages.blade_static.subject_8d183dbd') }}" value="{{ old('subject') }}" />
                        <span class="text-danger">{{ $errors->first('subject') }}</span>
                    </div>
                     <div class="mb-3">
                        <label class="form-label visually-hidden">
                            {{ __('messages.admin.manage_emails_page.form.message') }}
                        </label>
                        <div id="txtEditor"></div>
                        <textarea id="message" name="message" hidden>{{ old('message') }}</textarea>
                        <span class="text-danger d-block mt-1">{{ $errors->first('message') }}</span>
                    </div>

                  
                </div>

                {{--  ====  RICH TEXT EDITOR + HIDDEN FIELD  ====  --}}
                <div class="border-top p-5">
                     {{--  ====  SUBMIT / CANCEL BUTTONS  ====  --}}
                    <div class="d-flex justify-content-center gap-2">
                        <button type="submit" name="submit" value="submit"
                                class="btn btn-primary">{{ __('messages.admin.submit') }}</button>
                        <a href="{{ url('admin/send_email') }}"
                           class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
                    </div>
                </div>

                {!! Form::close() !!}
            </div>
        </div>

    </div>
</div>
</main>
@endsection


@push('scripts')
{{-- jQuery UI Autocomplete --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

{{-- Editor.js (or your WYSIWYG) --}}
<script src="{{ url('js/editor.js') }}"></script> {{-- Replace with your actual path --}}

<script>
$(function () {
    // === 1. Initialize WYSIWYG Editor ===
    $("#txtEditor").Editor();
    $('.Editor-editor').html($('#message').val());

    // Sync editor content to hidden textarea before submit
    $('form').on('submit', function () {
        $('#message').val($('.Editor-editor').html());
    });

    // === 2. Show/hide email textbox ===
    $('.send_to').on('change', function () {
        $('#email_textbox').toggle(this.value === 'to_specific');
    }).trigger('change'); // init state

    // === 3. jQuery UI Autocomplete for Emails ===
    var emailList = $('#email_address_list').val();
    emailList = emailList ? $.parseJSON(emailList) : [];

    function split(val) {
        return val.split(/,\s*/);
    }
    function extractLast(term) {
        return split(term).pop();
    }

    $("#input_email")
        .on("keydown", function (event) {
            if (event.keyCode === $.ui.keyCode.TAB && $(this).autocomplete("instance").menu.active) {
                event.preventDefault();
            }
        })
        .autocomplete({
            minLength: 0,
            source: function (request, response) {
                response($.ui.autocomplete.filter(emailList, extractLast(request.term)));
            },
            focus: function () { return false; },
            select: function (event, ui) {
                var terms = split(this.value);
                terms.pop();
                terms.push(ui.item.value);
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });

    // === 4. Restore state on validation error ===
    @if($errors->has('email'))
        $("#to_specific").prop('checked', true);
        $('#email_textbox').show();
    @endif
});
</script>
@endpush