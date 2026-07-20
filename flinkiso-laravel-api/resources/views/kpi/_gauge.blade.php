{{-- Reusable SVG gauge. Expects: $kpi, $value (nullable). Dependency-free. --}}
@php
  $status = $kpi->statusFor($value);
  $ach = $kpi->achievement($value);              // % of target (nullable)
  $colors = ['on_target' => '#00a65a', 'warning' => '#f39c12', 'critical' => '#dd4b39', 'no_data' => '#bbb'];
  $color = $colors[$status] ?? '#999';
  $len = 251.3;                                   // pi * r (r=80) semicircle length
  $fillPct = $ach === null ? 0 : max(0, min(100, $ach));
  $offset = $len * (1 - $fillPct / 100);
@endphp
<div style="text-align:center;">
  <svg viewBox="0 0 200 118" style="width:100%;max-width:200px;">
    <path d="M20,100 A80,80 0 0 1 180,100" fill="none" stroke="#eee" stroke-width="16" stroke-linecap="round"/>
    <path d="M20,100 A80,80 0 0 1 180,100" fill="none" stroke="{{ $color }}" stroke-width="16" stroke-linecap="round"
          stroke-dasharray="{{ $len }}" stroke-dashoffset="{{ $offset }}"/>
    <text x="100" y="88" text-anchor="middle" style="font-size:26px;font-weight:bold;fill:#333;">{{ $value !== null ? rtrim(rtrim(number_format((float)$value, 2), '0'), '.') : '—' }}</text>
    <text x="100" y="108" text-anchor="middle" style="font-size:11px;fill:#999;">{{ $kpi->unit }}@if($kpi->target_value !== null) / target {{ rtrim(rtrim(number_format((float)$kpi->target_value, 2), '0'), '.') }}@endif</text>
  </svg>
  <div><span class="label" style="background:{{ $color }};text-transform:capitalize;">{{ str_replace('_',' ',$status) }}</span>
    @if($ach !== null)<span class="text-muted qms-sign">{{ $ach }}% of target</span>@endif
  </div>
</div>
