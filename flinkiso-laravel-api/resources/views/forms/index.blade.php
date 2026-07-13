@extends('layout')
@section('title', 'Forms bridge')
@section('page_title', 'Form Builder bridge')
@section('page_sub', 'custom forms from FlinkISO')
@section('menu_forms', 'active')
@section('breadcrumb')<li class="active">Forms</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Custom forms &amp; submissions</h3>
  </div>
  <div class="box-body">
    <p class="text-muted">These are the forms built in the FlinkISO Form Builder. Their submissions are read here (read only) and made available to the QMS.</p>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Form</th><th>Table</th><th>Submissions</th><th></th></tr></thead>
        <tbody>
        @foreach($forms as $f)
        <tr>
          <td><b>{{ $f->name }}</b></td>
          <td><code>{{ $f->table_name }}</code></td>
          <td>@if($f->exists){{ $f->submissions }}@else<span class="text-muted">no table</span>@endif</td>
          <td class="text-right">@if($f->exists)<a class="btn btn-xs btn-default" href="/forms/{{ $f->id }}">View submissions</a>@endif</td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
