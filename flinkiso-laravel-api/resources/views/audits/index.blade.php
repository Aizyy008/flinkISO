@extends('layout')
@section('title', 'Audit Management')
@section('page_title', 'Audit Management')
@section('page_sub', 'programs, schedule, findings')
@section('menu_audits', 'active')
@section('breadcrumb')<li class="active">Audits</li>@endsection
@php
  $badge = ['scheduled'=>'label-default','in_progress'=>'label-warning','completed'=>'label-info','closed'=>'label-success'];
@endphp
@section('content')
<div class="row"><div class="col-md-12">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Audits</h3>
      <div class="box-tools"><a class="btn btn-primary btn-sm" href="/audits/create"><i class="fa fa-plus"></i> Schedule audit</a></div>
    </div>
    <div class="box-body no-padding">
      <table class="table table-hover">
        <thead><tr><th>Reference</th><th>Title</th><th>Type</th><th>Standard</th><th>Planned</th><th>Findings</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($audits as $a)
        <tr>
          <td><a href="/audits/{{ $a->id }}"><b>{{ $a->reference }}</b></a></td>
          <td>{{ $a->title }}</td>
          <td>{{ ucfirst($a->audit_type) }}</td>
          <td>{{ $a->standard ?: '—' }}</td>
          <td class="text-muted">{{ $a->planned_date?->format('d M Y') ?? '—' }}</td>
          <td>{{ $a->findings_count }}</td>
          <td><span class="label {{ $badge[$a->status] ?? 'label-default' }}">{{ str_replace('_',' ',$a->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" class="text-muted" style="padding:15px;">No audits scheduled yet.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    @if($audits->hasPages())<div class="box-footer">{{ $audits->links() }}</div>@endif
  </div>
</div></div>

<div class="row"><div class="col-md-12">
  <div class="box box-default">
    <div class="box-header with-border">
      <h3 class="box-title">Annual audit programs</h3>
      <div class="box-tools"><a class="btn btn-default btn-sm" href="/audits/create#program"><i class="fa fa-plus"></i> New program</a></div>
    </div>
    <div class="box-body no-padding">
      <table class="table">
        <thead><tr><th>Reference</th><th>Year</th><th>Title</th><th>Audits</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($programs as $p)
        <tr><td>{{ $p->reference }}</td><td>{{ $p->year }}</td><td>{{ $p->title }}</td><td>{{ $p->audits_count }}</td>
          <td><span class="label label-info">{{ $p->status }}</span></td></tr>
        @empty
        <tr><td colspan="5" class="text-muted" style="padding:15px;">No program yet — create one on the “Schedule audit” page.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div></div>
@endsection
