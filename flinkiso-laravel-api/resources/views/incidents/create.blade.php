@extends('layout')
@section('title', 'New incident')
@section('page_title', 'Incidents')
@section('page_sub', 'new incident')
@section('menu_incidents', 'active')
@section('breadcrumb')<li><a href="/incidents">Incidents</a></li><li class="active">New</li>@endsection
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">Log an incident / non-conformity</h3></div>
      <form method="post" action="/incidents">
        @csrf
        <div class="box-body">
          <div class="form-group">
            <label>Title *</label>
            <input class="form-control" name="title" value="{{ old('title') }}" placeholder="Short description of the issue">
          </div>
          <div class="row">
            <div class="col-sm-4 form-group">
              <label>Type *</label>
              <select class="form-control" name="type">
                @foreach(\App\Models\Qms\Incident::TYPES as $t)<option value="{{ $t }}" @selected(old('type')===$t)>{{ ucwords(str_replace('_',' ',$t)) }}</option>@endforeach
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>Severity *</label>
              <select class="form-control" name="severity">
                @foreach(\App\Models\Qms\Incident::SEVERITIES as $s)<option value="{{ $s }}" @selected(old('severity')===$s)>{{ ucfirst($s) }}</option>@endforeach
              </select>
            </div>
            <div class="col-sm-4 form-group">
              <label>Source</label>
              <input class="form-control" name="source" value="{{ old('source') }}" placeholder="audit / customer / ccp ...">
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group"><label>Root cause (if known)</label><textarea class="form-control" name="root_cause" rows="2">{{ old('root_cause') }}</textarea></div>
            <div class="col-sm-6 form-group"><label>Immediate containment action</label><textarea class="form-control" name="containment_action" rows="2">{{ old('containment_action') }}</textarea></div>
          </div>
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Assign to</label>
              <select class="form-control" name="assigned_to">
                <option value="">(unassigned)</option>
                @foreach($users as $u)<option value="{{ $u->id }}">{{ $u->name ?: $u->username }}</option>@endforeach
              </select>
            </div>
            <div class="col-sm-6 form-group"><label>Due date</label><input type="date" class="form-control" name="due_date" value="{{ old('due_date') }}"></div>
          </div>

          {{-- ISO overlay: standard-specific fields (M2.2) --}}
          @php $overlays = config('iso_overlays'); @endphp
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>ISO standard (overlay)</label>
              <select class="form-control" id="iso_standard" name="iso_standard">
                <option value="">(none)</option>
                @foreach($overlays as $std => $cfg)<option value="{{ $std }}">{{ $cfg['label'] }}</option>@endforeach
              </select>
            </div>
          </div>
          @foreach($overlays as $std => $cfg)
          <div class="iso-overlay-group" data-std="{{ $std }}" style="display:none;">
            <div class="box box-solid" style="box-shadow:none;border:1px dashed #d2d6de;">
              <div class="box-header"><h4 class="box-title" style="font-size:14px;">{{ $cfg['label'] }} fields</h4></div>
              <div class="box-body"><div class="row">
                @foreach($cfg['fields'] as $key => $f)
                <div class="col-sm-4 form-group">
                  <label>{{ $f['label'] }}</label>
                  @if(($f['type'] ?? 'text') === 'select')
                    <select class="form-control" name="overlay[{{ $key }}]"><option value="">—</option>@foreach($f['options'] as $opt)<option value="{{ $opt }}">{{ $opt }}</option>@endforeach</select>
                  @else
                    <input type="{{ $f['type']==='number'?'number':'text' }}" class="form-control" name="overlay[{{ $key }}]">
                  @endif
                </div>
                @endforeach
              </div></div>
            </div>
          </div>
          @endforeach
        </div>
        @section('scripts')
        <script>
        (function(){
          var sel=document.getElementById('iso_standard');
          function sync(){var v=sel.value;document.querySelectorAll('.iso-overlay-group').forEach(function(g){g.style.display=(g.getAttribute('data-std')===v)?'block':'none';});}
          if(sel){sel.addEventListener('change',sync);sync();}
        })();
        </script>
        @append
        <div class="box-footer">
          <button class="btn btn-primary"><i class="fa fa-check"></i> Create incident</button>
          <a class="btn btn-default" href="/incidents">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
