@extends('layout')
@section('title', 'KPI Dashboard')
@section('page_title', 'KPI Dashboard')
@section('page_sub', 'live status from recorded results')
@section('menu_kpi', 'active')
@section('breadcrumb')<li class="active">KPI Dashboard</li>@endsection
@php $areas = \App\Http\Controllers\Web\KpiController::AREAS; @endphp
@section('content')

<div class="row">
  @foreach(['on_target'=>['On target','bg-green'],'warning'=>['Warning','bg-yellow'],'critical'=>['Critical','bg-red'],'no_data'=>['No data','bg-gray']] as $k=>$v)
  <div class="col-sm-3 col-xs-6">
    <div class="small-box {{ $v[1] }}"><div class="inner"><h3>{{ $summary[$k] }}</h3><p>{{ $v[0] }}</p></div>
      <div class="icon"><i class="fa fa-dashboard"></i></div></div>
  </div>
  @endforeach
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Filters</h3>
    <div class="box-tools"><a class="btn btn-default btn-sm" href="/kpi/report{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"><i class="fa fa-file-pdf-o"></i> Report PDF</a>
      <a class="btn btn-primary btn-sm" href="/kpi"><i class="fa fa-list"></i> Manage KPIs</a></div>
  </div>
  <div class="box-body">
    <form method="get" class="row">
      <div class="col-sm-3 form-group"><label>Area</label><select class="form-control" name="area"><option value="">All</option>@foreach($areas as $k=>$v)<option value="{{ $k }}" @selected(request('area')===$k)>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-3 form-group"><label>Standard</label><input class="form-control" name="standard" value="{{ request('standard') }}" placeholder="ISO 9001"></div>
      <div class="col-sm-2 form-group"><label>Site</label><input class="form-control" name="related_site" value="{{ request('related_site') }}"></div>
      <div class="col-sm-2 form-group"><label>Department</label><input class="form-control" name="related_department" value="{{ request('related_department') }}"></div>
      <div class="col-sm-2 form-group"><label>Process</label><input class="form-control" name="related_process" value="{{ request('related_process') }}"></div>
      <div class="col-sm-12"><button class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Apply</button> <a class="btn btn-default btn-sm" href="/kpi/dashboard">Reset</a></div>
    </form>
  </div>
</div>

<div class="row">
  @forelse($kpis as $kpi)
  <div class="col-md-3 col-sm-6">
    <div class="box box-default">
      <div class="box-header with-border" style="padding:8px 10px;">
        <h3 class="box-title" style="font-size:14px;"><a href="/kpi/{{ $kpi->id }}">{{ $kpi->name }}</a></h3>
      </div>
      <div class="box-body" style="padding:8px;">
        @include('kpi._gauge', ['kpi' => $kpi, 'value' => $kpi->latestResult?->value])
        <p class="text-muted qms-sign" style="text-align:center;margin:6px 0 0;">
          {{ $areas[$kpi->area] ?? $kpi->area }}@if($kpi->latestResult) &middot; {{ $kpi->latestResult->period_label }}@else &middot; no results yet @endif
        </p>
      </div>
    </div>
  </div>
  @empty
  <div class="col-md-12"><div class="box"><div class="box-body text-muted">No active KPIs match. <a href="/kpi/create">Define a KPI</a>.</div></div></div>
  @endforelse
</div>
@endsection
