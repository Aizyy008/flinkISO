@extends('layout')
@section('title', $form->name)
@section('page_title', 'Form Builder')
@section('page_sub', $form->name)
@section('menu_formbuilder', 'active')
@section('breadcrumb')<li><a href="/form-builder">Form Builder</a></li><li class="active">{{ $form->name }}</li>@endsection
@section('content')
<div class="row"><div class="col-md-9">
  <div class="box box-primary">
    <div class="box-header with-border"><h3 class="box-title">{{ $form->name }}</h3>
      <div class="box-tools"><a class="btn btn-default btn-sm" href="/form-builder/{{ $form->id }}/submissions">View submissions</a></div>
    </div>
    <form method="post" action="/form-builder/{{ $form->id }}/submit" enctype="multipart/form-data" id="fillForm">
      @csrf
      <div class="box-body">
        @if($form->description)<p class="text-muted">{{ $form->description }}</p>@endif
        @foreach($form->fields as $f)
          @php $name = 'f_'.$f->field_key; @endphp
          <div class="form-group js-field" data-key="{{ $f->field_key }}"
               @if($f->cond_field)data-cond-field="{{ $f->cond_field }}" data-cond-op="{{ $f->cond_op ?: '=' }}" data-cond-value="{{ $f->cond_value }}"@endif>
            @if($f->field_type === 'section')
              <h4 style="border-bottom:1px solid #eee;padding-bottom:6px;margin-top:18px;">{{ $f->label }}</h4>
            @else
              <label>{{ $f->label }} @if($f->required)*@endif</label>
              @if($f->help_text)<small class="text-muted"> — {{ $f->help_text }}</small>@endif
              @switch($f->field_type)
                @case('textarea')
                  <textarea class="form-control" name="{{ $name }}" rows="3" placeholder="{{ $f->placeholder }}">{{ old($name) }}</textarea> @break
                @case('number')
                  <input type="number" step="any" class="form-control" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $f->placeholder }}"> @break
                @case('date')
                  <input type="date" class="form-control" name="{{ $name }}" value="{{ old($name) }}"> @break
                @case('datetime')
                  <input type="datetime-local" class="form-control" name="{{ $name }}" value="{{ old($name) }}"> @break
                @case('dropdown')
                  <select class="form-control" name="{{ $name }}"><option value="">— select —</option>@foreach(($f->options ?: []) as $o)<option>{{ $o }}</option>@endforeach</select> @break
                @case('multiselect')
                  <select class="form-control" name="{{ $name }}[]" multiple>@foreach(($f->options ?: []) as $o)<option>{{ $o }}</option>@endforeach</select> @break
                @case('radio')
                  @foreach(($f->options ?: []) as $o)<div class="radio"><label><input type="radio" name="{{ $name }}" value="{{ $o }}"> {{ $o }}</label></div>@endforeach @break
                @case('checkbox')
                  <div class="checkbox"><label><input type="checkbox" name="{{ $name }}" value="1"> {{ $f->placeholder ?: 'Yes' }}</label></div> @break
                @case('file')
                  <input type="file" class="form-control" name="{{ $name }}"> @break
                @case('signature')
                  <div>
                    <canvas class="js-sig" data-target="sig_{{ $f->field_key }}" width="360" height="120" style="border:1px solid #ccc;border-radius:4px;touch-action:none;background:#fff;"></canvas>
                    <input type="hidden" name="{{ $name }}" id="sig_{{ $f->field_key }}">
                    <div><button type="button" class="btn btn-xs btn-default js-sig-clear" data-target="sig_{{ $f->field_key }}">Clear signature</button></div>
                  </div> @break
                @case('repeatable')
                  <div class="js-repeat" data-name="{{ $name }}" data-sub='@json($f->options ?: ["Item"])'>
                    <table class="table table-condensed"><thead><tr>@foreach(($f->options ?: ['Item']) as $sub)<th>{{ $sub }}</th>@endforeach<th></th></tr></thead><tbody class="rp-body"></tbody></table>
                    <button type="button" class="btn btn-xs btn-default js-repeat-add"><i class="fa fa-plus"></i> Add row</button>
                    <input type="hidden" name="{{ $name }}" class="rp-json">
                  </div> @break
                @default
                  <input class="form-control" name="{{ $name }}" value="{{ old($name) }}" placeholder="{{ $f->placeholder }}">
              @endswitch
            @endif
          </div>
        @endforeach
      </div>
      <div class="box-footer"><button class="btn btn-primary"><i class="fa fa-check"></i> Submit</button> <a class="btn btn-default" href="/form-builder">Cancel</a></div>
    </form>
  </div>
</div></div>
@endsection

@section('scripts')
<script>
(function () {
  // ---- Signature pads ----
  document.querySelectorAll('.js-sig').forEach(function (cv) {
    var ctx = cv.getContext('2d'), drawing = false, target = document.getElementById(cv.dataset.target);
    ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111';
    function pos(e) { var r = cv.getBoundingClientRect(); var t = e.touches ? e.touches[0] : e; return { x: t.clientX - r.left, y: t.clientY - r.top }; }
    function start(e) { drawing = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); e.preventDefault(); }
    function move(e) { if (!drawing) return; var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); target.value = cv.toDataURL('image/png'); e.preventDefault(); }
    function end() { drawing = false; }
    cv.addEventListener('mousedown', start); cv.addEventListener('mousemove', move); document.addEventListener('mouseup', end);
    cv.addEventListener('touchstart', start); cv.addEventListener('touchmove', move); cv.addEventListener('touchend', end);
  });
  document.querySelectorAll('.js-sig-clear').forEach(function (b) {
    b.addEventListener('click', function () {
      var cv = document.querySelector('.js-sig[data-target="' + b.dataset.target + '"]');
      cv.getContext('2d').clearRect(0, 0, cv.width, cv.height); document.getElementById(b.dataset.target).value = '';
    });
  });

  // ---- Repeatable groups ----
  document.querySelectorAll('.js-repeat').forEach(function (rep) {
    var sub = JSON.parse(rep.dataset.sub || '["Item"]'), body = rep.querySelector('.rp-body'), json = rep.querySelector('.rp-json');
    function sync() {
      var rows = [];
      body.querySelectorAll('tr').forEach(function (tr) {
        var o = {}; tr.querySelectorAll('input.rp-cell').forEach(function (inp) { o[inp.dataset.sub] = inp.value; }); rows.push(o);
      });
      json.value = JSON.stringify(rows);
    }
    function addRow() {
      var tr = document.createElement('tr');
      tr.innerHTML = sub.map(function (s) { return '<td><input class="form-control input-sm rp-cell" data-sub="' + s + '"></td>'; }).join('') +
        '<td><button type="button" class="btn btn-xs btn-danger rp-del"><i class="fa fa-times"></i></button></td>';
      body.appendChild(tr);
      tr.querySelector('.rp-del').addEventListener('click', function () { tr.remove(); sync(); });
      tr.querySelectorAll('.rp-cell').forEach(function (c) { c.addEventListener('input', sync); });
    }
    rep.querySelector('.js-repeat-add').addEventListener('click', addRow);
    addRow();
  });

  // ---- Conditional visibility ----
  var fields = document.querySelectorAll('.js-field[data-cond-field]');
  function val(key) {
    var el = document.querySelector('[name="f_' + key + '"]');
    if (!el) return '';
    if (el.type === 'checkbox') return el.checked ? el.value : '';
    return el.value;
  }
  function apply() {
    fields.forEach(function (f) {
      var cur = val(f.dataset.condField), want = f.dataset.condValue, op = f.dataset.condOp;
      var show = op === '!=' ? (cur != want) : (cur == want);
      f.style.display = show ? '' : 'none';
    });
  }
  document.getElementById('fillForm').addEventListener('input', apply);
  document.getElementById('fillForm').addEventListener('change', apply);
  apply();
})();
</script>
@endsection
