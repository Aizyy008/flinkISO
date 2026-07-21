@extends('layout')
@section('title', 'New validation')
@section('page_title', 'GMP / Validation logs')
@section('page_sub', 'new record')
@section('menu_validations', 'active')
@section('breadcrumb')<li><a href="/validations">Validation</a></li><li class="active">New</li>@endsection
@php $types = \App\Models\Qms\Validation::TYPES; @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">New validation record</h3></div>
  <form method="post" action="/validations">
    @csrf
    <div class="box-body">
      <div class="row">
        <div class="col-sm-4 form-group"><label>Type *</label><select class="form-control" name="type">@foreach($types as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select></div>
        <div class="col-sm-8 form-group"><label>Title *</label><input class="form-control" name="title" value="{{ old('title') }}" required placeholder="e.g. Pasteurizer OQ 2026"></div>
      </div>
      <div class="row">
        <div class="col-sm-6 form-group"><label>Subject</label><input class="form-control" name="subject" placeholder="what is being validated (equipment / process)"></div>
        <div class="col-sm-6 form-group"><label>Linked asset</label><select class="form-control" name="asset_id"><option value="">(none)</option>@foreach($assets as $a)<option value="{{ $a->id }}">{{ $a->reference }} — {{ $a->name }}</option>@endforeach</select></div>
      </div>
      <div class="row">
        <div class="col-sm-4 form-group"><label>Protocol no.</label><input class="form-control" name="protocol_no"></div>
        <div class="col-sm-4 form-group"><label>Performed date</label><input type="date" class="form-control" name="performed_date"></div>
        <div class="col-sm-4 form-group"><label>Performed by</label><input class="form-control" name="performed_by"></div>
      </div>
      <div class="row">
        <div class="col-sm-4 form-group"><label>Result</label><select class="form-control" name="result"><option value="">(pending)</option><option value="pass">Pass</option><option value="conditional">Conditional</option><option value="fail">Fail</option></select></div>
        <div class="col-sm-4 form-group"><label>Valid until (revalidation)</label><input type="date" class="form-control" name="valid_until"></div>
      </div>
      <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="2"></textarea></div>
      <div class="form-group"><label>Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
    </div>
    <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-check"></i> Create</button> <a class="btn btn-default" href="/validations">Cancel</a></div>
  </form>
</div>
@endsection
