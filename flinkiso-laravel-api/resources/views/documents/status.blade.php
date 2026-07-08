@php
  $map = [
    'draft' => 'default', 'review' => 'warning', 'approved' => 'info',
    'released' => 'success', 'obsolete' => 'danger', 'superseded' => 'default',
    'open' => 'warning', 'implemented' => 'success',
  ];
  $cls = $map[$status] ?? 'default';
@endphp
<span class="label label-{{ $cls }}" style="text-transform:capitalize;">{{ $status }}</span>
