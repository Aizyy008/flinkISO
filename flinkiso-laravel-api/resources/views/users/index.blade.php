@extends('layout')
@section('title', 'Users & Roles')
@section('page_title', 'Users &amp; Roles')
@section('page_sub', 'document-workflow roles')
@section('menu_users', 'active')
@section('breadcrumb')<li class="active">Users &amp; Roles</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Document-workflow roles</h3>
    <div class="box-tools"><span class="text-muted small">Creator → Reviewer → Approver → Publisher</span></div>
  </div>
  <div class="box-body">
    <p class="text-muted">Assign each user the roles they can perform in the document lifecycle. These roles drive the Reviewer / Approver / Publisher pickers on documents and gate who can review, approve and publish. Login accounts are managed in FlinkISO; roles are stored here.</p>
    <div class="table-responsive">
    <table class="table table-hover">
      <thead><tr><th>User</th><th class="text-center">Creator</th><th class="text-center">Reviewer</th><th class="text-center">Approver</th><th class="text-center">Publisher</th><th></th></tr></thead>
      <tbody>
      @foreach($users as $u)
      <form method="post" action="/users/{{ $u->id }}/roles">
      @csrf
      <tr>
        <td><b>{{ $u->name ?: $u->username }}</b><br><span class="text-muted small">{{ $u->username }}</span></td>
        <td class="text-center"><input type="checkbox" name="creator" value="1" @checked($u->roles['creator'])></td>
        <td class="text-center"><input type="checkbox" name="reviewer" value="1" @checked($u->roles['reviewer'])></td>
        <td class="text-center"><input type="checkbox" name="approver" value="1" @checked($u->roles['approver'])></td>
        <td class="text-center"><input type="checkbox" name="publisher" value="1" @checked($u->roles['publisher'])></td>
        <td class="text-right"><button class="btn btn-xs btn-primary">Save</button></td>
      </tr>
      </form>
      @endforeach
      </tbody>
    </table>
    </div>
  </div>
</div>
@endsection
