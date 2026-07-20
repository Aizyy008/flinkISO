@extends('layout')
@section('title', $form ? 'Edit form' : 'New form')
@section('page_title', 'Form Builder')
@section('page_sub', $form ? $form->name : 'design a new form')
@section('menu_formbuilder', 'active')
@section('breadcrumb')<li><a href="/form-builder">Form Builder</a></li><li class="active">{{ $form ? 'Edit' : 'New' }}</li>@endsection
@php
  $types = \App\Http\Controllers\Web\FormBuilderController::FIELD_TYPES;
@endphp
@section('content')
<form method="post" action="{{ $form ? '/form-builder/'.$form->id : '/form-builder' }}" id="formBuilderForm" enctype="multipart/form-data">
  @csrf
  @if($form)@method('put')@endif
  <input type="hidden" name="fields_json" id="fields_json">

  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">Form settings</h3>
      <div class="box-tools"><button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save form</button></div>
    </div>
    <div class="box-body">
      <div class="row">
        <div class="col-sm-5 form-group"><label>Form name *</label><input class="form-control" name="name" value="{{ old('name', $form->name ?? '') }}" required></div>
        <div class="col-sm-3 form-group"><label>Category</label><input class="form-control" name="category" value="{{ old('category', $form->category ?? '') }}" placeholder="e.g. Inspection"></div>
        <div class="col-sm-2 form-group"><label>Status *</label>
          <select class="form-control" name="status">@foreach(['draft','active','archived'] as $s)<option value="{{ $s }}" @selected(($form->status ?? 'draft')===$s)>{{ ucfirst($s) }}</option>@endforeach</select>
        </div>
        <div class="col-sm-2 form-group"><label>On submit → record</label>
          <select class="form-control" name="feeds_record_type"><option value="">(none)</option>
            <option value="incident" @selected(($form->feeds_record_type ?? '')==='incident')>Incident / NC</option>
            <option value="risk" @selected(($form->feeds_record_type ?? '')==='risk')>Risk</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-8 form-group"><label>Description</label><input class="form-control" name="description" value="{{ old('description', $form->description ?? '') }}"></div>
        <div class="col-sm-4 form-group"><label>Trigger workflow event <small class="text-muted">(optional)</small></label><input class="form-control" name="trigger_event" value="{{ old('trigger_event', $form->trigger_event ?? '') }}" placeholder="e.g. form.submitted"></div>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3">
      <div class="box box-default">
        <div class="box-header with-border"><h3 class="box-title">Field palette</h3></div>
        <div class="box-body" style="padding:8px;">
          <p class="text-muted qms-sign">Drag onto the canvas, or click to add.</p>
          @foreach($types as $key => $label)
          <div class="btn btn-default btn-block js-palette" draggable="true" data-type="{{ $key }}" style="text-align:left;margin-bottom:5px;cursor:grab;">
            <i class="fa fa-plus-square-o"></i> {{ $label }}
          </div>
          @endforeach
        </div>
      </div>
    </div>
    <div class="col-md-9">
      <div class="box box-default">
        <div class="box-header with-border"><h3 class="box-title">Form canvas</h3></div>
        <div class="box-body" id="canvas" style="min-height:300px;">
          <div id="emptyHint" class="text-muted" style="text-align:center;padding:40px;border:2px dashed #ddd;border-radius:4px;">
            Drag fields here, or click a field in the palette to add it.
          </div>
          <div id="fieldList"></div>
        </div>
      </div>
    </div>
  </div>
</form>
@endsection

@section('scripts')
<script>
(function () {
  var TYPES = @json($types);
  var CHOICE = ['dropdown','multiselect','radio'];
  var fields = @json(collect($fields)->map(function ($f) {
    return [
      'field_key' => $f->field_key, 'label' => $f->label, 'field_type' => $f->field_type,
      'options' => $f->options ?: [], 'required' => (bool) $f->required, 'placeholder' => $f->placeholder,
      'help_text' => $f->help_text, 'cond_field' => $f->cond_field, 'cond_op' => $f->cond_op, 'cond_value' => $f->cond_value,
    ];
  })->values());

  var list = document.getElementById('fieldList');
  var hint = document.getElementById('emptyHint');
  var form = document.getElementById('formBuilderForm');

  function keyFor(label, i) { return (label || 'field').toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') + '_' + i; }

  function addField(type, at) {
    var f = { field_key: '', label: TYPES[type] || 'Field', field_type: type, options: CHOICE.indexOf(type) > -1 ? ['Option 1','Option 2'] : [], required: false, placeholder: '', help_text: '', cond_field: '', cond_op: '', cond_value: '' };
    if (typeof at === 'number') fields.splice(at, 0, f); else fields.push(f);
    render();
  }

  function render() {
    hint.style.display = fields.length ? 'none' : 'block';
    list.innerHTML = '';
    fields.forEach(function (f, i) {
      var isChoice = CHOICE.indexOf(f.field_type) > -1;
      var others = fields.filter(function (x, j) { return j !== i && x.field_type !== 'section'; });
      var row = document.createElement('div');
      row.className = 'box box-widget';
      row.setAttribute('draggable', 'true');
      row.dataset.i = i;
      row.style.cssText = 'border:1px solid #ddd;border-radius:4px;margin-bottom:8px;padding:10px;background:#fbfbfb;';
      row.innerHTML =
        '<div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">' +
          '<i class="fa fa-arrows move" style="cursor:move;color:#999;"></i>' +
          '<span class="label label-info" style="text-transform:capitalize;">' + f.field_type + '</span>' +
          '<input class="form-control input-sm fld-label" style="flex:1;" placeholder="Field label" value="' + esc(f.label) + '">' +
          '<label class="qms-sign" style="margin:0;white-space:nowrap;"><input type="checkbox" class="fld-required"' + (f.required ? ' checked' : '') + '> required</label>' +
          '<button type="button" class="btn btn-xs btn-default fld-up"><i class="fa fa-arrow-up"></i></button>' +
          '<button type="button" class="btn btn-xs btn-default fld-down"><i class="fa fa-arrow-down"></i></button>' +
          '<button type="button" class="btn btn-xs btn-danger fld-del"><i class="fa fa-trash"></i></button>' +
        '</div>' +
        (f.field_type === 'section' ? '' :
        '<div class="row" style="margin:0;">' +
          '<div class="col-sm-4" style="padding-left:0;"><input class="form-control input-sm fld-ph" placeholder="Placeholder / hint" value="' + esc(f.placeholder || '') + '"></div>' +
          (isChoice ? '<div class="col-sm-8" style="padding-right:0;"><input class="form-control input-sm fld-opts" placeholder="Options, comma-separated" value="' + esc((f.options || []).join(', ')) + '"></div>' :
           (f.field_type === 'repeatable' ? '<div class="col-sm-8" style="padding-right:0;"><input class="form-control input-sm fld-opts" placeholder="Sub-fields, comma-separated (e.g. Item, Qty)" value="' + esc((f.options || []).join(', ')) + '"></div>' : '')) +
        '</div>' +
        '<div class="row" style="margin:4px 0 0;"><div class="col-sm-12" style="padding:0;">' +
          '<span class="qms-sign text-muted">Show only when </span>' +
          '<select class="input-sm fld-cf"><option value="">(always)</option>' + others.map(function (o) { return '<option value="' + esc(o.field_key || keyFor(o.label, fields.indexOf(o))) + '"' + (f.cond_field === (o.field_key || keyFor(o.label, fields.indexOf(o))) ? ' selected' : '') + '>' + esc(o.label) + '</option>'; }).join('') + '</select> ' +
          '<select class="input-sm fld-co"><option value="=" ' + (f.cond_op === '=' ? 'selected' : '') + '>=</option><option value="!="' + (f.cond_op === '!=' ? ' selected' : '') + '>≠</option></select> ' +
          '<input class="input-sm fld-cv" placeholder="value" value="' + esc(f.cond_value || '') + '" style="width:140px;">' +
        '</div></div>');
      list.appendChild(row);
    });
    bind();
  }

  function bind() {
    list.querySelectorAll('.box-widget').forEach(function (row) {
      var i = +row.dataset.i, f = fields[i];
      row.querySelector('.fld-label').addEventListener('input', function () { f.label = this.value; });
      row.querySelector('.fld-required').addEventListener('change', function () { f.required = this.checked; });
      var ph = row.querySelector('.fld-ph'); if (ph) ph.addEventListener('input', function () { f.placeholder = this.value; });
      var op = row.querySelector('.fld-opts'); if (op) op.addEventListener('input', function () { f.options = this.value.split(',').map(function (s) { return s.trim(); }).filter(Boolean); });
      var cf = row.querySelector('.fld-cf'); if (cf) cf.addEventListener('change', function () { f.cond_field = this.value; });
      var co = row.querySelector('.fld-co'); if (co) co.addEventListener('change', function () { f.cond_op = this.value; });
      var cv = row.querySelector('.fld-cv'); if (cv) cv.addEventListener('input', function () { f.cond_value = this.value; });
      row.querySelector('.fld-del').addEventListener('click', function () { fields.splice(i, 1); render(); });
      row.querySelector('.fld-up').addEventListener('click', function () { if (i > 0) { fields.splice(i - 1, 0, fields.splice(i, 1)[0]); render(); } });
      row.querySelector('.fld-down').addEventListener('click', function () { if (i < fields.length - 1) { fields.splice(i + 1, 0, fields.splice(i, 1)[0]); render(); } });
      // drag reorder
      row.addEventListener('dragstart', function (e) { e.dataTransfer.setData('reorder', i); });
      row.addEventListener('dragover', function (e) { e.preventDefault(); });
      row.addEventListener('drop', function (e) {
        e.preventDefault(); e.stopPropagation();
        var from = e.dataTransfer.getData('reorder');
        if (from !== '') { var mv = fields.splice(+from, 1)[0]; fields.splice(i, 0, mv); render(); }
      });
    });
  }

  function esc(s) { return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;'); }

  // Palette: click + drag to canvas.
  document.querySelectorAll('.js-palette').forEach(function (p) {
    p.addEventListener('click', function () { addField(p.dataset.type); });
    p.addEventListener('dragstart', function (e) { e.dataTransfer.setData('newtype', p.dataset.type); });
  });
  var canvas = document.getElementById('canvas');
  canvas.addEventListener('dragover', function (e) { e.preventDefault(); });
  canvas.addEventListener('drop', function (e) {
    var t = e.dataTransfer.getData('newtype');
    if (t) { e.preventDefault(); addField(t); }
  });

  // Serialize on submit: assign stable keys.
  form.addEventListener('submit', function () {
    fields.forEach(function (f, i) { if (!f.field_key) f.field_key = keyFor(f.label, i); });
    document.getElementById('fields_json').value = JSON.stringify(fields);
  });

  render();
})();
</script>
@endsection
