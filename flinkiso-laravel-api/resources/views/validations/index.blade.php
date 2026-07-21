@extends('layout')
@section('title', 'GMP / Validation')
@section('page_title', 'GMP / Validation logs')
@section('menu_validations', 'active')
@section('breadcrumb')<li class="active">Validation</li>@endsection
@php $types = \App\Models\Qms\Validation::TYPES; $sb = ['approved'=>'success','in_progress'=>'info','rejected'=>'danger','expired'=>'warning','planned'=>'default']; $rb = ['valid'=>'success','due'=>'warning','expired'=>'danger','n/a'=>'default']; @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Validation records</h3>
    <div class="box-tools">
      <a class="btn btn-primary btn-sm" href="/validations/create"><i class="fa fa-plus"></i> New validation</a>
    </div>
  </div>
  <div class="box-body no-padding">
    @if($validations->count())
    <table class="table table-hover">
      <thead><tr><th>Ref</th><th>Type</th><th>Title</th><th>Subject</th><th>Performed</th><th>Result</th><th>Status</th><th>Revalidation</th><th></th></tr></thead>
      <tbody>
      @foreach($validations as $v)
      @php $rs = $v->revalidationStatus(); @endphp
      <tr>
        <td><b>{{ $v->reference }}</b></td>
        <td class="small">{{ $types[$v->type] ?? $v->type }}</td>
        <td>{{ $v->title }}</td>
        <td class="small">{{ $v->subject ?: ($v->asset->name ?? '—') }}</td>
        <td class="small">{{ $v->performed_date?->format('d M Y') ?: '—' }}</td>
        <td>@if($v->result)<span class="label label-{{ $v->result==='pass'?'success':($v->result==='fail'?'danger':'warning') }}">{{ $v->result }}</span>@else — @endif</td>
        <td><span class="label label-{{ $sb[$v->status] ?? 'default' }}">{{ str_replace('_',' ',$v->status) }}</span></td>
        <td>@if($rs==='n/a') — @else<span class="label label-{{ $rb[$rs] ?? 'default' }}">{{ $rs }}</span>@endif</td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="/validations/{{ $v->id }}">Open</a></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else<div class="box-body"><p class="text-muted">No validation records yet. <a href="/validations/create">Add one</a>.</p></div>@endif
  </div>
  @if($validations->count())<div class="box-footer">{{ $validations->links() }}</div>@endif
</div>
@endsection
