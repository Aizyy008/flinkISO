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
    <tr><td class="k">Version / Revision / Issue</td><td>v{{ $document->current_version }} &middot; Rev {{ $document->revision_number ?? 0 }} &middot; Issue {{ $document->issue_number ?? 1 }}</td></tr>
    <tr><td class="k">Status</td><td><strong>{{ strtoupper($document->status) }}</strong></td></tr>
    <tr><td class="k">Effective date</td><td>{{ $document->effective_date?->toDateString() ?? 'N/A' }}</td></tr>
    <tr><td class="k">Review due date</td><td>{{ $document->review_due_date?->toDateString() ?? 'N/A' }}</td></tr>
    <tr><td class="k">Related standard / clause</td><td>{{ $standard ?? 'N/A' }}@if($document->related_clause_id) / {{ $document->related_clause_id }}@endif</td></tr>
    <tr><td class="k">Generated</td><td>{{ now()->toDayDateTimeString() }}</td></tr>
  </table>

  <h2>Electronic signature record (21 CFR Part 11)</h2>
  @if($signatures->count())
  <table class="v">
    <tr><th>Action</th><th>Meaning</th><th>Signed by</th><th>Reason</th><th>Timestamp (UTC)</th><th>Record reference</th></tr>
    @foreach($signatures as $s)
    <tr>
      <td>{{ ucfirst(str_replace('_', ' ', $s->action)) }}</td>
      <td>{{ ucfirst($s->meaning) }}</td>
      <td>{{ $s->signer_name }} ({{ $s->signer_username }})</td>
      <td>{{ $s->reason }}</td>
      <td>{{ $s->signed_at }}</td>
      <td style="font-size:9px;">{{ $s->record_reference }}</td>
    </tr>
    @endforeach
  </table>
  @else
  <p>No electronic signature recorded yet.</p>
  @endif

  @if($document->controlledCopies->where('returned_at', null)->count())
  <h2 style="margin-top:16px;">Controlled copy register (active)</h2>
  <table class="v">
    <tr><th>Copy holder</th><th>Location</th><th>Version</th><th>Issued</th></tr>
    @foreach($document->controlledCopies->whereNull('returned_at') as $c)
    <tr><td>{{ $c->holder }}</td><td>{{ $c->location ?? '—' }}</td><td>v{{ $c->version }}</td><td>{{ $c->issued_at ?? $c->created_at }}</td></tr>
    @endforeach
  </table>
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
