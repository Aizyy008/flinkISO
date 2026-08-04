@extends('layout')
@section('title', 'My Tasks')
@section('page_title', 'My Tasks')
@section('page_sub', 'tasks assigned to you')
@section('menu_tasks', 'active')
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Assigned tasks</h3>
    <div class="box-tools"><span class="text-muted small">Created by workflow "assign a task" / "request an approval" actions</span></div>
  </div>
  <div class="box-body">
    @if($tasks->isEmpty())
      <p class="text-muted">You have no tasks assigned.</p>
    @else
    <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>Task</th><th>Type</th><th>Related to</th><th>Due</th><th>Status</th><th></th></tr></thead>
      <tbody>
      @foreach($tasks as $t)
      <tr class="{{ $t->status === 'open' && $t->due_date && $t->due_date->isPast() ? 'text-danger' : '' }}">
        <td><b>{{ $t->title }}</b>@if($t->description)<div class="text-muted small">{{ $t->description }}</div>@endif</td>
        <td><span class="label label-{{ $t->kind === 'approval' ? 'warning' : 'info' }}">{{ $t->kind === 'approval' ? 'Approval' : 'Task' }}</span></td>
        <td class="text-muted small">
          @if($t->related_type && $t->related_id)
            {{ str_replace('qms_', '', $t->related_type) }}
          @else — @endif
        </td>
        <td class="text-muted small">{{ $t->due_date?->format('d M Y') ?: '—' }}</td>
        <td>
          @if($t->status === 'done')
            <span class="label label-success">Done</span>
            <div class="text-muted small">{{ $t->completed_at?->format('d M Y, g:i A') }}</div>
          @else
            <span class="label label-default">Open</span>
          @endif
        </td>
        <td class="text-right">
          @if($t->status !== 'done')
          <form method="post" action="/tasks/{{ $t->id }}/complete" style="display:inline">
            @csrf
            <button class="btn btn-xs btn-primary"><i class="fa fa-check"></i> Mark done</button>
          </form>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    </div>
    @endif
  </div>
</div>
@endsection
