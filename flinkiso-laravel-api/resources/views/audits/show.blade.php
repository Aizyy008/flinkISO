@extends('layout')
@section('title', $audit->reference)
@section('page_title', 'Audit Management')
@section('page_sub', $audit->reference)
@section('menu_audits', 'active')
@section('breadcrumb')<li><a href="/audits">Audits</a></li><li class="active">{{ $audit->reference }}</li>@endsection
@php
  $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username;
  $badge = ['scheduled'=>'label-default','in_progress'=>'label-warning','completed'=>'label-info','closed'=>'label-success'];
  $rBadge = ['conform'=>'label-success','nonconform'=>'label-danger','observation'=>'label-warning','na'=>'label-default'];
  $sections = $audit->checklistItems->groupBy('section');
@endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">{{ $audit->reference }} &middot; {{ $audit->title }}</h3>
    <div class="box-tools"><a class="btn btn-default btn-sm" href="/audits/{{ $audit->id }}/report"><i class="fa fa-file-pdf-o"></i> Audit report PDF</a></div>
  </div>
  <div class="box-body">
    <p>
      <span class="label {{ $badge[$audit->status] ?? 'label-default' }}">{{ str_replace('_',' ',$audit->status) }}</span>
      <span class="text-muted" style="margin-left:8px;">{{ ucfirst($audit->audit_type) }}@if($audit->standard) &middot; {{ $audit->standard }}@endif</span>
      @if($audit->result)<span class="label label-info" style="margin-left:8px;">Result: {{ str_replace('_',' ',$audit->result) }}</span>@endif
    </p>
    <form method="post" action="/audits/{{ $audit->id }}/status" class="form-inline" style="margin-top:8px;">
      @csrf
      <select class="form-control input-sm" name="status">@foreach(['scheduled','in_progress','completed','closed'] as $s)<option value="{{ $s }}" @selected($audit->status===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select>
      <select class="form-control input-sm" name="result"><option value="">Result…</option>@foreach(['conform'=>'Conform','minor_nc'=>'Minor NC','major_nc'=>'Major NC'] as $k=>$v)<option value="{{ $k }}" @selected($audit->result===$k)>{{ $v }}</option>@endforeach</select>
      <button class="btn btn-sm btn-primary">Update</button>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-6"><div class="box box-default">
    <div class="box-header with-border"><h3 class="box-title">Details</h3></div>
    <div class="box-body no-padding"><table class="table">
      <tr><th style="width:40%;">Program</th><td>{{ $audit->program?->reference ?? '—' }}</td></tr>
      <tr><th>Lead auditor</th><td>{{ $uName($audit->lead_auditor_id) ?? '—' }}</td></tr>
      <tr><th>Auditor</th><td>{{ $uName($audit->auditor_id) ?? '—' }}</td></tr>
      <tr><th>Planned / Actual</th><td>{{ $audit->planned_date?->format('d M Y') ?? '—' }} / {{ $audit->actual_date?->format('d M Y') ?? '—' }}</td></tr>
      <tr><th>Process / Site / Dept</th><td>{{ collect([$audit->related_process,$audit->related_site,$audit->related_department])->filter()->implode(' / ') ?: '—' }}</td></tr>
      <tr><th>Clause</th><td>{{ $audit->related_clause ?? '—' }}</td></tr>
    </table></div>
  </div></div>
  <div class="col-md-6"><div class="box box-default">
    <div class="box-header with-border"><h3 class="box-title">Add checklist item</h3></div>
    <form method="post" action="/audits/{{ $audit->id }}/checklist"><div class="box-body">
      @csrf
      <div class="row">
        <div class="col-sm-6 form-group"><input class="form-control" name="section" placeholder="Section (e.g. Leadership)"></div>
        <div class="col-sm-6 form-group"><input class="form-control" name="clause_ref" placeholder="Clause (e.g. 5.1)"></div>
      </div>
      <div class="form-group"><textarea class="form-control" name="question" rows="2" placeholder="Checklist question / requirement" required></textarea></div>
    </div><div class="box-footer"><button class="btn btn-default"><i class="fa fa-plus"></i> Add item</button></div></form>
  </div></div>
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Checklist ({{ $audit->checklistItems->count() }} items)</h3></div>
  <div class="box-body no-padding">
    @forelse($sections as $section => $items)
    <h4 style="padding:8px 12px;margin:0;background:#f5f5f5;">{{ $section }}</h4>
    <table class="table table-hover">
      <tbody>
      @foreach($items as $item)
      <tr>
        <td style="width:45%;">{{ $item->question }} @if($item->clause_ref)<small class="text-muted">[{{ $item->clause_ref }}]</small>@endif</td>
        <td style="width:15%;">@if($item->response)<span class="label {{ $rBadge[$item->response] ?? 'label-default' }}">{{ $item->response }}</span>@else<span class="text-muted">pending</span>@endif</td>
        <td>
          <form method="post" action="/audits/{{ $audit->id }}/checklist/{{ $item->id }}/response" class="form-inline">
            @csrf
            <select class="form-control input-sm" name="response">@foreach(['conform','nonconform','observation','na'] as $r)<option value="{{ $r }}" @selected($item->response===$r)>{{ $r }}</option>@endforeach</select>
            <input class="form-control input-sm" name="notes" value="{{ $item->notes }}" placeholder="notes" style="width:180px;">
            <button class="btn btn-xs btn-default">Save</button>
          </form>
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @empty
    <div class="box-body"><p class="text-muted">No checklist items yet. Build a multi-section checklist above.</p></div>
    @endforelse
  </div>
</div>

<div class="row">
  <div class="col-md-5"><div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title">Raise a finding</h3></div>
    <form method="post" action="/audits/{{ $audit->id }}/finding"><div class="box-body">
      @csrf
      <div class="row">
        <div class="col-sm-6 form-group"><label>Type</label><select class="form-control" name="finding_type">@foreach(['nonconformity'=>'Non-conformity','observation'=>'Observation','ofi'=>'Opportunity (OFI)'] as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <div class="col-sm-6 form-group"><label>Severity</label><select class="form-control" name="severity">@foreach(['minor','major','critical'] as $s)<option value="{{ $s }}">{{ ucfirst($s) }}</option>@endforeach</select></div>
      </div>
      <div class="form-group"><label>Clause</label><input class="form-control" name="clause_ref" placeholder="e.g. 8.5.1"></div>
      <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="2" required></textarea></div>
      <p class="help-block">A non-conformity automatically raises an Incident (NC) → which can spawn a CAPA.</p>
    </div><div class="box-footer"><button class="btn btn-warning"><i class="fa fa-flag"></i> Record finding</button></div></form>
  </div></div>
  <div class="col-md-7"><div class="box box-default">
    <div class="box-header with-border"><h3 class="box-title">Findings ({{ $audit->findings->count() }})</h3></div>
    <div class="box-body no-padding"><table class="table table-hover">
      <thead><tr><th>Ref</th><th>Type</th><th>Severity</th><th>Description</th><th>NC / CAPA</th></tr></thead>
      <tbody>
      @forelse($audit->findings as $f)
      <tr>
        <td>{{ $f->reference }}</td>
        <td>{{ str_replace('_',' ',$f->finding_type) }}</td>
        <td><span class="label {{ $f->severity==='critical'?'label-danger':($f->severity==='major'?'label-warning':'label-default') }}">{{ $f->severity }}</span></td>
        <td>{{ \Illuminate\Support\Str::limit($f->description, 60) }}</td>
        <td>@if($f->incident)<a href="/incidents/{{ $f->incident_id }}">{{ $f->incident->reference }}</a>@else<span class="text-muted">—</span>@endif</td>
      </tr>
      @empty
      <tr><td colspan="5" class="text-muted" style="padding:12px;">No findings recorded.</td></tr>
      @endforelse
      </tbody>
    </table></div>
  </div></div>
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Evidence</h3></div>
  <div class="box-body">
    <form method="post" action="/evidence" enctype="multipart/form-data" class="row">
      @csrf
      <input type="hidden" name="related_type" value="qms_audit">
      <input type="hidden" name="related_id" value="{{ $audit->id }}">
      <input type="hidden" name="redirect" value="/audits/{{ $audit->id }}">
      <div class="col-sm-3 form-group"><select class="form-control" name="evidence_type">@foreach(['file','photo','record','report'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
      <div class="col-sm-3 form-group"><input class="form-control" name="title" placeholder="Title"></div>
      <div class="col-sm-3 form-group"><input class="form-control" type="file" name="file"></div>
      <div class="col-sm-3 form-group"><button class="btn btn-default btn-block">Attach</button></div>
      <div class="col-sm-12 form-group"><input class="form-control" name="note" placeholder="…or a note instead of a file"></div>
    </form>
    @if($evidence->count())
    <table class="table table-hover"><thead><tr><th>Type</th><th>Title</th><th>When</th><th></th></tr></thead><tbody>
      @foreach($evidence as $e)<tr><td>{{ $e->evidence_type }}</td><td>{{ $e->title }}</td><td class="text-muted">{{ $e->created_at?->format('d M Y, g:i A') }}</td>
        <td>@if($e->file_path)<a class="btn btn-xs btn-default" href="/evidence/{{ $e->id }}/download">Download</a>@endif</td></tr>@endforeach
    </tbody></table>
    @endif
  </div>
</div>

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail</h3></div>
  <div class="box-body no-padding"><table class="table table-hover">
    <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>When</th></tr></thead>
    <tbody>@foreach($trail as $t)<tr><td>{{ $t->seq }}</td><td>{{ $t->action }}</td><td>{{ $t->username }}</td><td class="text-muted qms-sign">{{ $t->created_at?->format('d M Y, g:i A') }}</td></tr>@endforeach</tbody>
  </table></div>
</div>
@endsection
