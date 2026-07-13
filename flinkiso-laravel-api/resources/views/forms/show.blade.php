@extends('layout')
@section('title', $form->name)
@section('page_title', 'Form Builder bridge')
@section('page_sub', $form->name)
@section('menu_forms', 'active')
@section('breadcrumb')<li><a href="/forms">Forms</a></li><li class="active">{{ $form->name }}</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">{{ $form->name }} <small class="text-muted"><code>{{ $form->table_name }}</code></small></h3>
  </div>
  <div class="box-body">
    @if(!$exists)
    <p class="text-muted">This form has no data table yet.</p>
    @elseif($rows->count())
    <p class="text-muted">Latest {{ $rows->count() }} submission(s), read live from the FlinkISO database.</p>
    <div class="table-responsive">
      <table class="table table-hover table-bordered">
        <thead><tr>@foreach($columns as $c)<th>{{ ucwords(str_replace('_',' ',$c)) }}</th>@endforeach</tr></thead>
        <tbody>
        @foreach($rows as $row)
        <tr>
          @foreach($columns as $c)
            <td>{{ \Illuminate\Support\Str::limit((string)($row->$c ?? ''), 60) }}</td>
          @endforeach
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    @else
    <p class="text-muted">No submissions in this form yet.</p>
    @endif
  </div>
</div>
@endsection
