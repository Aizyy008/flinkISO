@extends('layout')
@section('title', 'CAPA')
@section('page_title', 'CAPA')
@section('page_sub', 'corrective &amp; preventive actions')
@section('menu_capa', 'active')
@section('breadcrumb')<li class="active">CAPA</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Corrective / Preventive Actions</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/capa/create"><i class="fa fa-plus"></i> New CAPA</a></div>
  </div>
  <div class="box-body">
    <form method="get" class="form-inline" style="margin-bottom:12px;">
      <select name="status" class="form-control input-sm" onchange="this.form.submit()">
        <option value="">All statuses</option>
        @foreach(['open','in_progress','effectiveness_check','closed','cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>@endforeach
      </select>
    </form>
    @if($capas->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Ref</th><th>Title</th><th>Type</th><th>From incident</th><th>Owner</th><th>Due</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($capas as $c)
        <tr>
          <td><b>{{ $c->reference }}</b></td>
          <td>{{ $c->title }}</td>
          <td>@include('qms._label', ['value' => $c->type])</td>
          <td>@if($c->incident)<a href="/incidents/{{ $c->incident->id }}">{{ $c->incident->reference }}</a>@else <span class="text-muted">standalone</span>@endif</td>
          <td>{{ $c->assigned_to ? 'assigned' : '' }}</td>
          <td class="text-muted small">{{ $c->due_date?->format('d M Y') }}</td>
          <td>@include('qms._label', ['value' => $c->status])</td>
          <td class="text-right"><a class="btn btn-default btn-xs" href="/capa/{{ $c->id }}"><i class="fa fa-folder-open"></i> Open</a></td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    {{ $capas->links() }}
    @else
    <p class="text-muted">No CAPA yet.</p>
    @endif
  </div>
</div>
@endsection
