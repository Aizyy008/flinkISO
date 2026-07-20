@extends('layout')
@section('title', $kpi->name)
@section('page_title', 'KPI Engine')
@section('page_sub', $kpi->reference)
@section('menu_kpi', 'active')
@section('breadcrumb')<li><a href="/kpi">KPIs</a></li><li class="active">{{ $kpi->reference }}</li>@endsection
@php
  $results = $kpi->results;
  $latest = $results->last();
  $areas = \App\Http\Controllers\Web\KpiController::AREAS;
  // Build SVG line chart geometry.
  $vals = $results->pluck('value')->map(fn($v)=>(float)$v);
  $target = $kpi->target_value !== null ? (float)$kpi->target_value : null;
  $all = $vals->push($target)->filter(fn($v)=>$v!==null);
  $min = $all->min() ?? 0; $max = $all->max() ?? 1; if ($max == $min) { $max = $min + 1; }
  $W = 640; $H = 200; $pad = 30;
  $n = max($results->count(), 1);
  $x = fn($i) => $pad + ($n <= 1 ? ($W-2*$pad)/2 : $i * ($W-2*$pad)/($n-1));
  $y = fn($v) => $H - $pad - (($v - $min) / ($max - $min)) * ($H - 2*$pad);
@endphp
@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">{{ $kpi->reference }} &middot; {{ $kpi->name }}</h3>
        <div class="box-tools"><a class="btn btn-default btn-sm" href="/kpi/dashboard"><i class="fa fa-dashboard"></i> Dashboard</a></div>
      </div>
      <div class="box-body">
        @if($results->count())
        <svg viewBox="0 0 {{ $W }} {{ $H }}" style="width:100%;border:1px solid #f0f0f0;border-radius:4px;">
          {{-- target line --}}
          @if($target !== null)
          <line x1="{{ $pad }}" y1="{{ $y($target) }}" x2="{{ $W-$pad }}" y2="{{ $y($target) }}" stroke="#00a65a" stroke-dasharray="4 3" stroke-width="1"/>
          <text x="{{ $W-$pad }}" y="{{ $y($target)-4 }}" text-anchor="end" style="font-size:10px;fill:#00a65a;">target {{ rtrim(rtrim(number_format($target,2),'0'),'.') }}</text>
          @endif
          {{-- value polyline --}}
          <polyline fill="none" stroke="#3c8dbc" stroke-width="2"
            points="@foreach($results as $i=>$r){{ $x($i) }},{{ $y((float)$r->value) }} @endforeach"/>
          @foreach($results as $i=>$r)
            @php $st = $kpi->statusFor($r->value); $c = ['on_target'=>'#00a65a','warning'=>'#f39c12','critical'=>'#dd4b39'][$st] ?? '#3c8dbc'; @endphp
            <circle cx="{{ $x($i) }}" cy="{{ $y((float)$r->value) }}" r="4" fill="{{ $c }}"><title>{{ $r->period_label }}: {{ $r->value }}</title></circle>
            <text x="{{ $x($i) }}" y="{{ $H-8 }}" text-anchor="middle" style="font-size:9px;fill:#999;">{{ \Illuminate\Support\Str::limit($r->period_label, 8, '') }}</text>
          @endforeach
        </svg>
        @else
        <p class="text-muted">No results recorded yet. Add one on the right to build the trend.</p>
        @endif

        <table class="table table-hover" style="margin-top:12px;">
          <thead><tr><th>Period</th><th>Value</th><th>Status</th><th>Notes</th><th>Recorded</th></tr></thead>
          <tbody>
          @foreach($results->reverse() as $r)
          @php $st=$kpi->statusFor($r->value); $c=['on_target'=>'label-success','warning'=>'label-warning','critical'=>'label-danger'][$st]??'label-default'; @endphp
          <tr><td>{{ $r->period_label }}</td><td><b>{{ rtrim(rtrim(number_format((float)$r->value,4),'0'),'.') }}</b> {{ $kpi->unit }}</td>
            <td><span class="label {{ $c }}" style="text-transform:capitalize;">{{ str_replace('_',' ',$st) }}</span></td>
            <td class="text-muted">{{ $r->notes }}</td><td class="text-muted qms-sign">{{ $r->created_at?->format('d M Y') }}</td></tr>
          @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Current status</h3></div>
      <div class="box-body">@include('kpi._gauge', ['kpi' => $kpi, 'value' => $latest?->value])</div>
    </div>
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Definition</h3></div>
      <div class="box-body no-padding"><table class="table">
        <tr><th style="width:45%;">Area / Standard</th><td>{{ $areas[$kpi->area] ?? $kpi->area }}@if($kpi->standard) / {{ $kpi->standard }}@endif</td></tr>
        <tr><th>Target</th><td>{{ $kpi->target_value !== null ? rtrim(rtrim(number_format((float)$kpi->target_value,4),'0'),'.') : '—' }} {{ $kpi->unit }}</td></tr>
        <tr><th>Warning / Critical</th><td>{{ $kpi->warning_threshold ?? '—' }} / {{ $kpi->critical_threshold ?? '—' }}</td></tr>
        <tr><th>Direction</th><td>{{ str_replace('_',' ',$kpi->direction) }}</td></tr>
        <tr><th>Aggregation</th><td>{{ ucfirst($kpi->aggregation) }}</td></tr>
        <tr><th>Calculation</th><td>{{ $kpi->calculation_method ?: '—' }}</td></tr>
        <tr><th>Process / Site / Dept</th><td>{{ collect([$kpi->related_process,$kpi->related_site,$kpi->related_department])->filter()->implode(' / ') ?: '—' }}</td></tr>
      </table></div>
    </div>
    @if(config('zaikpi.enabled'))
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">ZaiKPI sync</h3></div>
      <div class="box-body">
        @php $zs = $kpi->zaikpi_status; @endphp
        <p>Status:
          @if($zs === 'synced')<span class="label label-success">Synced</span>
          @elseif($zs === 'failed')<span class="label label-danger">Failed</span>
          @else<span class="label label-default">Not synced</span>@endif
        </p>
        @if($kpi->zaikpi_synced_at)<p class="text-muted qms-sign">Last synced {{ $kpi->zaikpi_synced_at->format('d M Y H:i') }}</p>@endif
        @if($zs === 'failed' && $kpi->zaikpi_error)<p class="text-danger" style="font-size:12px;">{{ $kpi->zaikpi_error }}</p>@endif
        <form method="post" action="/kpi/{{ $kpi->id }}/sync">
          @csrf
          <button class="btn btn-default btn-sm"><i class="fa fa-cloud-upload"></i> Sync to ZaiKPI now</button>
        </form>
      </div>
    </div>
    @endif
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Record a result</h3></div>
      <form method="post" action="/kpi/{{ $kpi->id }}/result">
        @csrf
        <div class="box-body">
          <div class="form-group"><label>Period label *</label><input class="form-control" name="period_label" placeholder="e.g. 2026-01 or Q1 2026" required></div>
          <div class="form-group"><label>Period date *</label><input type="date" class="form-control" name="period_date" value="{{ now()->toDateString() }}" required></div>
          <div class="form-group"><label>Value *</label><input type="number" step="any" class="form-control" name="value" required></div>
          <div class="form-group"><label>Notes</label><input class="form-control" name="notes"></div>
        </div>
        <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-plus"></i> Record result</button></div>
      </form>
    </div>
  </div>
</div>
@endsection
