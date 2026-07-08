@extends('layout')
@section('title', 'Document Control')
@section('page_title', 'Document Control')
@section('page_sub', 'ISO controlled documents')
@section('menu_documents', 'active')
@section('breadcrumb')<li class="active">Documents</li>@endsection
@section('content')
<div class="row">
  <div class="col-xs-12">
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Controlled Documents</h3>
        <div class="box-tools">
          <a class="btn btn-primary btn-sm" href="/documents/create"><i class="fa fa-plus"></i> New document</a>
        </div>
      </div>
      <div class="box-body">
        @if($documents->count())
        <div class="table-responsive">
          <table class="table table-hover table-striped">
            <thead><tr><th>Number</th><th>Title</th><th>Category</th><th>Version</th><th>Status</th><th class="text-right">Action</th></tr></thead>
            <tbody>
            @foreach($documents as $d)
            <tr>
              <td><b>{{ $d->doc_number }}</b></td>
              <td>{{ $d->title }}</td>
              <td>{{ $d->category }}</td>
              <td>v{{ $d->current_version }}</td>
              <td>@include('documents.status', ['status' => $d->status])</td>
              <td class="text-right"><a class="btn btn-default btn-xs" href="/documents/{{ $d->id }}"><i class="fa fa-folder-open"></i> Open</a></td>
            </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        {{ $documents->links() }}
        @else
        <p class="text-muted">No documents yet. Click <b>New document</b> to create the first one.</p>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
