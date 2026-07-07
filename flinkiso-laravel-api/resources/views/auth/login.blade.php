@extends('layout')
@section('title', 'Sign in')
@section('content')
<div class="login-box" style="margin:7% auto;">
  <div class="login-logo"><b>FlinkISO</b> QMS</div>
  <div class="login-box-body">
    <p class="login-box-msg">Sign in to the Quality Management System</p>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <form method="post" action="/login">
      @csrf
      <div class="form-group has-feedback">
        <input class="form-control" name="username" value="{{ old('username') }}" placeholder="Username" autofocus>
        <span class="glyphicon glyphicon-user form-control-feedback"></span>
      </div>
      <div class="form-group has-feedback">
        <input class="form-control" type="password" name="password" placeholder="Password">
        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
      </div>
      <div class="row">
        <div class="col-xs-8"><p class="text-muted" style="font-size:12px;">Same account as FlinkISO</p></div>
        <div class="col-xs-4"><button type="submit" class="btn btn-primary btn-block btn-flat">Sign In</button></div>
      </div>
    </form>
  </div>
</div>
@endsection
