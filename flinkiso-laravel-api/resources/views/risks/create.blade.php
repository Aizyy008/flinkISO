@extends('layout')
@section('title', 'New risk')
@section('page_title', 'Risk Register')
@section('page_sub', 'new risk')
@section('menu_risks', 'active')
@section('breadcrumb')<li><a href="/risks">Risks</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">New risk</h3></div>
      <form method="post" action="/risks">
        @csrf
        <div class="box-body">
          <div class="form-group"><label>Title *</label><input class="form-control" name="title" value="{{ old('title') }}"></div>
          <div class="row">
            <div class="col-sm-3 form-group"><label>Standard</label>
              <select class="form-control" name="standard"><option value="">(none)</option>@foreach($standards as $s)<option value="{{ $s->name }}" @selected(old('standard')===$s->name)>{{ $s->name }}</option>@endforeach</select>
            </div>
            <div class="col-sm-3 form-group"><label>Context</label><input class="form-control" name="context" value="{{ old('context') }}" placeholder="process / product / ccp ..."></div>
            <div class="col-sm-3 form-group"><label>Hazard type</label><input class="form-control" name="hazard_type" value="{{ old('hazard_type') }}"></div>
            <div class="col-sm-3 form-group"><label>Owner</label>
              <select class="form-control" name="owner_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach</select>
            </div>
          </div>
          <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea></div>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Likelihood (1&ndash;5) *</label><input type="number" min="1" max="5" class="form-control" name="likelihood" value="{{ old('likelihood', 3) }}"></div>
            <div class="col-sm-4 form-group"><label>Severity (1&ndash;5) *</label><input type="number" min="1" max="5" class="form-control" name="severity" value="{{ old('severity', 3) }}"></div>
            <div class="col-sm-4 form-group"><label>Detection (1&ndash;5) *</label><input type="number" min="1" max="5" class="form-control" name="detection" value="{{ old('detection', 3) }}"></div>
          </div>
          <p class="help-block">Risk score = likelihood &times; severity &times; detection. Calculated automatically.</p>
          <div class="form-group"><label>Treatment plan</label><textarea class="form-control" name="treatment_plan" rows="2">{{ old('treatment_plan') }}</textarea></div>
        </div>
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Add risk</button>
          <a class="btn btn-default" href="/risks">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
