@extends('layout')
@section('title', 'Training')
@section('page_title', 'Training &amp; Competency')
@section('menu_training', 'active')
@section('breadcrumb')<li class="active">Training</li>@endsection
@php
  $badge = ['assigned'=>'default','valid'=>'success','expiring'=>'warning','expired'=>'danger'];
@endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Training courses</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/training/create"><i class="fa fa-plus"></i> New training</a></div>
  </div>
  <div class="box-body no-padding">
    @if($trainings->count())
    <table class="table table-hover">
      <thead><tr><th>Ref</th><th>Title</th><th>Category</th><th>Retraining</th><th>Mandatory</th><th></th></tr></thead>
      <tbody>
      @foreach($trainings as $t)
      <tr>
        <td><b>{{ $t->reference }}</b></td><td>{{ $t->title }}</td><td>{{ $t->category }}</td>
        <td>{{ $t->validity_months ? $t->validity_months.' months' : 'no expiry' }}</td>
        <td>@if($t->mandatory)<span class="label label-warning">mandatory</span>@endif</td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="/training/{{ $t->id }}">Open</a></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else<div class="box-body"><p class="text-muted">No training courses yet.</p></div>@endif
  </div>
</div>

@if($trainings->count() && $users->count())
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Competency matrix</h3></div>
  <div class="box-body table-responsive">
    <table class="table table-bordered">
      <thead><tr><th>Employee</th>@foreach($trainings as $t)<th class="small text-center">{{ $t->title }}</th>@endforeach</tr></thead>
      <tbody>
      @foreach($users as $u)
      <tr>
        <td>{{ $u->name ?: $u->username }}</td>
        @foreach($trainings as $t)
          @php $rec = optional($records->get($u->id.'|'.$t->id))?->last(); $comp = $rec ? $rec->competency() : null; @endphp
          <td class="text-center">
            @if($comp)<span class="label label-{{ $badge[$comp] ?? 'default' }}">{{ $comp }}</span>@else<span class="text-muted">&middot;</span>@endif
          </td>
        @endforeach
      </tr>
      @endforeach
      </tbody>
    </table>
    <p class="help-block">
      <span class="label label-success">valid</span>
      <span class="label label-warning">expiring (&le; 30 days)</span>
      <span class="label label-danger">expired</span>
      <span class="label label-default">assigned (not completed)</span>
    </p>
  </div>
</div>
@endif
@endsection
