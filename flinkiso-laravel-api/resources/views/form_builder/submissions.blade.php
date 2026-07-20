@extends('layout')
@section('title', 'Submissions')
@section('page_title', 'Form Builder')
@section('page_sub', $form->name.' — submissions')
@section('menu_formbuilder', 'active')
@section('breadcrumb')<li><a href="/form-builder">Form Builder</a></li><li><a href="/form-builder/{{ $form->id }}/edit">{{ $form->name }}</a></li><li class="active">Submissions</li>@endsection
@php $cols = $form->fields->where('field_type','!=','section')->take(5); @endphp
@section('content')
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">{{ $form->name }} — {{ $submissions->total() }} submission(s)</h3>
    @if($form->status==='active')<div class="box-tools"><a class="btn btn-success btn-sm" href="/form-builder/{{ $form->id }}/fill"><i class="fa fa-plus"></i> New submission</a></div>@endif
  </div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Reference</th>@foreach($cols as $c)<th>{{ $c->label }}</th>@endforeach<th>Linked record</th><th>Submitted</th></tr></thead>
      <tbody>
      @forelse($submissions as $s)
      <tr>
        <td>{{ $s->reference }}</td>
        @foreach($cols as $c)
          @php $v = $s->data[$c->field_key] ?? null; @endphp
          <td>@if($c->field_type==='signature' && $v)<img src="{{ $v }}" style="height:28px;">@elseif(is_array($v)){{ implode(', ', $v) }}@else{{ \Illuminate\Support\Str::limit((string)$v, 40) }}@endif</td>
        @endforeach
        <td>@if($s->linked_record_id)<span class="label label-info">{{ str_replace('qms_','',$s->linked_record_type) }}</span>@else—@endif</td>
        <td class="text-muted qms-sign">{{ $s->created_at?->format('d M Y, g:i A') }}</td>
      </tr>
      @empty
      <tr><td colspan="{{ 4 + $cols->count() }}" class="text-muted" style="padding:15px;">No submissions yet.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($submissions->hasPages())<div class="box-footer">{{ $submissions->links() }}</div>@endif
</div>
@endsection
