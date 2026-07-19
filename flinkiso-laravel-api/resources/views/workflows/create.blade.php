@extends('layout')
@section('title', 'New workflow rule')
@section('page_title', 'Workflow rules')
@section('page_sub', 'new rule')
@section('menu_workflows', 'active')
@section('breadcrumb')<li><a href="/workflows">Workflows</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">New rule</h3></div>
      <form method="post" action="/workflows">
        @csrf
        <div class="box-body">
          <div class="row">
            <div class="col-sm-6 form-group"><label>Rule name *</label><input class="form-control" name="name" value="{{ old('name') }}" placeholder="e.g. Critical incident auto-CAPA"></div>
            <div class="col-sm-6 form-group"><label>Trigger event *</label>
              <select class="form-control" name="trigger_event">
                <option value="incident.created">incident.created</option>
                <option value="risk.assessed">risk.assessed</option>
              </select>
            </div>
          </div>

          <h4>Condition <small class="text-muted">(optional)</small></h4>
          <div class="row">
            <div class="col-sm-4 form-group"><label>Field</label><input class="form-control" name="cond_field" placeholder="severity / risk_level / type"></div>
            <div class="col-sm-3 form-group"><label>Operator</label>
              <select class="form-control" name="cond_op"><option>=</option><option>!=</option><option>&gt;</option><option>&gt;=</option><option>&lt;</option><option>&lt;=</option><option>in</option></select>
            </div>
            <div class="col-sm-5 form-group"><label>Value</label><input class="form-control" name="cond_value" placeholder="critical"></div>
          </div>

          <h4>Actions *</h4>
          <div class="checkbox"><label><input type="checkbox" name="action_create_capa" value="1"> Create a CAPA</label></div>
          <div class="form-group" style="max-width:500px;margin-left:20px;"><input class="form-control" name="capa_title" placeholder="CAPA title (e.g. CAPA for critical incident)"></div>
          <div class="checkbox"><label><input type="checkbox" name="action_notify" value="1"> Send a notification</label></div>
          <div class="row" style="margin-left:10px;">
            <div class="col-sm-6 form-group"><input class="form-control" name="notify_title" placeholder="Notification title"></div>
            <div class="col-sm-6"><div class="checkbox"><label><input type="checkbox" name="notify_email" value="1"> also send email</label></div></div>
          </div>
        </div>
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Create rule</button>
          <a class="btn btn-default" href="/workflows">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
