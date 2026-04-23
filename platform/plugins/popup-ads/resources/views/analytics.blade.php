@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="container-fluid">

        {{-- Back + title --}}
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('popup-ads.index') }}" class="btn btn-secondary btn-sm">
                <i class="ti ti-arrow-left"></i> {{ trans('core/base::forms.back') }}
            </a>
            <h4 class="mb-0">{{ trans('plugins/popup-ads::popup-ads.analytics.title', ['name' => $popupAd->name]) }}</h4>
        </div>

        {{-- Period selector --}}
        <div class="mb-4">
            @foreach ([7, 14, 30] as $period)
                <a
                    href="{{ route('popup-ads.analytics', ['popupAd' => $popupAd->id, 'days' => $period]) }}"
                    class="btn btn-sm {{ $days == $period ? 'btn-primary' : 'btn-outline-secondary' }} me-1"
                >
                    {{ trans('plugins/popup-ads::popup-ads.analytics.last_days', ['days' => $period]) }}
                </a>
            @endforeach
        </div>

        {{-- Summary cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-muted small mb-1">{{ trans('plugins/popup-ads::popup-ads.analytics.total_impressions') }}</div>
                        <div class="fs-3 fw-bold">{{ number_format($totalImpressions) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-muted small mb-1">{{ trans('plugins/popup-ads::popup-ads.analytics.total_clicks') }}</div>
                        <div class="fs-3 fw-bold">{{ number_format($totalClicks) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card text-center">
                    <div class="card-body">
                        <div class="text-muted small mb-1">{{ trans('plugins/popup-ads::popup-ads.analytics.ctr') }}</div>
                        <div class="fs-3 fw-bold">{{ $ctr }}%</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart --}}
        <div class="card">
            <div class="card-body">
                @if(empty($dateRange))
                    <p class="text-muted">{{ trans('plugins/popup-ads::popup-ads.analytics.no_data') }}</p>
                @else
                    <canvas id="popup-ads-chart" height="100"></canvas>
                @endif
            </div>
        </div>

    </div>
@endsection

@push('footer')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
(function () {
    var data = @json($dateRange);
    var labels      = data.map(function (r) { return r.date; });
    var impressions = data.map(function (r) { return r.impressions; });
    var clicks      = data.map(function (r) { return r.clicks; });

    var ctx = document.getElementById('popup-ads-chart');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '{{ trans("plugins/popup-ads::popup-ads.analytics.impressions") }}',
                    data: impressions,
                    borderColor: '#4263eb',
                    backgroundColor: 'rgba(66,99,235,.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                },
                {
                    label: '{{ trans("plugins/popup-ads::popup-ads.analytics.clicks") }}',
                    data: clicks,
                    borderColor: '#f03e3e',
                    backgroundColor: 'rgba(240,62,62,.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 3,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
            plugins: {
                legend: { position: 'top' },
            },
        },
    });
}());
</script>
@endpush
