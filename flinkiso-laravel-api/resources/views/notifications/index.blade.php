@extends('layout')
@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('menu_notifications', 'active')
@section('breadcrumb')<li class="active">Notifications</li>@endsection
@section('content')
@php
  $typeLabels = ['assignment' => 'Assignment', 'status_change' => 'Status change', 'deviation' => 'Deviation', 'overdue' => 'Overdue reminder'];
  $typeIcons = ['assignment' => 'user-plus', 'status_change' => 'exchange', 'deviation' => 'exclamation-triangle', 'overdue' => 'clock-o'];
@endphp
<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">My notifications</h3>
    <div class="box-tools">
      <form method="post" action="/notifications/read-all" style="display:inline;">@csrf<button class="btn btn-default btn-sm">Mark all read</button></form>
    </div>
  </div>
  <div class="box-body no-padding">
    @if($notifications->count())
    <table class="table table-hover">
      <tbody>
      @foreach($notifications as $n)
      @php $icon = $typeIcons[$n->type] ?? 'bell'; $label = $typeLabels[$n->type] ?? ucfirst(str_replace('_', ' ', $n->type)); $link = \App\Http\Controllers\Web\NotificationController::recordLink($n->related_type, $n->related_id); @endphp
      <tr class="notif-row{{ $n->is_read ? '' : ' notif-unread' }}"
          style="cursor:pointer;{{ $n->is_read ? '' : 'font-weight:bold;background:#f9fbfd;' }}"
          data-id="{{ $n->id }}"
          data-unread="{{ $n->is_read ? 0 : 1 }}"
          data-title="{{ e($n->title) }}"
          data-body="{{ e($n->body) }}"
          data-icon="{{ $icon }}"
          data-typelabel="{{ e($label) }}"
          data-time="{{ $n->created_at?->format('d M Y, g:i A') }}"
          data-link="{{ $link }}">
        <td class="notif-icon-cell" style="width:56px;padding-left:22px;"><i class="notif-icon fa fa-{{ $icon }} text-{{ $n->is_read ? 'muted' : 'aqua' }}"></i></td>
        <td>
          <span class="notif-title">{{ $n->title }}</span>
          @if($n->body)<div class="text-muted small notif-preview" style="font-weight:normal;">{{ \Illuminate\Support\Str::limit($n->body, 90) }}</div>@endif
        </td>
        <td class="text-muted small" style="width:160px;font-weight:normal;">{{ $n->created_at?->format('d M Y, g:i A') }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
    @else
    <div class="box-body"><p class="text-muted">No notifications.</p></div>
    @endif
  </div>
  @if($notifications->count())<div class="box-footer">{{ $notifications->links() }}</div>@endif
</div>

{{-- Notification detail modal (AdminLTE / Bootstrap 3, centered) --}}
<div class="modal fade" id="notifModal" tabindex="-1" role="dialog" aria-labelledby="notifModalTitle">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" style="padding:16px 22px;">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="notifModalTitle"><i id="notifModalIcon" class="fa fa-bell"></i> <span id="notifModalTitleText"></span></h4>
      </div>
      <div class="modal-body" style="padding:22px;">
        <p id="notifModalBody" style="white-space:pre-line;font-size:14px;line-height:1.6;margin:0 0 18px;"></p>
        <hr style="margin:0 0 16px;">
        <table class="notif-meta"><tbody>
          <tr><th>Type</th><td id="notifModalType"></td></tr>
          <tr><th>Received</th><td id="notifModalTime"></td></tr>
        </tbody></table>
      </div>
      <div class="modal-footer" style="padding:14px 22px;">
        <a href="#" id="notifModalOpen" class="btn btn-primary" style="display:none;"><i class="fa fa-external-link"></i> Open record</a>
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<style>
  /* Centre the Bootstrap 3 modal vertically. */
  #notifModal .modal-dialog { display:flex; align-items:center; min-height:calc(100vh - 60px); }
  #notifModal .modal-content { width:100%; }
  /* Detail rows: fixed label column, left-aligned, no theme interference. */
  #notifModal .notif-meta { width:100%; border-collapse:collapse; }
  #notifModal .notif-meta th,
  #notifModal .notif-meta td { padding:5px 0; vertical-align:top; text-align:left; border:0; font-size:14px; }
  #notifModal .notif-meta th { width:110px; font-weight:700; color:#555; white-space:nowrap; }
  #notifModal .notif-meta td { color:#333; }
</style>
<script>
(function () {
  var $modal = jQuery('#notifModal');

  function updateBadges(n) {
    jQuery('.notif-badge').each(function () {
      var $b = jQuery(this);
      if (n > 0) { $b.text(n).show(); } else { $b.hide(); }
    });
  }

  jQuery('.notif-row').on('click', function () {
    var $r = jQuery(this);
    jQuery('#notifModalTitleText').text($r.data('title'));
    jQuery('#notifModalIcon').attr('class', 'fa fa-' + $r.data('icon'));
    jQuery('#notifModalBody').text($r.data('body') || 'No additional details.');
    jQuery('#notifModalType').text($r.data('typelabel'));
    jQuery('#notifModalTime').text($r.data('time'));

    var link = $r.data('link');
    if (link) { jQuery('#notifModalOpen').attr('href', link).show(); }
    else { jQuery('#notifModalOpen').hide(); }

    $modal.modal('show');

    // Mark read as soon as the notification is opened.
    if ($r.data('unread')) {
      jQuery.ajax({
        url: '/notifications/' + $r.data('id') + '/read',
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      }).done(function (res) {
        $r.data('unread', false).removeClass('notif-unread').css({ 'font-weight': '', 'background': '' });
        $r.find('.notif-icon').removeClass('text-aqua').addClass('text-muted');
        if (res && typeof res.unread !== 'undefined') { updateBadges(res.unread); }
      });
    }
  });
})();
</script>
@endsection
