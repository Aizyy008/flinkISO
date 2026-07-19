@extends('layout')
@section('title', 'Form Builder')
@section('page_title', 'Form Builder')
@section('page_sub', 'drag & drop forms')
@section('menu_formbuilder', 'active')
@section('breadcrumb')<li class="active">Form Builder</li>@endsection
@section('content')
<div class="box box-primary">
  <div class="box-header with-border"><h3 class="box-title">Forms</h3>
    <div class="box-tools"><a class="btn btn-primary btn-sm" href="/form-builder/create"><i class="fa fa-plus"></i> New form</a></div>
  </div>
  <div class="box-body no-padding">
    <table class="table table-hover">
      <thead><tr><th>Reference</th><th>Name</th><th>Category</th><th>Fields feed</th><th>Submissions</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
      <tbody>
      @forelse($forms as $f)
      <tr>
        <td><b>{{ $f->reference }}</b></td>
        <td>{{ $f->name }}</td>
        <td>{{ $f->category ?: '—' }}</td>
        <td>{{ $f->feeds_record_type ? ucfirst($f->feeds_record_type) : '—' }}</td>
        <td>{{ $f->submissions_count }}</td>
        <td><span class="label {{ $f->status==='active'?'label-success':($f->status==='archived'?'label-default':'label-warning') }}">{{ $f->status }}</span></td>
        <td class="text-right">
          @if($f->status==='active')<a class="btn btn-xs btn-success" href="/form-builder/{{ $f->id }}/fill"><i class="fa fa-pencil-square-o"></i> Fill</a>@endif
          <a class="btn btn-xs btn-default" href="/form-builder/{{ $f->id }}/submissions">Submissions</a>
          <a class="btn btn-xs btn-default" href="/form-builder/{{ $f->id }}/edit">Edit</a>
          <form method="post" action="/form-builder/{{ $f->id }}" style="display:inline;" onsubmit="return confirm('Delete form {{ $f->reference }}? This removes its fields and submissions.');">@csrf @method('delete')<button class="btn btn-xs btn-danger">Delete</button></form>
        </td>
      </tr>
      @empty
      <tr><td colspan="7" class="text-muted" style="padding:15px;">No forms yet. Click “New form” to build one.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  @if($forms->hasPages())<div class="box-footer">{{ $forms->links() }}</div>@endif
</div>
@endsection
