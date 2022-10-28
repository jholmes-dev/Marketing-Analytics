<div class="report-info-box">

    <div class="rib-header">
        <p>{{ $title }}</p>
    </div>

    <div class="rib-body">
        <p>{{ $content }}</p>
    </div>

    @if (isset($footer) && $footer !== '') 
    <div class="rib-footer">
        <p>{!! $footer !!}</p>
    </div>
    @endif

    @if (isset($tooltip) && $tooltip !== '') 
    <div class="rib-tooltip">
        <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{ $tooltip }}"></i>
    </div>
    @endif

</div>