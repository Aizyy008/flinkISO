@extends('layout')
@section('title', $training->title)
@section('page_title', 'Training &amp; Competency')
@section('page_sub', $training->reference)
@section('menu_training', 'active')
@section('breadcrumb')<li><a href="/training">Training</a></li><li class="active">{{ $training->reference }}</li>@endsection
@php
  $badge = ['assigned'=>'default','valid'=>'success','expiring'=>'warning','expired'=>'danger'];
  $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username;
@endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">{{ $training->reference }} &middot; {{ $training->title }}</h3></div>
  <div class="box-body">
    @if($training->mandatory)<span class="label label-warning">mandatory</span> @endif
    <span class="text-muted">Retraining: {{ $training->validity_months ? $training->validity_months.' months' : 'no expiry' }}</span>
    @if($training->category)<span class="text-muted" style="margin-left:10px;">{{ $training->category }}</span>@endif
    @if($training->description)<p style="margin-top:8px;">{{ $training->description }}</p>@endif

    <form method="post" action="/training/{{ $training->id }}/assign" class="form-inline" style="margin-top:10px;">
      @csrf
      <select name="user_id" class="form-control input-sm">
        @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach
      </select>
      <button class="btn btn-sm btn-primary">Assign to employee</button>
    </form>
  </div>
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Assigned employees</h3></div>
  <div class="box-body no-padding">
    @if($records->count())
    <table class="table table-hover">
      <thead><tr><th>Employee</th><th>Status</th><th>Completed</th><th>Expires</th><th></th></tr></thead>
      <tbody>
      @foreach($records as $r)
      @php $comp = $r->competency(); @endphp
      <tr>
        <td>{{ $uName($r->user_id) }}</td>
        <td><span class="label label-{{ $badge[$comp] ?? 'default' }}">{{ $comp }}</span></td>
        <td class="small text-muted">{{ $r->completed_date?->format('d M Y') }}</td>
        <td class="small text-muted">{{ $r->expiry_date?->format('d M Y') }}</td>
        <td class="text-right">
          @if($r->status !== 'completed' || $comp === 'expired')
          <form method="post" action="/training/record/{{ $r->id }}/complete" class="form-inline" style="display:inline;">
            @csrf
            <input type="date" name="completed_date" class="form-control input-sm" value="{{ now()->toDateString() }}">
            <button class="btn btn-xs btn-success">Mark complete</button>
          </form>
          @endif
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else<div class="box-body"><p class="text-muted">No employees assigned yet.</p></div>@endif
  </div>
</div>
@endsection
