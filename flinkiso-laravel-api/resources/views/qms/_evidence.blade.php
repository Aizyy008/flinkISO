{{-- Shared evidence box. Expects: $relatedType, $relatedId, $evidence (collection), $redirect --}}
<div class="box box-default">
  <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-paperclip"></i> Evidence</h3></div>
  <div class="box-body">
    <form method="post" action="/evidence" enctype="multipart/form-data" class="row">
      @csrf
      <input type="hidden" name="related_type" value="{{ $relatedType }}">
      <input type="hidden" name="related_id" value="{{ $relatedId }}">
      <input type="hidden" name="redirect" value="{{ $redirect }}">
      <div class="col-sm-3 form-group">
        <select class="form-control" name="evidence_type">
          <option value="file">File</option><option value="photo">Photo</option>
          <option value="measurement">Measurement</option><option value="record">Record</option>
          <option value="report">Report</option>
        </select>
      </div>
      <div class="col-sm-3 form-group"><input type="file" name="file" class="form-control"></div>
      <div class="col-sm-4 form-group"><input class="form-control" name="note" placeholder="or a note / measurement"></div>
      <div class="col-sm-2 form-group"><button class="btn btn-default btn-block"><i class="fa fa-upload"></i> Attach</button></div>
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
