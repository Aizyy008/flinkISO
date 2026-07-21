@extends('layout')
@section('title', $incident->reference)
@section('page_title', 'Incidents')
@section('page_sub', $incident->reference)
@section('menu_incidents', 'active')
@section('breadcrumb')<li><a href="/incidents">Incidents</a></li><li class="active">{{ $incident->reference }}</li>@endsection
@php $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username; @endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">{{ $incident->reference }} &middot; {{ $incident->title }}</h3>
  </div>
  <div class="box-body">
    <p>
      @include('qms._label', ['value' => $incident->type])
      @include('qms._label', ['value' => $incident->severity])
      @include('qms._label', ['value' => $incident->status])
      @if($incident->assigned_to)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-user"></i> {{ $uName($incident->assigned_to) }}</span>@endif
      @if($incident->due_date)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-calendar"></i> due {{ $incident->due_date->format('d M Y') }}</span>@endif
    </p>
    @if($incident->description)<p>{{ $incident->description }}</p>@endif

    <form method="post" action="/incidents/{{ $incident->id }}/status" class="form-inline" style="margin-top:8px;">
      @csrf
      <select name="status" class="form-control input-sm">
        @foreach(\App\Models\Qms\Incident::STATUSES as $s)<option value="{{ $s }}" @selected($incident->status===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
      </select>
      <input name="reason" class="form-control input-sm" placeholder="reason (optional)" style="width:220px;">
      <button class="btn btn-primary btn-sm">Update status</button>
      <button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#editBox"><i class="fa fa-pencil"></i> Edit / assign</button>
    </form>

    <div class="collapse" id="editBox" style="margin-top:12px;">
      <form method="post" action="/incidents/{{ $incident->id }}/update" class="well" style="margin-bottom:0;">
        @csrf
        <div class="row">
          <div class="col-sm-3 form-group"><label>Severity</label>
            <select class="form-control" name="severity">@foreach(\App\Models\Qms\Incident::SEVERITIES as $s)<option value="{{ $s }}" @selected($incident->severity===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
          </div>
          <div class="col-sm-3 form-group"><label>Source</label><input class="form-control" name="source" value="{{ $incident->source }}"></div>
          <div class="col-sm-3 form-group"><label>Assign to</label>
            <select class="form-control" name="assigned_to"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($incident->assigned_to===$u->id)>{{ $u->name ?: $u->username }}</option>@endforeach</select>
          </div>
          <div class="col-sm-3 form-group"><label>Due date</label><input type="date" class="form-control" name="due_date" value="{{ $incident->due_date?->toDateString() }}"></div>
        </div>
        <div class="row">
          <div class="col-sm-6 form-group"><label>Root cause</label><textarea class="form-control" name="root_cause" rows="2">{{ $incident->root_cause }}</textarea></div>
          <div class="col-sm-6 form-group"><label>Containment action</label><textarea class="form-control" name="containment_action" rows="2">{{ $incident->containment_action }}</textarea></div>
        </div>
        <button class="btn btn-primary btn-sm">Save</button>
      </form>
    </div>
  </div>
</div>

@if($incident->iso_standard)
@php $ov = config("iso_overlays.{$incident->iso_standard}"); $vals = $incident->iso_overlay ?? []; @endphp
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">ISO overlay — {{ $ov['label'] ?? $incident->iso_standard }}</h3></div>
  <div class="box-body no-padding"><table class="table">
    @foreach(($ov['fields'] ?? []) as $key => $f)
    <tr><th style="width:35%;">{{ $f['label'] }}</th><td>{{ $vals[$key] ?? '—' }}</td></tr>
    @endforeach
  </table></div>
</div>
@endif

<div class="box box-default">
  <div class="box-header with-border">
    <h3 class="box-title">Corrective / Preventive Actions (CAPA)</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/capa/create?incident_id={{ $incident->id }}"><i class="fa fa-plus"></i> Raise CAPA</a></div>
  </div>
  <div class="box-body no-padding">
    @if($incident->capas->count())
    <table class="table table-hover">
      <thead><tr><th>Ref</th><th>Title</th><th>Type</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @foreach($incident->capas as $c)
      <tr>
        <td>{{ $c->reference }}</td><td>{{ $c->title }}</td>
        <td>@include('qms._label', ['value' => $c->type])</td>
        <td>@include('qms._label', ['value' => $c->status])</td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="/capa/{{ $c->id }}">Open</a></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="box-body"><p class="text-muted">No CAPA raised for this incident yet.</p></div>
    @endif
  </div>
</div>

@include('qms._evidence', ['relatedType' => 'qms_incident', 'relatedId' => $incident->id, 'evidence' => $evidence, 'redirect' => '/incidents/'.$incident->id])

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail</h3></div>
  <div class="box-body no-padding">
    <table class="table">
      <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>Reason</th><th>When</th></tr></thead>
      <tbody>
      @foreach($audit as $a)
      <tr><td>{{ $a->seq }}</td><td>{{ str_replace('_',' ',$a->action) }}</td><td>{{ $a->username }}</td><td class="text-muted">{{ $a->reason }}</td><td class="text-muted small">{{ $a->created_at?->format('d M Y, g:i A') }}</td></tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
