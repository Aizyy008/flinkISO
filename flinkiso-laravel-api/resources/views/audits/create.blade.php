@extends('layout')
@section('title', 'Schedule audit')
@section('page_title', 'Audit Management')
@section('page_sub', 'schedule an audit')
@section('menu_audits', 'active')
@section('breadcrumb')<li><a href="/audits">Audits</a></li><li class="active">Schedule</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">Schedule an audit</h3></div>
      <form method="post" action="/audits">
        @csrf
        <div class="box-body">
          <div class="form-group"><label>Title *</label><input class="form-control" name="title" required placeholder="e.g. Internal audit — Production, ISO 9001"></div>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Type *</label>
              <select class="form-control" name="audit_type">@foreach(['internal','external','supplier'] as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select>
            </div>
            <div class="col-sm-4 form-group"><label>Standard</label>
              <select class="form-control" name="standard"><option value="">(none)</option>@foreach($standards as $s)<option value="{{ $s->name }}">{{ $s->name }}</option>@endforeach</select>
            </div>
            <div class="col-sm-4 form-group"><label>Program</label>
              <select class="form-control" name="program_id"><option value="">(none)</option>@foreach($programs as $p)<option value="{{ $p->id }}">{{ $p->reference }} — {{ $p->title }}</option>@endforeach</select>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group"><label>Lead auditor</label>
              <select class="form-control" name="lead_auditor_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach</select>
            </div>
            <div class="col-sm-6 form-group"><label>Auditor</label>
              <select class="form-control" name="auditor_id"><option value="">(unassigned)</option>@foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach</select>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Planned date</label><input class="form-control" type="date" name="planned_date"></div>
            <div class="col-sm-4 form-group"><label>Related clause</label><input class="form-control" name="related_clause" placeholder="e.g. 8.5"></div>
            <div class="col-sm-4 form-group"><label>Related process</label><input class="form-control" name="related_process"></div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group"><label>Related site</label><input class="form-control" name="related_site"></div>
            <div class="col-sm-6 form-group"><label>Related department</label><input class="form-control" name="related_department"></div>
          </div>
          <div class="form-group"><label>Scope</label><textarea class="form-control" name="scope" rows="2"></textarea></div>
        </div>
        <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-calendar-check-o"></i> Schedule audit</button> <a href="/audits" class="btn btn-default">Cancel</a></div>
      </form>
    </div>
  </div>
  <div class="col-md-4">
    <div class="box box-default" id="program">
      <div class="box-header with-border"><h3 class="box-title">New annual program</h3></div>
      <form method="post" action="/audits/program">
        @csrf
        <div class="box-body">
          <div class="form-group"><label>Year *</label><input class="form-control" type="number" name="year" value="{{ date('Y') }}" required></div>
          <div class="form-group"><label>Title *</label><input class="form-control" name="title" placeholder="Annual audit program {{ date('Y') }}" required></div>
          <div class="form-group"><label>Objectives</label><textarea class="form-control" name="objectives" rows="2"></textarea></div>
        </div>
        <div class="box-footer"><button class="btn btn-default"><i class="fa fa-plus"></i> Create program</button></div>
      </form>
    </div>
  </div>
</div>
@endsection
