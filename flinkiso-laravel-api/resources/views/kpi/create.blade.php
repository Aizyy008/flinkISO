@extends('layout')
@section('title', 'Define KPI')
@section('page_title', 'KPI Engine')
@section('page_sub', 'define a KPI')
@section('menu_kpi', 'active')
@section('breadcrumb')<li><a href="/kpi">KPIs</a></li><li class="active">Define</li>@endsection
@php $areas = \App\Http\Controllers\Web\KpiController::AREAS; @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">Define a KPI</h3></div>
  <form method="post" action="/kpi">
    @csrf
    <div class="box-body">
      <div class="row">
        <div class="col-sm-6 form-group"><label>Name *</label><input class="form-control" name="name" value="{{ old('name') }}" required placeholder="e.g. On-Time Delivery"></div>
        <div class="col-sm-3 form-group"><label>Area *</label><select class="form-control" name="area">@foreach($areas as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <div class="col-sm-3 form-group"><label>Standard</label><input class="form-control" name="standard" placeholder="ISO 9001"></div>
      </div>
      <div class="row">
        <div class="col-sm-3 form-group"><label>Unit</label><input class="form-control" name="unit" placeholder="%, hrs, ppm"></div>
        <div class="col-sm-3 form-group"><label>Target</label><input type="number" step="any" class="form-control" name="target_value"></div>
        <div class="col-sm-3 form-group"><label>Direction *</label><select class="form-control" name="direction"><option value="higher_better">Higher is better</option><option value="lower_better">Lower is better</option></select></div>
        <div class="col-sm-3 form-group"><label>Aggregation *</label><select class="form-control" name="aggregation"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="yearly">Yearly</option></select></div>
      </div>
      <div class="row">
        <div class="col-sm-3 form-group"><label>Warning threshold</label><input type="number" step="any" class="form-control" name="warning_threshold"></div>
        <div class="col-sm-3 form-group"><label>Critical threshold</label><input type="number" step="any" class="form-control" name="critical_threshold"></div>
        <div class="col-sm-6 form-group"><label>Calculation method</label><input class="form-control" name="calculation_method" placeholder="e.g. on-time deliveries / total * 100"></div>
      </div>
      <div class="row">
        <div class="col-sm-3 form-group"><label>Frequency</label>
          <select class="form-control" name="frequency">
            <option value="">(not set)</option>
            <option value="Daily">Daily</option><option value="Weekly">Weekly</option>
            <option value="Monthly">Monthly</option><option value="Quarterly">Quarterly</option>
            <option value="Annually">Annually</option>
          </select></div>
        <div class="col-sm-9 form-group"><label>Data source</label><input class="form-control" name="data_source" placeholder="e.g. ERP export, LIMS, manual entry"></div>
      </div>
      <div class="row">
        <div class="col-sm-4 form-group"><label>Related process</label><input class="form-control" name="related_process"></div>
        <div class="col-sm-4 form-group"><label>Related site</label><input class="form-control" name="related_site"></div>
        <div class="col-sm-4 form-group"><label>Related department</label><input class="form-control" name="related_department"></div>
      </div>
      <div class="row">
        <div class="col-sm-6 form-group"><label>Owner</label><select class="form-control" name="owner_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach</select></div>
        <div class="col-sm-6 form-group"><label>Description</label><input class="form-control" name="description"></div>
      </div>
    </div>
    <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-check"></i> Create KPI</button> <a class="btn btn-default" href="/kpi">Cancel</a></div>
  </form>
</div>
@endsection
