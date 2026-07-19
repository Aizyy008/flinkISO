@extends('layout')
@section('title', 'Risk Register')
@section('page_title', 'Risk Register')
@section('page_sub', 'likelihood &times; severity &times; detection')
@section('menu_risks', 'active')
@section('breadcrumb')<li class="active">Risks</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Risk Register</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/risks/create"><i class="fa fa-plus"></i> New risk</a></div>
  </div>
  <div class="box-body">
    <form method="get" class="form-inline" style="margin-bottom:12px;">
      <select name="risk_level" class="form-control input-sm" onchange="this.form.submit()">
        <option value="">All levels</option>
        @foreach(['low','medium','high','critical'] as $l)<option value="{{ $l }}" @selected(request('risk_level')===$l)>{{ ucfirst($l) }}</option>@endforeach
      </select>
      <select name="status" class="form-control input-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(['open','mitigated','accepted','closed'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach
      </select>
    </form>
    @if($risks->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Ref</th><th>Title</th><th>Standard</th><th>L</th><th>S</th><th>D</th><th>Score</th><th>Level</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($risks as $r)
        <tr>
          <td><b>{{ $r->reference }}</b></td>
          <td>{{ $r->title }}</td>
          <td>{{ $r->standard }}</td>
          <td>{{ $r->likelihood }}</td><td>{{ $r->severity }}</td><td>{{ $r->detection }}</td>
          <td><b>{{ $r->risk_score }}</b></td>
          <td>@include('qms._label', ['value' => $r->risk_level])</td>
          <td>@include('qms._label', ['value' => $r->status])</td>
          <td class="text-right"><a class="btn btn-default btn-xs" href="/risks/{{ $r->id }}"><i class="fa fa-folder-open"></i> Open</a></td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    {{ $risks->links() }}
    @else
    <p class="text-muted">No risks yet.</p>
    @endif
  </div>
</div>
@endsection
