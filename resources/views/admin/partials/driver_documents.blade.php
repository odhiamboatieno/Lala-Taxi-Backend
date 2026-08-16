@php
    $list = [];
    $src = $documents ?? $driver_doc ?? [];
    if (is_string($src)) {
        $list = json_decode($src) ?: [];
    } else {
        $items = $src instanceof \Illuminate\Support\Collection ? $src->toArray() : (is_array($src) ? $src : []);
        $list = array_map(fn($i) => is_array($i) ? (object) $i : $i, $items);
    }
    $expireLabel = $expireLabel ?? __('messages.admin.manage_drivers_page.form.expire_date');
    $statusLabel = $statusLabel ?? __('messages.admin.manage_drivers_page.form.status');
@endphp
@if(!empty($list))
    @foreach($list as $doc)
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">
                        {{ $doc->document_name ?? '' }} <em class="text-danger">*</em>
                    </label>
                    <input type="file" name="file_{{ $doc->id ?? '' }}" class="form-control">
                    @error('file_'.($doc->id ?? ''))
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                @if(isset($doc->expire_on_date) && $doc->expire_on_date == 'Yes')
                <div class="col-md-6">
                    <label class="form-label">
                        {{ $expireLabel }} <em class="text-danger">*</em>
                    </label>
                    <input type="text"
                           name="expired_date_{{ $doc->id }}"
                           class="form-control document_expired air-datepicker"
                           placeholder="{{ __('messages.blade_static.expire_date_b5b8f80b') }}"
                           autocomplete="off"
                           min="{{ date('Y-m-d') }}">
                    @error('expired_date_'.$doc->id)
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                @endif

                <div class="col-md-6">
                    <label class="form-label">
                        {{ $doc->document_name ?? '' }} {{ $statusLabel }} <em class="text-danger">*</em>
                    </label>
                    <select class="form-select" name="{{ $doc->doc_name ?? '' }}_status">
                        <option value="0">{{ __('messages.blade_static.pending_96f608c1') }}</option>
                        <option value="1">{{ __('messages.blade_static.approved_41b81eb8') }}</option>
                        <option value="2">{{ __('messages.blade_static.rejected_27eeb7a2') }}</option>
                    </select>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="text-muted">{{ __('messages.no_documents_in_section') }}</div>
@endif
