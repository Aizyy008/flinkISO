@extends('layout')
@section('title', 'Notifications')
@section('page_title', 'Notifications')
@section('menu_notifications', 'active')
@section('breadcrumb')<li class="active">Notifications</li>@endsection
@section('content')
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
      <tr style="{{ $n->is_read ? '' : 'font-weight:bold;background:#f9fbfd;' }}">
        <td style="width:40px;"><i class="fa fa-{{ $n->type==='assignment' ? 'user-plus' : ($n->type==='status_change' ? 'exchange' : ($n->type==='deviation' ? 'exclamation-triangle' : 'bell')) }} text-{{ $n->is_read ? 'muted' : 'aqua' }}"></i></td>
        <td>
          <a href="/notifications/{{ $n->id }}/read">{{ $n->title }}</a>
          @if($n->body)<div class="text-muted small" style="font-weight:normal;">{{ $n->body }}</div>@endif
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
@endsection
