{{-- Shared evidence box. Expects: $relatedType, $relatedId, $evidence (collection), $redirect --}}
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-paperclip"></i> Evidence</h3></div>
  <div class="box-body">
    <form method="post" action="/evidence" enctype="multipart/form-data" class="row">
      @csrf
      <input type="hidden" name="related_type" value="{{ $relatedType }}">
      <input type="hidden" name="related_id" value="{{ $relatedId }}">
      <input type="hidden" name="redirect" value="{{ $redirect }}">
      <div class="col-sm-2 form-group">
        <label class="small">Type</label>
        <select class="form-control" name="evidence_type">
          <option value="file">File</option><option value="photo">Photo</option>
          <option value="measurement">Measurement</option><option value="record">Record</option>
          <option value="report">Report</option>
        </select>
      </div>
      <div class="col-sm-3 form-group"><label class="small">Title</label><input class="form-control" name="title" placeholder="e.g. Temperature log"></div>
      <div class="col-sm-3 form-group">
        <label class="small">File</label>
        <label class="btn btn-default btn-block text-left" style="font-weight:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin:0;">
          <i class="fa fa-folder-open text-yellow"></i> <span class="ev-file-name">Choose a file…</span>
          <input type="file" name="file" style="display:none" onchange="var s=this.closest('.form-group').querySelector('.ev-file-name'); s.textContent=(this.files[0]?this.files[0].name:'Choose a file…');">
        </label>
        <span class="help-block small" style="margin:3px 0 0;">Original filename is kept on download.</span>
      </div>
      <div class="col-sm-2 form-group"><label class="small">…or a note</label><input class="form-control" name="note" placeholder="note / measurement"></div>
      <div class="col-sm-2 form-group"><label class="small">&nbsp;</label><button class="btn btn-default btn-block"><i class="fa fa-upload"></i> Attach</button></div>
    </form>

    @if($evidence->count())
    <table class="table table-hover">
      <thead><tr><th>Title</th><th>Type</th><th>Added</th><th></th></tr></thead>
      <tbody>
      @foreach($evidence as $e)
      <tr>
        <td>{{ $e->title }}@if($e->json_data && isset($e->json_data['note']))<div class="text-muted small">{{ $e->json_data['note'] }}</div>@endif</td>
        <td><span class="label label-default">{{ $e->evidence_type }}</span></td>
        <td class="text-muted small">{{ $e->created_at?->format('d M Y, g:i A') }}</td>
        <td class="text-right">@if($e->file_path)<a class="btn btn-xs btn-default" href="/evidence/{{ $e->id }}/download"><i class="fa fa-download"></i> Download</a>@endif</td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <p class="text-muted">No evidence attached yet.</p>
    @endif
  </div>
</div>
