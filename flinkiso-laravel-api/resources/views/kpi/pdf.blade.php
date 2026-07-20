<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#111;font-size:12px;}
h1{font-size:18px;border-bottom:2px solid #111;padding-bottom:6px;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border:1px solid #ccc;padding:5px 7px;text-align:left;font-size:11px;}
.on_target{color:#00a65a;}.warning{color:#f39c12;}.critical{color:#dd4b39;}
</style></head><body>
<h1>KPI Report</h1>
<p>Generated {{ $generated->toDayDateTimeString() }} &middot; {{ $kpis->count() }} KPI(s)</p>
<table>
<tr><th>Reference</th><th>Name</th><th>Area</th><th>Target</th><th>Latest value</th><th>Period</th><th>Status</th></tr>
@foreach($kpis as $kpi)
@php $lv=$kpi->latestResult?->value; $st=$kpi->statusFor($lv); @endphp
<tr><td>{{ $kpi->reference }}</td><td>{{ $kpi->name }}</td><td>{{ ucfirst(str_replace('_',' ',$kpi->area)) }}</td>
<td>{{ $kpi->target_value }} {{ $kpi->unit }}</td><td>{{ $lv ?? '—' }}</td><td>{{ $kpi->latestResult?->period_label ?? '—' }}</td>
<td class="{{ $st }}">{{ strtoupper(str_replace('_',' ',$st)) }}</td></tr>
@endforeach
</table>
</body></html>
