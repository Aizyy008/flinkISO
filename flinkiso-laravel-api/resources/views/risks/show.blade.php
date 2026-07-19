@extends('layout')
@section('title', $risk->reference)
@section('page_title', 'Risk Register')
@section('page_sub', $risk->reference)
@section('menu_risks', 'active')
@section('breadcrumb')<li><a href="/risks">Risks</a></li><li class="active">{{ $risk->reference }}</li>@endsection
@php
  $uName = fn($id) => optional($users->firstWhere('id', $id))->name ?: optional($users->firstWhere('id', $id))->username;
  $cellColor = function($ls){ return $ls > 15 ? '#dd4b39' : ($ls > 9 ? '#f39c12' : ($ls > 4 ? '#f7e463' : '#a3d977')); };
@endphp
@section('content')

<div class="row">
  <div class="col-md-7">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">{{ $risk->reference }} &middot; {{ $risk->title }}</h3></div>
      <div class="box-body">
        <p>
          @include('qms._label', ['value' => $risk->risk_level])
          <span style="margin-left:8px;">Score <b>{{ $risk->risk_score }}</b> (L{{ $risk->likelihood }} &times; S{{ $risk->severity }} &times; D{{ $risk->detection }})</span>
          @include('qms._label', ['value' => $risk->status])
          @if($risk->owner_id)<span class="text-muted" style="margin-left:10px;"><i class="fa fa-user"></i> {{ $uName($risk->owner_id) }}</span>@endif
        </p>
        @if($risk->standard)<p><b>Standard:</b> {{ $risk->standard }} @if($risk->context)&middot; {{ $risk->context }}@endif</p>@endif
        @if($risk->description)<p>{{ $risk->description }}</p>@endif
        @if($risk->treatment_plan)<p><b>Treatment plan:</b> {{ $risk->treatment_plan }}</p>@endif

        <form method="post" action="/risks/{{ $risk->id }}/update" class="well" style="margin-bottom:0;">
          @csrf
          <div class="row">
            <div class="col-sm-3 form-group"><label>Likelihood</label><input type="number" min="1" max="5" class="form-control" name="likelihood" value="{{ $risk->likelihood }}"></div>
            <div class="col-sm-3 form-group"><label>Severity</label><input type="number" min="1" max="5" class="form-control" name="severity" value="{{ $risk->severity }}"></div>
            <div class="col-sm-3 form-group"><label>Detection</label><input type="number" min="1" max="5" class="form-control" name="detection" value="{{ $risk->detection }}"></div>
            <div class="col-sm-3 form-group"><label>Status</label>
              <select class="form-control" name="status">@foreach(['open','mitigated','accepted','closed'] as $s)<option value="{{ $s }}" @selected($risk->status===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
            </div>
          </div>
          <div class="form-group"><label>Treatment plan</label><textarea class="form-control" name="treatment_plan" rows="2">{{ $risk->treatment_plan }}</textarea></div>
          <button class="btn btn-primary btn-sm">Recalculate &amp; save</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Risk matrix (likelihood &times; severity)</h3></div>
      <div class="box-body">
        <table style="width:100%;border-collapse:collapse;text-align:center;font-size:12px;">
          <tr><td></td>@for($l=1;$l<=5;$l++)<td style="padding:4px;font-weight:bold;">L{{ $l }}</td>@endfor</tr>
          @for($s=5;$s>=1;$s--)
          <tr>
            <td style="padding:4px;font-weight:bold;">S{{ $s }}</td>
            @for($l=1;$l<=5;$l++)
              @php $here = ($l==$risk->likelihood && $s==$risk->severity); @endphp
              <td style="height:34px;background:{{ $cellColor($l*$s) }};color:#333;border:1px solid #fff;{{ $here ? 'outline:3px solid #111;font-weight:bold;' : '' }}">{{ $l*$s }}{!! $here ? '<br><span style="font-size:9px;">HERE</span>' : '' !!}</td>
            @endfor
          </tr>
          @endfor
        </table>
        <p class="help-block" style="margin-top:8px;">Detection factor ({{ $risk->detection }}) further multiplies the score. Overall level: <b style="text-transform:capitalize;">{{ $risk->risk_level }}</b>.</p>
      </div>
    </div>
  </div>
</div>

@include('qms._evidence', ['relatedType' => 'qms_risk', 'relatedId' => $risk->id, 'evidence' => $evidence, 'redirect' => '/risks/'.$risk->id])

<div class="box box-solid box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-lock"></i> Audit trail</h3></div>
  <div class="box-body no-padding">
    <table class="table">
      <thead><tr><th>Seq</th><th>Action</th><th>By</th><th>When</th></tr></thead>
      <tbody>
      @foreach($audit as $a)
      <tr><td>{{ $a->seq }}</td><td>{{ str_replace('_',' ',$a->action) }}</td><td>{{ $a->username }}</td><td class="text-muted small">{{ $a->created_at?->format('d M Y, g:i A') }}</td></tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
