@extends('layout')
@section('title', $v->reference)
@section('page_title', 'GMP / Validation logs')
@section('page_sub', $v->reference)
@section('menu_validations', 'active')
@section('breadcrumb')<li><a href="/validations">Validation</a></li><li class="active">{{ $v->reference }}</li>@endsection
@php $types = \App\Models\Qms\Validation::TYPES; $sb = ['approved'=>'success','in_progress'=>'info','rejected'=>'danger','expired'=>'warning','planned'=>'default']; $rs = $v->revalidationStatus(); $rb = ['valid'=>'success','due'=>'warning','expired'=>'danger','n/a'=>'default']; @endphp
@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">{{ $v->title }}</h3>
        <div class="box-tools"><span class="label label-{{ $sb[$v->status] ?? 'default' }}">{{ str_replace('_',' ',$v->status) }}</span></div>
      </div>
      <div class="box-body no-padding"><table class="table">
        <tr><th style="width:35%;">Reference</th><td>{{ $v->reference }}</td></tr>
        <tr><th>Type</th><td>{{ $types[$v->type] ?? $v->type }}</td></tr>
        <tr><th>Subject</th><td>{{ $v->subject ?: ($v->asset->name ?? '—') }}</td></tr>
        <tr><th>Protocol no.</th><td>{{ $v->protocol_no ?: '—' }}</td></tr>
        <tr><th>Performed</th><td>{{ $v->performed_date?->format('d M Y') ?: '—' }} @if($v->performed_by) by {{ $v->performed_by }}@endif</td></tr>
        <tr><th>Result</th><td>@if($v->result)<span class="label label-{{ $v->result==='pass'?'success':($v->result==='fail'?'danger':'warning') }}">{{ $v->result }}</span>@else — @endif</td></tr>
        <tr><th>Valid until</th><td>{{ $v->valid_until?->format('d M Y') ?: '—' }} @if($rs!=='n/a')<span class="label label-{{ $rb[$rs] ?? 'default' }}">{{ $rs }}</span>@endif</td></tr>
        <tr><th>Description</th><td>{{ $v->description ?: '—' }}</td></tr>
        <tr><th>Notes</th><td>{{ $v->notes ?: '—' }}</td></tr>
      </table></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="box box-default">
      <div class="box-header with-border"><h3 class="box-title">Approval</h3></div>
      <div class="box-body">
        <p class="text-muted">GMP sign-off for this validation record. Approval is written to the immutable audit trail.</p>
        <form method="post" action="/validations/{{ $v->id }}/transition">
          @csrf
          <div class="form-group"><select class="form-control" name="to">
            <option value="in_progress" @selected($v->status==='in_progress')>In progress</option>
            <option value="approved" @selected($v->status==='approved')>Approved</option>
            <option value="rejected" @selected($v->status==='rejected')>Rejected</option>
            <option value="expired" @selected($v->status==='expired')>Expired</option>
          </select></div>
          <button class="btn btn-primary btn-sm"><i class="fa fa-gavel"></i> Update status</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
