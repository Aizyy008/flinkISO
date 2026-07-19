<!doctype html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; color: #111; font-size: 12px; }
  .header { border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 18px; }
  .meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .meta td { padding: 5px 8px; border: 1px solid #999; }
  .meta td.k { background: #eee; font-weight: bold; width: 28%; }
  h2 { font-size: 13px; border-bottom: 1px solid #ccc; padding-bottom: 3px; margin-top: 16px; }
  table.v { width: 100%; border-collapse: collapse; }
  table.v th, table.v td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; font-size: 11px; }
  .footer { position: fixed; bottom: -20px; left: 0; right: 0; font-size: 9px; color: #666; border-top: 1px solid #ccc; padding-top: 4px; }
</style>
</head>
<body>
  <div class="header">
    <h1>Audit Report &middot; {{ $audit->reference }}</h1>
    <span>{{ $audit->title }}</span>
  </div>
  <table class="meta">
    <tr><td class="k">Reference</td><td>{{ $audit->reference }}</td><td class="k">Type</td><td>{{ ucfirst($audit->audit_type) }}</td></tr>
    <tr><td class="k">Standard</td><td>{{ $standard ?? 'N/A' }}</td><td class="k">Status</td><td>{{ strtoupper($audit->status) }}</td></tr>
    <tr><td class="k">Program</td><td>{{ $audit->program?->reference ?? 'N/A' }}</td><td class="k">Result</td><td>{{ $audit->result ? strtoupper(str_replace('_',' ',$audit->result)) : 'N/A' }}</td></tr>
    <tr><td class="k">Planned date</td><td>{{ $audit->planned_date?->toDateString() ?? 'N/A' }}</td><td class="k">Actual date</td><td>{{ $audit->actual_date?->toDateString() ?? 'N/A' }}</td></tr>
    <tr><td class="k">Process / Site / Dept</td><td colspan="3">{{ collect([$audit->related_process,$audit->related_site,$audit->related_department])->filter()->implode(' / ') ?: 'N/A' }} @if($audit->related_clause) &middot; Clause {{ $audit->related_clause }}@endif</td></tr>
    <tr><td class="k">Scope</td><td colspan="3">{{ $audit->scope ?? 'N/A' }}</td></tr>
  </table>

  <h2>Checklist results</h2>
  @if($audit->checklistItems->count())
  <table class="v">
    <tr><th>Section</th><th>Clause</th><th>Requirement</th><th>Response</th><th>Notes</th></tr>
    @foreach($audit->checklistItems as $c)
    <tr><td>{{ $c->section }}</td><td>{{ $c->clause_ref }}</td><td>{{ $c->question }}</td><td>{{ ucfirst($c->response ?? 'pending') }}</td><td>{{ $c->notes }}</td></tr>
    @endforeach
  </table>
  @else<p>No checklist recorded.</p>@endif

  <h2>Findings</h2>
  @if($audit->findings->count())
  <table class="v">
    <tr><th>Reference</th><th>Type</th><th>Severity</th><th>Clause</th><th>Description</th><th>NC raised</th></tr>
    @foreach($audit->findings as $f)
    <tr><td>{{ $f->reference }}</td><td>{{ str_replace('_',' ',$f->finding_type) }}</td><td>{{ ucfirst($f->severity) }}</td><td>{{ $f->clause_ref }}</td><td>{{ $f->description }}</td><td>{{ $f->incident?->reference ?? '—' }}</td></tr>
    @endforeach
  </table>
  @else<p>No findings recorded.</p>@endif

  @if($audit->summary)<h2>Summary</h2><p>{{ $audit->summary }}</p>@endif

  <div class="footer">Audit report {{ $audit->reference }} &middot; Generated {{ now()->toDayDateTimeString() }} &middot; FlinkISO QMS</div>
</body>
</html>
