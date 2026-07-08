@extends('layout')
@section('title', $document->doc_number)
@section('page_title', 'Document Control')
@section('page_sub', $document->doc_number)
@section('menu_documents', 'active')
@section('breadcrumb')<li><a href="/documents">Documents</a></li><li class="active">{{ $document->doc_number }}</li>@endsection
@php
  $allowed = \App\Models\Qms\Document::TRANSITIONS[$document->status] ?? [];
  $editable = !in_array($document->status, ['released','obsolete'], true);
  $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username;
  $stdName = optional($standards->firstWhere('id', $document->related_standard_id))->name;
@endphp
@section('content')

<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">{{ $document->doc_number }} &middot; {{ $document->title }}</h3>
        <div class="box-tools">
          <a class="btn btn-default btn-sm" href="/documents/{{ $document->id }}/pdf"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
        </div>
      </div>
      <div class="box-body">
        <p>
          @include('documents.status', ['status' => $document->status])
          <span class="text-muted" style="margin-left:10px;">v{{ $document->current_version }} &middot; {{ $document->iso_ref }}</span>
          <span class="text-muted" style="margin-left:10px;">{{ $document->category }}@if($document->document_type) / {{ $document->document_type }}@endif</span>
        </p>
        <div style="margin-top:8px;">
          @foreach($allowed as $to)
            <form method="post" action="/documents/{{ $document->id }}/transition" style="display:inline;">
              @csrf<input type="hidden" name="to" value="{{ $to }}"><input type="hidden" name="reason" value="{{ ucfirst($to) }} via UI">
              <button class="btn btn-sm {{ in_array($to,['approved','released'])?'btn-success':'btn-primary' }}">
                @switch($to)
                  @case('review') <i class="fa fa-share"></i> Submit for review @break
                  @case('approved') <i class="fa fa-check"></i> Approve (e-sign) @break
                  @case('released') <i class="fa fa-unlock-alt"></i> Release (e-sign) @break
                  @case('obsolete') <i class="fa fa-ban"></i> Mark obsolete @break
                  @default {{ ucfirst($to) }}
                @endswitch
              </button>
            </form>
          @endforeach
          @if($editable)
            <button class="btn btn-sm btn-default" data-toggle="collapse" data-target="#editBox"><i class="fa fa-pencil"></i> Edit details</button>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Document details</h3></div>
      <div class="box-body no-padding">
        <table class="table">
          <tr><th style="width:45%;">Issue / Revision</th><td>{{ $document->iso_ref }}</td></tr>
          <tr><th>Effective date</th><td>{{ $document->effective_date?->toDateString() ?? 'N/A' }}</td></tr>
          <tr><th>Review due date</th><td>{{ $document->review_due_date?->toDateString() ?? 'N/A' }}</td></tr>
          <tr><th>Related standard</th><td>{{ $stdName ?? 'N/A' }}</td></tr>
          <tr><th>Related clause</th><td>{{ $document->related_clause_id ?? 'N/A' }}</td></tr>
          <tr><th>Reviewer</th><td>{{ $uName($document->reviewer_id) ?? 'N/A' }}</td></tr>
          <tr><th>Approver</th><td>{{ $uName($document->approver_id) ?? 'N/A' }}</td></tr>
          <tr><th>Process / Site / Dept</th><td>{{ collect([$document->related_process,$document->related_site,$document->related_department])->filter()->implode(' / ') ?: 'N/A' }}</td></tr>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">New version</h3></div>
      <form method="post" action="/documents/{{ $document->id }}/version">
        @csrf
        <div class="box-body">
          <textarea class="form-control" name="change_summary" rows="2" placeholder="What changed in this version"></textarea>
          <p class="help-block">Bumps to v{{ $document->current_version + 1 }} (Rev {{ ($document->revision_number ?? 0) + 1 }}) and returns to draft.</p>
        </div>
        <div class="box-footer"><button class="btn btn-default"><i class="fa fa-code-fork"></i> Create new version</button></div>
      </form>
    </div>
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Change request</h3></div>
      <form method="post" action="/documents/{{ $document->id }}/change-request">
        @csrf
        <div class="box-body"><textarea class="form-control" name="reason" rows="2" placeholder="Why is a change needed"></textarea></div>
        <div class="box-footer"><button class="btn btn-default"><i class="fa fa-refresh"></i> Raise change request</button></div>
      </form>
    </div>
  </div>
</div>

@if($editable)
<div class="collapse" id="editBox">
  <div class="box box-warning">
    <div class="box-header with-border"><h3 class="box-title">Edit document details</h3></div>
    <form method="post" action="/documents/{{ $document->id }}/edit">
      @csrf
      <div class="box-body">
        <div class="row">
          <div class="col-sm-6 form-group"><label>Title</label><input class="form-control" name="title" value="{{ $document->title }}"></div>
          <div class="col-sm-3 form-group"><label>Category</label>
            <select class="form-control" name="category">@foreach(['SOP','WI','Form','Policy','HACCP record'] as $c)<option @selected($document->category===$c)>{{ $c }}</option>@endforeach</select>
          </div>
          <div class="col-sm-3 form-group"><label>Document type</label><input class="form-control" name="document_type" value="{{ $document->document_type }}"></div>
        </div>
        <div class="row">
          <div class="col-sm-3 form-group"><label>Issue no.</label><input class="form-control" type="number" min="1" name="issue_number" value="{{ $document->issue_number }}"></div>
          <div class="col-sm-3 form-group"><label>Review due date</label><input class="form-control" type="date" name="review_due_date" value="{{ $document->review_due_date?->toDateString() }}"></div>
          <div class="col-sm-3 form-group"><label>Related standard</label>
            <select class="form-control" name="related_standard_id"><option value="">(none)</option>@foreach($standards as $s)<option value="{{ $s->id }}" @selected($document->related_standard_id===$s->id)>{{ $s->name }}</option>@endforeach</select>
          </div>
          <div class="col-sm-3 form-group"><label>Related clause</label><input class="form-control" name="related_clause_id" value="{{ $document->related_clause_id }}"></div>
        </div>
        <div class="row">
          <div class="col-sm-4 form-group"><label>Reviewer</label>
            <select class="form-control" name="reviewer_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($document->reviewer_id===$u->id)>{{ $u->name ?: $u->username }}</option>@endforeach</select>
          </div>
          <div class="col-sm-4 form-group"><label>Approver</label>
            <select class="form-control" name="approver_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}" @selected($document->approver_id===$u->id)>{{ $u->name ?: $u->username }}</option>@endforeach</select>
          </div>
        </div>
        <div class="row">
          <div class="col-sm-4 form-group"><label>Related process</label><input class="form-control" name="related_process" value="{{ $document->related_process }}"></div>
          <div class="col-sm-4 form-group"><label>Related site</label><input class="form-control" name="related_site" value="{{ $document->related_site }}"></div>
          <div class="col-sm-4 form-group"><label>Related department</label><input class="form-control" name="related_department" value="{{ $document->related_department }}"></div>
        </div>
      </div>
      <div class="box-footer"><button class="btn btn-primary">Save changes</button></div>
    </form>
  </div>
</div>
@endif

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Version history</h3></div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Version</th><th>Status</th><th>Change summary</th><th>Created</th></tr></thead>
      <tbody>
      @foreach($document->versions as $v)
      <tr>
        <td>v{{ $v->version }} @if($v->version == $document->current_version)<small class="text-muted">(current)</small>@endif</td>
        <td>@include('documents.status', ['status' => $v->status])</td>
        <td>{{ $v->change_summary }}</td>
        <td class="text-muted">{{ $v->created_at?->format('d M Y, g:i A') }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

@if($document->changeRequests->count())
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Change requests</h3></div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Reference</th><th>Reason</th><th>Status</th><th class="text-right">Action</th></tr></thead>
      <tbody>
      @foreach($document->changeRequests as $cr)
      <tr>
        <td>{{ $cr->reference }}</td>
        <td>{{ $cr->reason }}</td>
        <td>@include('documents.status', ['status' => $cr->status])</td>
        <td class="text-right">
          @if($cr->status === 'open')
            <form method="post" action="/documents/{{ $document->id }}/change-request/{{ $cr->id }}/decide" style="display:inline;">
              @csrf<input type="hidden" name="decision" value="approved"><button class="btn btn-xs btn-success">Approve</button>
            </form>
            <form method="post" action="/documents/{{ $document->id }}/change-request/{{ $cr->id }}/decide" style="display:inline;">
              @csrf<input type="hidden" name="decision" value="rejected"><button class="btn btn-xs btn-danger">Reject</button>
            </form>
          @elseif($cr->status === 'approved')
            <form method="post" action="/documents/{{ $document->id }}/change-request/{{ $cr->id }}/implement" style="display:inline;">
              @csrf<button class="btn btn-xs btn-primary">Implement (new version)</button>
            </form>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

<div class="box box-default" id="copy">
  <div class="box-header with-border"><h3 class="box-title">Controlled copies</h3></div>
  <div class="box-body">
    @if($document->status === 'released')
    <form method="post" action="/documents/{{ $document->id }}/copy" class="row">
      @csrf
      <div class="col-sm-5 form-group"><input class="form-control" name="holder" placeholder="Holder (e.g. Production Line A)"></div>
      <div class="col-sm-5 form-group"><input class="form-control" name="location" placeholder="Location (e.g. Floor 1)"></div>
      <div class="col-sm-2 form-group"><button class="btn btn-default btn-block">Issue copy</button></div>
    </form>
    @else
    <p class="text-muted">Controlled copies can be issued only when the document is released.</p>
    @endif
    @if($document->controlledCopies->count())
    <table class="table table-hover">
      <thead><tr><th>Holder</th><th>Location</th><th>Version</th><th>Issued</th></tr></thead>
      <tbody>@foreach($document->controlledCopies as $c)<tr><td>{{ $c->holder }}</td><td>{{ $c->location }}</td><td>v{{ $c->version }}</td><td class="text-muted">{{ $c->issued_at?->format('d M Y, g:i A') }}</td></tr>@endforeach</tbody>
    </table>
    @endif
  </div>
</div>

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail <small class="text-muted qms-sign">tamper evident, FDA 21 CFR Part 11</small></h3></div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>Signature</th><th>Reason</th><th>When</th></tr></thead>
      <tbody>
      @foreach($audit as $a)
      <tr>
        <td>{{ $a->seq }}</td><td>{{ $a->action }}</td><td>{{ $a->username }}</td>
        <td>@if($a->signature_meaning)<span class="label label-info" style="text-transform:capitalize;">{{ $a->signature_meaning }}</span>@endif</td>
        <td class="text-muted">{{ $a->reason }}</td><td class="text-muted qms-sign">{{ $a->created_at?->format('d M Y, g:i A') }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>

@endsection
