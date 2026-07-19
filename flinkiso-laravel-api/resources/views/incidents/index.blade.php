@extends('layout')
@section('title', 'Incidents')
@section('page_title', 'Incidents')
@section('page_sub', 'non-conformities &amp; deviations')
@section('menu_incidents', 'active')
@section('breadcrumb')<li class="active">Incidents</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Incidents / Non-conformities</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/incidents/create"><i class="fa fa-plus"></i> New incident</a></div>
  </div>
  <div class="box-body">
    <form method="get" class="form-inline" style="margin-bottom:12px;">
      <select name="status" class="form-control input-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(\App\Models\Qms\Incident::STATUSES as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
      </select>
      <select name="type" class="form-control input-sm" onchange="this.form.submit()">
        <option value="">All types</option>
        @foreach(\App\Models\Qms\Incident::TYPES as $t)<option value="{{ $t }}" @selected(request('type')===$t)>{{ ucwords(str_replace('_',' ',$t)) }}</option>@endforeach
      </select>
    </form>
    @if($incidents->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Ref</th><th>Title</th><th>Type</th><th>Severity</th><th>Status</th><th>CAPA</th><th></th></tr></thead>
        <tbody>
        @foreach($incidents as $i)
        <tr>
          <td><b>{{ $i->reference }}</b></td>
          <td>{{ $i->title }}</td>
          <td>@include('qms._label', ['value' => $i->type])</td>
          <td>@include('qms._label', ['value' => $i->severity])</td>
          <td>@include('qms._label', ['value' => $i->status])</td>
          <td>{{ $i->capas_count ?: '' }}</td>
          <td class="text-right"><a class="btn btn-default btn-xs" href="/incidents/{{ $i->id }}"><i class="fa fa-folder-open"></i> Open</a></td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    {{ $incidents->links() }}
    @else
    <p class="text-muted">No incidents yet.</p>
    @endif
  </div>
</div>
@endsection
