@extends('layout')
@section('title', 'New CAPA')
@section('page_title', 'CAPA')
@section('page_sub', 'new corrective / preventive action')
@section('menu_capa', 'active')
@section('breadcrumb')<li><a href="/capa">CAPA</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-10">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">New CAPA</h3></div>
      <form method="post" action="/capa">
        @csrf
        @if($incident)<input type="hidden" name="incident_id" value="{{ $incident->id }}">@endif
        <div class="box-body">
          @if($incident)
          <div class="callout callout-info" style="padding:8px 12px;">Linked to incident <b>{{ $incident->reference }}</b> &mdash; {{ $incident->title }}</div>
          @endif
          <div class="form-group"><label>Title *</label><input class="form-control" name="title" value="{{ old('title', $incident?->title ? 'CAPA for '.$incident->title : '') }}"></div>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Type *</label>
              <select class="form-control" name="type"><option value="corrective">Corrective</option><option value="preventive">Preventive</option></select>
            </div>
            <div class="col-sm-4 form-group"><label>Priority *</label>
              <select class="form-control" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select>
            </div>
            <div class="col-sm-4 form-group"><label>Due date</label><input type="date" class="form-control" name="due_date" value="{{ old('due_date') }}"></div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group"><label>Root cause</label><textarea class="form-control" name="root_cause" rows="2">{{ old('root_cause', $incident?->root_cause) }}</textarea></div>
            <div class="col-sm-6 form-group"><label>Action plan</label><textarea class="form-control" name="action_plan" rows="2">{{ old('action_plan') }}</textarea></div>
          </div>
          <div class="form-group" style="max-width:400px;">
            <label>Assign owner</label>
            <select class="form-control" name="assigned_to"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach</select>
          </div>
        </div>
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Create CAPA</button>
          <a class="btn btn-default" href="{{ $incident ? '/incidents/'.$incident->id : '/capa' }}">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
