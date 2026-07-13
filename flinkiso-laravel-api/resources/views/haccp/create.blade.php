@extends('layout')
@section('title', 'New HACCP plan')
@section('page_title', 'HACCP')
@section('page_sub', 'new plan')
@section('menu_haccp', 'active')
@section('breadcrumb')<li><a href="/haccp">HACCP</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-9">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">New HACCP plan</h3></div>
      <form method="post" action="/haccp">
        @csrf
        <div class="box-body">
          <div class="form-group"><label>Product *</label><input class="form-control" name="product" value="{{ old('product') }}" placeholder="e.g. Fresh Milk 3.5%"></div>
          <div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="2">{{ old('description') }}</textarea></div>
          <div class="form-group"><label>HACCP team</label><input class="form-control" name="team" value="{{ old('team') }}" placeholder="team members"></div>
        </div>
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Create plan</button>
          <a class="btn btn-default" href="/haccp">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
