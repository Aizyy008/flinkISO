@extends('layout')
@section('title', 'New training')
@section('page_title', 'Training &amp; Competency')
@section('page_sub', 'new course')
@section('menu_training', 'active')
@section('breadcrumb')<li><a href="/training">Training</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row"><div class="col-md-8">
  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">New training course</h3></div>
    <form method="post" action="/training">
      @csrf
      <div class="box-body">
        <div class="form-group"><label>Title *</label><input class="form-control" name="title" value="{{ old('title') }}" placeholder="e.g. Food Safety Induction"></div>
        <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea></div>
        <div class="row">
          <div class="col-sm-4 form-group"><label>Category</label><input class="form-control" name="category" value="{{ old('category') }}" placeholder="Food Safety / Safety ..."></div>
          <div class="col-sm-4 form-group"><label>Retraining cycle (months)</label><input type="number" min="1" class="form-control" name="validity_months" value="{{ old('validity_months') }}" placeholder="e.g. 12"></div>
          <div class="col-sm-4 form-group"><label>&nbsp;</label><div class="checkbox"><label><input type="checkbox" name="mandatory" value="1"> Mandatory</label></div></div>
        </div>
        <p class="help-block">Leave retraining empty for a one-off training with no expiry.</p>
      </div>
      <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-check"></i> Create</button> <a class="btn btn-default" href="/training">Cancel</a></div>
    </form>
  </div>
</div></div>
@endsection
