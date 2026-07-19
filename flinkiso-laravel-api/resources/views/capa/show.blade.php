@extends('layout')
@section('title', $capa->reference)
@section('page_title', 'CAPA')
@section('page_sub', $capa->reference)
@section('menu_capa', 'active')
@section('breadcrumb')<li><a href="/capa">CAPA</a></li><li class="active">{{ $capa->reference }}</li>@endsection
@php $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username; @endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">{{ $capa->reference }} &middot; {{ $capa->title }}</h3></div>
  <div class="box-body">
    <p>
      @include('qms._label', ['value' => $capa->type])
      @include('qms._label', ['value' => $capa->status])
      @include('qms._label', ['value' => $capa->priority])
      @if($capa->incident)<span style="margin-left:10px;">From incident <a href="/incidents/{{ $capa->incident->id }}">{{ $capa->incident->reference }}</a></span>@endif
      @if($capa->assigned_to)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-user"></i> {{ $uName($capa->assigned_to) }}</span>@endif
      @if($capa->due_date)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-calendar"></i> due {{ $capa->due_date->format('d M Y') }}</span>@endif
    </p>
    @if($capa->action_plan)<p><b>Action plan:</b> {{ $capa->action_plan }}</p>@endif
    @if($capa->effectiveness_verified)<p class="text-success"><i class="fa fa-check-circle"></i> Effectiveness verified. {{ $capa->effectiveness_notes }}</p>@endif

    <form method="post" action="/capa/{{ $capa->id }}/status" class="form-inline" style="margin-top:8px;">
      @csrf
      <select name="status" class="form-control input-sm">
        @foreach(['open','in_progress','effectiveness_check','closed','cancelled'] as $s)<option value="{{ $s }}" @selected($capa->status===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
      </select>
      <input name="reason" class="form-control input-sm" placeholder="reason (optional)" style="width:200px;">
      <button class="btn btn-primary btn-sm">Update status</button>
      <button type="button" class="btn btn-default btn-sm" data-toggle="collapse" data-target="#editBox"><i class="fa fa-pencil"></i> Edit / assign</button>
    </form>
    @if(!$capa->effectiveness_verified)<p class="help-block" style="margin-top:6px;">Closing is blocked until the effectiveness check is confirmed below.</p>@endif

    <div class="collapse" id="editBox" style="margin-top:12px;">
      <form method="post" action="/capa/{{ $capa->id }}/update" class="well" style="margin-bottom:0;">
        @csrf
        <div class="row">
          <div class="col-sm-3 form-group"><label>Priority</label>
            <select class="form-control" name="priority">@foreach(['low','medium','high'] as $p)<option value="{{ $p }}" @selected($capa->priority===$p)>{{ ucfirst($p) }}</option>@endforeach</select>
          </div>
          <div class="col-sm-4 form-group"><label>Owner</label>
            <select class="form-control" name="assigned_to"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($capa->assigned_to===$u->id)>{{ $u->name ?: $u->username }}</option>@endforeach</select>
          </div>
          <div class="col-sm-3 form-group"><label>Due date</label><input type="date" class="form-control" name="due_date" value="{{ $capa->due_date?->toDateString() }}"></div>
        </div>
        <div class="row">
          <div class="col-sm-6 form-group"><label>Root cause</label><textarea class="form-control" name="root_cause" rows="2">{{ $capa->root_cause }}</textarea></div>
          <div class="col-sm-6 form-group"><label>Action plan</label><textarea class="form-control" name="action_plan" rows="2">{{ $capa->action_plan }}</textarea></div>
        </div>
        <button class="btn btn-primary btn-sm">Save</button>
      </form>
    </div>
  </div>
</div>

<div class="box box-warning">
  <div class="box-header with-border"><h3 class="box-title">Effectiveness check</h3></div>
  <form method="post" action="/capa/{{ $capa->id }}/verify">
    @csrf
    <div class="box-body">
      <div class="form-group"><label>Effectiveness notes *</label><textarea class="form-control" name="effectiveness_notes" rows="2" placeholder="How was effectiveness confirmed?">{{ $capa->effectiveness_notes }}</textarea></div>
      <div class="row">
        <div class="col-sm-4 form-group"><label>Result</label><select class="form-control" name="verified"><option value="1">Effective (verify)</option><option value="0">Not effective</option></select></div>
        <div class="col-sm-6 form-group"><label>Reason (for the signature)</label><input class="form-control" name="reason" placeholder="e.g. re-checked after 30 days"></div>
      </div>
    </div>
    <div class="box-footer"><button class="btn btn-warning"><i class="fa fa-pencil-square-o"></i> Record effectiveness check (e-sign)</button></div>
  </form>
</div>

@include('qms._evidence', ['relatedType' => 'qms_capa', 'relatedId' => $capa->id, 'evidence' => $evidence, 'redirect' => '/capa/'.$capa->id])

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail</h3></div>
  <div class="box-body no-padding">
    <table class="table">
      <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>Signature</th><th>Reason</th><th>When</th></tr></thead>
      <tbody>
      @foreach($audit as $a)
      <tr><td>{{ $a->seq }}</td><td>{{ str_replace('_',' ',$a->action) }}</td><td>{{ $a->username }}</td>
        <td>@if($a->signature_meaning)<span class="label label-info" style="text-transform:capitalize;">{{ $a->signature_meaning }}</span>@endif</td>
        <td class="text-muted">{{ $a->reason }}</td><td class="text-muted small">{{ $a->created_at?->format('d M Y, g:i A') }}</td></tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
