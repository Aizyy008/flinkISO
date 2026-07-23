@extends('layout')
@section('title', 'New document')
@section('page_title', 'Document Control')
@section('page_sub', 'new document')
@section('menu_documents', 'active')
@section('breadcrumb')<li><a href="/documents">Documents</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-10">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">New controlled document</h3></div>
      <form method="post" action="/documents">
        @csrf
        <div class="box-body">
          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Document number *</label>
              <input class="form-control" name="doc_number" value="{{ old('doc_number') }}" placeholder="SOP 001" required>
            </div>
            <div class="col-sm-3 form-group">
              <label>Category *</label>
              <select class="form-control" name="category">
                <option>SOP</option><option>WI</option><option>Form</option><option>Policy</option><option>HACCP record</option>
              </select>
            </div>
            <div class="col-sm-3 form-group">
              <label>Document type</label>
              <input class="form-control" name="document_type" value="{{ old('document_type') }}" placeholder="Procedure / Manual ...">
            </div>
            <div class="col-sm-2 form-group">
              <label>Issue no.</label>
              <input class="form-control" type="number" min="1" name="issue_number" value="{{ old('issue_number', 1) }}">
            </div>
          </div>
          <div class="form-group">
            <label>Title *</label>
            <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Cleaning and Sanitation SOP" required>
          </div>
          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Related ISO standard</label>
              <select class="form-control" name="related_standard_id">
                <option value="">(none)</option>
                @foreach($standards as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>Related clause</label>
              <input class="form-control" name="related_clause_id" value="{{ old('related_clause_id') }}" placeholder="e.g. 7.5.3">
            </div>
            <div class="col-sm-4 form-group">
              <label>Review due date</label>
              <input class="form-control" type="date" name="review_due_date" value="{{ old('review_due_date') }}">
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Reviewer</label>
              <select class="form-control" name="reviewer_id">
                <option value="">(unassigned)</option>
                @foreach($reviewers as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach
              </select>
              @if($reviewers->isEmpty())<small class="text-muted">No users have the Reviewer role — assign one under <a href="/users">Users &amp; Roles</a>.</small>@endif
            </div>
            <div class="col-sm-4 form-group">
              <label>Approver</label>
              <select class="form-control" name="approver_id">
                <option value="">(unassigned)</option>
                @foreach($approvers as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach
              </select>
              @if($approvers->isEmpty())<small class="text-muted">No users have the Approver role — assign one under <a href="/users">Users &amp; Roles</a>.</small>@endif
            </div>
            <div class="col-sm-4 form-group">
              <label>Publisher</label>
              <select class="form-control" name="publisher_id">
                <option value="">(unassigned)</option>
                @foreach($publishers as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach
              </select>
              @if($publishers->isEmpty())<small class="text-muted">No users have the Publisher role — assign one under <a href="/users">Users &amp; Roles</a>.</small>@endif
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Related process</label><input class="form-control" name="related_process" value="{{ old('related_process') }}"></div>
            <div class="col-sm-4 form-group"><label>Related site</label><input class="form-control" name="related_site" value="{{ old('related_site') }}"></div>
            <div class="col-sm-4 form-group"><label>Related department</label><input class="form-control" name="related_department" value="{{ old('related_department') }}"></div>
          </div>
          <div class="form-group">
            <label>Change summary <small class="text-muted">(optional)</small></label>
            <textarea class="form-control" name="change_summary" rows="2" placeholder="Initial version"></textarea>
          </div>
        </div>
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Create as draft</button>
          <a class="btn btn-default" href="/documents">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
