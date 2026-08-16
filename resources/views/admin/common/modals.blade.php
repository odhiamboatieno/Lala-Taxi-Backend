{{-- Confirm delete and payout modals for admin (used by DataTables action buttons) --}}
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">{{ __('messages.blade_static.text_77d7c977') }}</button>
                <h4 class="modal-title" id="myModalLabel">{{ __('messages.blade_static.confirm_delete_59e5bdd4') }}</h4>
            </div>
            <div class="modal-body">
                <p>{{ __('messages.blade_static.you_are_about_to_delete_one_track_this_procedure_is_irre_41ecff3c') }}</p>
                <p>{{ __('messages.blade_static.do_you_want_to_proceed_c927ea0d') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default confirm-delete_cancel" data-dismiss="modal">{{ __('messages.blade_static.cancel_77dfd213') }}</button>
                <a class="btn btn-danger btn-ok confirm-delete">{{ __('messages.blade_static.delete_f6fdbe48') }}</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="payout-details" tabindex="-1" role="dialog" aria-labelledby="payout-details" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header text-center">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">{{ __('messages.blade_static.text_77d7c977') }}</button>
                <h3 class="modal-title" id="payout-details"> {{ __('messages.blade_static.payout_details_c9a9fb1a') }} </h3>
            </div>
            <div class="modal-body">
                <table class="table" id="payout_details"></table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal"> {{ __('messages.blade_static.close_bbfa773e') }} </button>
            </div>
        </div>
    </div>
</div>
