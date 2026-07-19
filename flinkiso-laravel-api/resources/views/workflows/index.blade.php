@extends('layout')
@section('title', 'Workflow rules')
@section('page_title', 'Workflow rules')
@section('page_sub', 'triggers, conditions &amp; actions')
@section('menu_workflows', 'active')
@section('breadcrumb')<li class="active">Workflows</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Rules</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/workflows/create"><i class="fa fa-plus"></i> New rule</a></div>
  </div>
  <div class="box-body no-padding">
    @if($workflows->count())
    <table class="table table-hover">
      <thead><tr><th>Name</th><th>Trigger</th><th>Conditions</th><th>Actions</th><th>Active</th><th></th></tr></thead>
      <tbody>
      @foreach($workflows as $w)
      <tr>
        <td><b>{{ $w->name }}</b></td>
        <td><code>{{ $w->trigger_event }}</code></td>
        <td class="small">@foreach($w->conditions ?? [] as $c){{ $c['field'] }} {{ $c['op'] }} {{ $c['value'] }}<br>@endforeach</td>
        <td class="small">@foreach($w->actions as $a)<span class="label label-info">{{ str_replace('_',' ',$a['type']) }}</span> @endforeach</td>
        <td>@include('qms._label', ['value' => $w->active ? 'implemented' : 'cancelled'])</td>
        <td class="text-right"><form method="post" action="/workflows/{{ $w->id }}/toggle" style="display:inline;">@csrf<button class="btn btn-xs btn-default">{{ $w->active ? 'Disable' : 'Enable' }}</button></form></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="box-body"><p class="text-muted">No workflow rules yet. Create one to auto-raise CAPA or send notifications.</p></div>
    @endif
  </div>
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Recent executions</h3></div>
  <div class="box-body no-padding">
    @if($runs->count())
    <table class="table">
      <thead><tr><th>When</th><th>Trigger</th><th>Entity</th><th>Result</th></tr></thead>
      <tbody>
      @foreach($runs as $r)
      <tr>
        <td class="text-muted small">{{ $r->created_at?->format('d M Y, g:i A') }}</td>
        <td><code>{{ $r->trigger_event }}</code></td>
        <td class="small">{{ str_replace('qms_','',$r->entity_type) }}</td>
        <td>@include('qms._label', ['value' => $r->status]) <span class="text-muted small">{{ collect($r->result ?? [])->pluck('type')->implode(', ') }}</span></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="box-body"><p class="text-muted">No executions yet. They appear here when a rule fires (e.g. after creating a matching incident).</p></div>
    @endif
  </div>
</div>
@endsection
