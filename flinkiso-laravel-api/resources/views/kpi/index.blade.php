@extends('layout')
@section('title', 'KPIs')
@section('page_title', 'KPI Engine')
@section('page_sub', 'definitions & results')
@section('menu_kpi', 'active')
@section('breadcrumb')<li class="active">KPIs</li>@endsection
@php $areas = \App\Http\Controllers\Web\KpiController::AREAS; @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">KPIs</h3>
    <div class="box-tools">
      <a class="btn btn-default btn-sm" href="/kpi/dashboard"><i class="fa fa-dashboard"></i> Dashboard</a>
      <a class="btn btn-primary btn-sm" href="/kpi/create"><i class="fa fa-plus"></i> Define KPI</a>
    </div>
  </div>
  <div class="box-body">
    <form method="get" class="row">
      <div class="col-sm-3 form-group"><select class="form-control" name="area"><option value="">All areas</option>@foreach($areas as $k=>$v)<option value="{{ $k }}" @selected(request('area')===$k)>{{ $v }}</option>@endforeach</select></div>
      <div class="col-sm-2 form-group"><input class="form-control" name="standard" value="{{ request('standard') }}" placeholder="Standard"></div>
      <div class="col-sm-2 form-group"><input class="form-control" name="related_site" value="{{ request('related_site') }}" placeholder="Site"></div>
      <div class="col-sm-2 form-group"><input class="form-control" name="related_department" value="{{ request('related_department') }}" placeholder="Department"></div>
      <div class="col-sm-3 form-group"><button class="btn btn-primary"><i class="fa fa-filter"></i> Filter</button> <a class="btn btn-default" href="/kpi">Reset</a></div>
    </form>
  </div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Reference</th><th>Name</th><th>Area</th><th>Target</th><th>Latest</th><th>Status</th></tr></thead>
      <tbody>
      @forelse($kpis as $kpi)
      @php $lv = $kpi->latestResult?->value; $st = $kpi->statusFor($lv); $c=['on_target'=>'label-success','warning'=>'label-warning','critical'=>'label-danger'][$st]??'label-default'; @endphp
      <tr>
        <td><a href="/kpi/{{ $kpi->id }}"><b>{{ $kpi->reference }}</b></a></td>
        <td>{{ $kpi->name }}</td>
        <td>{{ $areas[$kpi->area] ?? $kpi->area }}</td>
        <td>{{ $kpi->target_value !== null ? rtrim(rtrim(number_format((float)$kpi->target_value,2),'0'),'.') : '—' }} {{ $kpi->unit }}</td>
        <td>{{ $lv !== null ? rtrim(rtrim(number_format((float)$lv,2),'0'),'.') : '—' }} @if($kpi->latestResult)<small class="text-muted">({{ $kpi->latestResult->period_label }})</small>@endif</td>
        <td><span class="label {{ $c }}" style="text-transform:capitalize;">{{ str_replace('_',' ',$st) }}</span></td>
      </tr>
      @empty
      <tr><td colspan="6" class="text-muted" style="padding:15px;">No KPIs yet. Click “Define KPI”.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($kpis->hasPages())<div class="box-footer">{{ $kpis->links() }}</div>@endif
</div>
@endsection
