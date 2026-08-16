<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">{{ $title ?? '' }}</h5>
                    @if(!empty($addButton))
                        <div class="ms-auto">{!! $addButton !!}</div>
                    @endif
                </div>
                <div class="card-body {{ $bodyClass ?? '' }}">
                    @if(!empty($bodyHeader))
                        {!! $bodyHeader !!}
                    @endif
                    {!! $table !!}
                </div>
            </div>
        </div>
    </div>
</div>
