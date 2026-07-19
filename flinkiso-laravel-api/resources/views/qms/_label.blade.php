@php
  $map = [
    'open' => 'warning', 'investigating' => 'info', 'capa_raised' => 'primary', 'closed' => 'success',
    'in_progress' => 'info', 'effectiveness_check' => 'primary', 'cancelled' => 'default', 'implemented' => 'success',
    'mitigated' => 'info', 'accepted' => 'default', 'draft' => 'default',
    'low' => 'default', 'medium' => 'info', 'high' => 'warning', 'critical' => 'danger',
    'corrective' => 'primary', 'preventive' => 'info',
    'non_conformity' => 'warning', 'deviation' => 'info', 'incident' => 'primary', 'complaint' => 'danger', 'near_miss' => 'default',
  ];
  $cls = $map[$value] ?? 'default';
@endphp
<span class="label label-{{ $cls }}" style="text-transform:capitalize;">{{ str_replace('_', ' ', $value) }}</span>
