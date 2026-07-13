@extends('layout')
@section('title', 'New asset')
@section('page_title', 'Assets &amp; Calibration')
@section('page_sub', 'new asset')
@section('menu_calibration', 'active')
@section('breadcrumb')<li><a href="/assets">Assets</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row"><div class="col-md-9">
  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">New asset / equipment</h3></div>
    <form method="post" action="/assets">
      @csrf
      <div class="box-body">
        <div class="row">
          <div class="col-sm-6 form-group"><label>Name *</label><input class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. Pasteuriser temperature probe"></div>
          <div class="col-sm-3 form-group"><label>Type</label><input class="form-control" name="asset_type" value="{{ old('asset_type') }}" placeholder="Instrument / Equipment"></div>
          <div class="col-sm-3 form-group"><label>Serial no.</label><input class="form-control" name="serial_no" value="{{ old('serial_no') }}"></div>
        </div>
        <div class="row">
          <div class="col-sm-4 form-group"><label>Location</label><input class="form-control" name="location" value="{{ old('location') }}"></div>
          <div class="col-sm-3 form-group"><label>Calibration cycle (months)</label><input type="number" min="1" class="form-control" name="calibration_frequency_months" value="{{ old('calibration_frequency_months') }}" placeholder="e.g. 12"></div>
          <div class="col-sm-3 form-group"><label>First due date</label><input type="date" class="form-control" name="next_due_date" value="{{ old('next_due_date') }}"></div>
          <div class="col-sm-2 form-group"><label>&nbsp;</label><div class="checkbox"><label><input type="checkbox" name="requires_calibration" value="1" checked> Requires calibration</label></div></div>
        </div>
      </div>
      <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-check"></i> Add asset</button> <a class="btn btn-default" href="/assets">Cancel</a></div>
    </form>
  </div>
</div></div>
@endsection
