@extends('layout')
@section('title', $plan->reference)
@section('page_title', 'HACCP')
@section('page_sub', $plan->product)
@section('menu_haccp', 'active')
@section('breadcrumb')<li><a href="/haccp">HACCP</a></li><li class="active">{{ $plan->reference }}</li>@endsection
@php $allowed = ['draft'=>'approved','approved'=>'active','active'=>'obsolete','obsolete'=>null]; @endphp
@section('content')

<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">{{ $plan->reference }} &middot; {{ $plan->product }}</h3></div>
  <div class="box-body">
    <p>@include('qms._label', ['value' => $plan->status])
      @if($plan->team)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-users"></i> {{ $plan->team }}</span>@endif
      @if($plan->approved_date)<span class="text-muted" style="margin-left:10px;">approved {{ $plan->approved_date->format('d M Y') }}</span>@endif
    </p>
    @if($plan->description)<p>{{ $plan->description }}</p>@endif
    @if($allowed[$plan->status] ?? false)
    <form method="post" action="/haccp/{{ $plan->id }}/transition" style="display:inline;">
      @csrf<input type="hidden" name="to" value="{{ $allowed[$plan->status] }}">
      <button class="btn btn-sm btn-success">Move to {{ ucfirst($allowed[$plan->status]) }}@if($allowed[$plan->status]==='approved') (e-sign)@endif</button>
    </form>
    @endif
  </div>
</div>

<div class="row">
  {{-- Process steps --}}
  <div class="col-md-4">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Process flow</h3></div>
      <div class="box-body no-padding">
        <table class="table table-condensed">
          @foreach($plan->steps as $s)<tr><td style="width:30px;">{{ $s->seq }}</td><td>{{ $s->name }}</td></tr>@endforeach
        </table>
      </div>
      <div class="box-footer">
        <form method="post" action="/haccp/{{ $plan->id }}/step">
          @csrf<div class="input-group input-group-sm">
            <input class="form-control" name="name" placeholder="e.g. Pasteurization">
            <span class="input-group-btn"><button class="btn btn-default">Add</button></span>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Hazard analysis --}}
  <div class="col-md-8">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Hazard analysis</h3></div>
      <div class="box-body no-padding">
        <table class="table">
          <thead><tr><th>Step</th><th>Type</th><th>Significance</th><th>Control</th><th>Type</th></tr></thead>
          <tbody>
          @foreach($plan->hazards as $h)
          <tr>
            <td>{{ optional($h->step)->name }}</td>
            <td>@include('qms._label', ['value' => $h->hazard_type])</td>
            <td>@include('qms._label', ['value' => $h->significance])</td>
            <td class="small">{{ $h->control_measure }}</td>
            <td><span class="label label-{{ $h->control_type==='CCP'?'danger':($h->control_type==='OPRP'?'warning':'default') }}">{{ $h->control_type }}</span></td>
          </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <div class="box-footer">
        <form method="post" action="/haccp/{{ $plan->id }}/hazard" class="row">
          @csrf
          <div class="col-sm-3"><select class="form-control input-sm" name="step_id"><option value="">step...</option>@foreach($plan->steps as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
          <div class="col-sm-2"><select class="form-control input-sm" name="hazard_type">@foreach(\App\Models\Qms\HaccpHazard::TYPES as $t)<option value="{{ $t }}">{{ ucfirst($t) }}</option>@endforeach</select></div>
          <div class="col-sm-2"><select class="form-control input-sm" name="significance"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option></select></div>
          <div class="col-sm-3"><input class="form-control input-sm" name="control_measure" placeholder="control measure"></div>
          <div class="col-sm-2">
            <div class="input-group input-group-sm">
              <select class="form-control" name="control_type"><option>PRP</option><option>OPRP</option><option>CCP</option></select>
              <span class="input-group-btn"><button class="btn btn-default">Add</button></span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- CCPs + monitoring --}}
<div class="box box-warning">
  <div class="box-header with-border"><h3 class="box-title">Critical Control Points (CCP) &amp; monitoring</h3></div>
  <div class="box-body">
    @forelse($plan->ccps as $ccp)
    <div class="well" style="margin-bottom:12px;">
      <b>{{ $ccp->name }}</b> &middot; critical limit: <b>{{ $ccp->critical_limit }}</b>
      @if($ccp->limit_min!==null || $ccp->limit_max!==null)<span class="text-muted small">(numeric: {{ $ccp->limit_min ?? '-' }} to {{ $ccp->limit_max ?? '-' }})</span>@endif
      <div class="text-muted small">Monitor: {{ $ccp->monitor_what }} &middot; {{ $ccp->monitor_frequency }} &middot; {{ $ccp->responsible }}</div>
      <form method="post" action="/haccp/ccp/{{ $ccp->id }}/log" class="row" style="margin-top:8px;">
        @csrf
        <div class="col-sm-2"><input class="form-control input-sm" name="batch_no" placeholder="batch no"></div>
        <div class="col-sm-2"><input class="form-control input-sm" type="number" step="any" name="measured_value" placeholder="temperature"></div>
        <div class="col-sm-2"><input class="form-control input-sm" name="measured_time" placeholder="time (15 sec)"></div>
        <div class="col-sm-3"><input class="form-control input-sm" name="notes" placeholder="notes"></div>
        <div class="col-sm-2"><button class="btn btn-sm btn-warning btn-block">Log reading</button></div>
      </form>
    </div>
    @empty
    <p class="text-muted">No CCP defined yet. Add one below (e.g. Pasteurization, 72C for 15 sec).</p>
    @endforelse

    <form method="post" action="/haccp/{{ $plan->id }}/ccp" class="row">
      @csrf
      <div class="col-sm-2"><select class="form-control input-sm" name="step_id"><option value="">step...</option>@foreach($plan->steps as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
      <div class="col-sm-2"><input class="form-control input-sm" name="name" placeholder="CCP name"></div>
      <div class="col-sm-2"><input class="form-control input-sm" name="critical_limit" placeholder="72C for 15 sec"></div>
      <div class="col-sm-1"><input class="form-control input-sm" type="number" step="any" name="limit_min" placeholder="min"></div>
      <div class="col-sm-1"><input class="form-control input-sm" type="number" step="any" name="limit_max" placeholder="max"></div>
      <div class="col-sm-2"><input class="form-control input-sm" name="monitor_what" placeholder="monitor (Temperature)"></div>
      <div class="col-sm-2"><button class="btn btn-sm btn-default btn-block">Add CCP</button></div>
    </form>
    <p class="help-block">Set numeric min/max so out of limit readings auto raise a deviation and CAPA.</p>
  </div>
</div>

{{-- Monitoring log --}}
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title">CCP monitoring log</h3></div>
  <div class="box-body no-padding">
    @if($logs->count())
    <table class="table table-hover">
      <thead><tr><th>When</th><th>CCP</th><th>Batch</th><th>Temperature</th><th>Time</th><th>Operator</th><th>Result</th><th>CAPA</th></tr></thead>
      <tbody>
      @foreach($logs as $l)
      <tr class="{{ $l->result==='deviation'?'text-danger':'' }}">
        <td class="small">{{ $l->measured_at?->format('d M Y, g:i A') }}</td>
        <td>{{ optional($plan->ccps->firstWhere('id',$l->ccp_id))->name }}</td>
        <td>{{ $l->batch_no }}</td>
        <td>{{ $l->measured_value }}</td>
        <td>{{ $l->measured_time }}</td>
        <td class="small">{{ $l->logged_by ? 'logged' : '' }}</td>
        <td>@include('qms._label', ['value' => $l->result==='deviation'?'critical':'implemented'])</td>
        <td>@if($l->capa_id)<a href="/capa/{{ $l->capa_id }}">view CAPA</a>@endif</td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="box-body"><p class="text-muted">No monitoring readings logged yet.</p></div>
    @endif
  </div>
</div>

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail</h3></div>
  <div class="box-body no-padding">
    <table class="table">
      <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>When</th></tr></thead>
      <tbody>
      @foreach($audit as $a)<tr><td>{{ $a->seq }}</td><td>{{ str_replace('_',' ',$a->action) }}</td><td>{{ $a->username }}</td><td class="small text-muted">{{ $a->created_at?->format('d M Y, g:i A') }}</td></tr>@endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
