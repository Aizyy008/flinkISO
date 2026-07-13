@extends('layout')
@section('title', 'Assets &amp; Calibration')
@section('page_title', 'Assets &amp; Calibration')
@section('menu_calibration', 'active')
@section('breadcrumb')<li class="active">Calibration</li>@endsection
@php $badge = ['ok'=>'success','due'=>'warning','overdue'=>'danger','n/a'=>'default']; @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Assets</h3>
    <div class="box-tools">
      <a class="btn btn-default btn-sm {{ request('filter')=='due'?'active':'' }}" href="/assets?filter=due">Due / overdue</a>
      <a class="btn btn-default btn-sm {{ !request('filter')?'active':'' }}" href="/assets">All</a>
      <a class="btn btn-primary btn-sm" href="/assets/create"><i class="fa fa-plus"></i> New asset</a>
    </div>
  </div>
  <div class="box-body no-padding">
    @if($assets->count())
    <table class="table table-hover">
      <thead><tr><th>Ref</th><th>Name</th><th>Type</th><th>Location</th><th>Next due</th><th>Calibration</th><th></th></tr></thead>
      <tbody>
      @foreach($assets as $a)
      @php $cs = $a->calibrationStatus(); @endphp
      <tr class="{{ $cs==='overdue'?'text-danger':'' }}">
        <td><b>{{ $a->reference }}</b></td>
        <td>{{ $a->name }}</td>
        <td>{{ $a->asset_type }}</td>
        <td>{{ $a->location }}</td>
        <td class="small">{{ $a->next_due_date?->format('d M Y') }}</td>
        <td><span class="label label-{{ $badge[$cs] ?? 'default' }}">{{ $cs }}</span></td>
        <td class="text-right"><a class="btn btn-xs btn-default" href="/assets/{{ $a->id }}">Open</a></td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else<div class="box-body"><p class="text-muted">No assets yet.</p></div>@endif
  </div>
  @if($assets->count())<div class="box-footer">{{ $assets->links() }}</div>@endif
</div>
@endsection
