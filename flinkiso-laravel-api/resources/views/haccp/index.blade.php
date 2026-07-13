@extends('layout')
@section('title', 'HACCP')
@section('page_title', 'HACCP')
@section('page_sub', 'food safety plans (ISO 22000)')
@section('menu_haccp', 'active')
@section('breadcrumb')<li class="active">HACCP</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">HACCP Plans</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/haccp/create"><i class="fa fa-plus"></i> New plan</a></div>
  </div>
  <div class="box-body">
    @if($plans->count())
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Ref</th><th>Product</th><th>Hazards</th><th>CCPs</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach($plans as $p)
        <tr>
          <td><b>{{ $p->reference }}</b></td>
          <td>{{ $p->product }}</td>
          <td>{{ $p->hazards_count }}</td>
          <td>{{ $p->ccps_count }}</td>
          <td>@include('qms._label', ['value' => $p->status])</td>
          <td class="text-right"><a class="btn btn-default btn-xs" href="/haccp/{{ $p->id }}"><i class="fa fa-folder-open"></i> Open</a></td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    {{ $plans->links() }}
    @else
    <p class="text-muted">No HACCP plans yet.</p>
    @endif
  </div>
</div>
@endsection
