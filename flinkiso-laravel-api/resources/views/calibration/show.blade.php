@extends('layout')
@section('title', $asset->reference)
@section('page_title', 'Assets &amp; Calibration')
@section('page_sub', $asset->name)
@section('menu_calibration', 'active')
@section('breadcrumb')<li><a href="/assets">Assets</a></li><li class="active">{{ $asset->reference }}</li>@endsection
@php $badge = ['ok'=>'success','due'=>'warning','overdue'=>'danger','n/a'=>'default']; $cs = $asset->calibrationStatus(); @endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">{{ $asset->reference }} &middot; {{ $asset->name }}</h3></div>
  <div class="box-body">
    <p>
      <span class="label label-{{ $badge[$cs] ?? 'default' }}">{{ $cs }}</span>
      <span class="text-muted" style="margin-left:10px;">{{ $asset->asset_type }}</span>
      @if($asset->serial_no)<span class="text-muted" style="margin-left:10px;">S/N {{ $asset->serial_no }}</span>@endif
      @if($asset->location)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-map-marker"></i> {{ $asset->location }}</span>@endif
    </p>
    <p class="text-muted">
      Cycle: {{ $asset->calibration_frequency_months ? $asset->calibration_frequency_months.' months' : 'not set' }} &middot;
      Next due: {{ $asset->next_due_date?->format('d M Y') ?? 'not scheduled' }}
    </p>
  </div>
</div>

<div class="box box-warning">
  <div class="box-header with-border"><h3 class="box-title">Record a calibration</h3></div>
  <form method="post" action="/assets/{{ $asset->id }}/calibration">
    @csrf
    <div class="box-body row">
      <div class="col-sm-3 form-group"><label>Performed date *</label><input type="date" class="form-control" name="performed_date" value="{{ now()->toDateString() }}"></div>
      <div class="col-sm-2 form-group"><label>Result *</label><select class="form-control" name="result"><option value="pass">Pass</option><option value="fail">Fail</option></select></div>
      <div class="col-sm-3 form-group"><label>Performed by</label><input class="form-control" name="performed_by" placeholder="lab / person"></div>
      <div class="col-sm-4 form-group"><label>Notes</label><input class="form-control" name="notes"></div>
    </div>
    <div class="box-footer"><button class="btn btn-warning"><i class="fa fa-check"></i> Record calibration</button></div>
  </form>
</div>

<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">Calibration history</h3></div>
  <div class="box-body no-padding">
    @if($asset->calibrations->count())
    <table class="table table-hover">
      <thead><tr><th>Performed</th><th>Result</th><th>By</th><th>Next due</th><th>Notes</th></tr></thead>
      <tbody>
      @foreach($asset->calibrations as $c)
      <tr>
        <td class="small">{{ $c->performed_date?->format('d M Y') }}</td>
        <td><span class="label label-{{ $c->result==='pass'?'success':'danger' }}">{{ $c->result }}</span></td>
        <td>{{ $c->performed_by }}</td>
        <td class="small">{{ $c->next_due_date?->format('d M Y') }}</td>
        <td class="small text-muted">{{ $c->notes }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else<div class="box-body"><p class="text-muted">No calibration recorded yet.</p></div>@endif
  </div>
</div>
@endsection
