<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
  .header { border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 18px; }
  .meta { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
  .meta td { padding: 5px 8px; border: 1px solid #999; }
  .meta td.k { background: #eee; font-weight: bold; width: 28%; }
  .status { display: inline-block; padding: 3px 10px; border: 1px solid #111; border-radius: 3px; text-transform: uppercase; font-size: 11px; }
  h2 { font-size: 13px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
  table.v { width: 100%; border-collapse: collapse; }
  table.v th, table.v td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
  .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 4px; }
</style>
</head>
<body>
  <div class="header">
    <h1>{{ $document->doc_number }} &middot; {{ $document->title }}</h1>
    <span class="status">{{ $document->status }}</span>
  </div>

  <table class="meta">
    <tr><td class="k">Document number</td><td>{{ $document->doc_number }}</td></tr>
    <tr><td class="k">Title</td><td>{{ $document->title }}</td></tr>
    <tr><td class="k">Category / Type</td><td>{{ $document->category }}@if($document->document_type) / {{ $document->document_type }}@endif</td></tr>
    <tr><td class="k">Version</td><td>v{{ $document->current_version }} ({{ $document->iso_ref }})</td></tr>
    <tr><td class="k">Status</td><td>{{ ucfirst($document->status) }}</td></tr>
    <tr><td class="k">Effective date</td><td>{{ $document->effective_date?->toDateString() ?? 'N/A' }}</td></tr>
    <tr><td class="k">Review due date</td><td>{{ $document->review_due_date?->toDateString() ?? 'N/A' }}</td></tr>
    <tr><td class="k">Related standard / clause</td><td>{{ $standard ?? 'N/A' }}@if($document->related_clause_id) / {{ $document->related_clause_id }}@endif</td></tr>
    <tr><td class="k">Generated</td><td>{{ now()->toDayDateTimeString() }}</td></tr>
  </table>

  <h2>Approval / signature record</h2>
  @if($approval->count())
  <table class="v">
    <tr><th>Action</th><th>Signature meaning</th><th>Signed by</th><th>Reason</th><th>Timestamp</th></tr>
    @foreach($approval as $a)
    <tr>
      <td>{{ $a->action }}</td>
      <td>{{ ucfirst($a->signature_meaning) }}</td>
      <td>{{ $a->username }}</td>
      <td>{{ $a->reason }}</td>
      <td>{{ $a->created_at }}</td>
    </tr>
    @endforeach
  </table>
  @else
  <p>No approval or release signature recorded yet.</p>
  @endif

  <h2 style="margin-top:16px;">Version history</h2>
  <table class="v">
    <tr><th>Version</th><th>Status</th><th>Change summary</th></tr>
    @foreach($document->versions as $v)
    <tr><td>v{{ $v->version }}</td><td>{{ ucfirst($v->status) }}</td><td>{{ $v->change_summary }}</td></tr>
    @endforeach
  </table>

  <div class="footer">
    Controlled document {{ $document->doc_number }} | Version v{{ $document->current_version }} |
    Status {{ ucfirst($document->status) }} | This is a controlled copy when printed from the QMS.
  </div>
</body>
</html>
