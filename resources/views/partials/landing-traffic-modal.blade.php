@php
    $trafficUi = [
        'title' => __('landing_traffic.modal_title'),
        'sub' => __('landing_traffic.modal_sub'),
        'total' => __('landing_traffic.modal_total'),
        'opens' => __('landing_traffic.modal_opens'),
        'loading' => __('landing_traffic.modal_loading'),
        'error' => __('landing_traffic.modal_error'),
        'empty' => __('landing_traffic.modal_empty'),
        'close' => __('landing_traffic.modal_close'),
    ];
@endphp
<div id="traffic-insights-root" class="traffic-modal-root" hidden aria-hidden="true">
    <div class="traffic-modal-backdrop" data-traffic-close tabindex="-1"></div>
    <div class="traffic-modal-shell" role="dialog" aria-modal="true" aria-labelledby="traffic-modal-title">
        <button type="button" class="traffic-modal-x" data-traffic-close aria-label="{{ __('landing_traffic.modal_close') }}">&times;</button>
        <div class="traffic-modal-inner">
            <p class="traffic-modal-kicker" aria-hidden="true">● live</p>
            <h2 id="traffic-modal-title" class="traffic-modal-title">{{ __('landing_traffic.modal_title') }}</h2>
            <p class="traffic-modal-sub">{{ __('landing_traffic.modal_sub') }}</p>
            <div id="traffic-modal-body" class="traffic-modal-body">
                <p class="traffic-modal-loading">{{ __('landing_traffic.modal_loading') }}</p>
            </div>
        </div>
    </div>
</div>
<script type="application/json" id="traffic-modal-config">
@json([
    'statsUrl' => route('landing.traffic-stats'),
    'modalUrl' => route('landing.traffic-modal'),
    'i18n' => $trafficUi,
])
</script>
