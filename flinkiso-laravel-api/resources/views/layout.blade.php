<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', 'FlinkISO QMS')</title>
<link rel="stylesheet" href="/vendor/flinkiso/css/font-awesome.min.css">
<link rel="stylesheet" href="/vendor/flinkiso/css/allcss.css">
<link rel="stylesheet" href="/vendor/flinkiso/css/icons.css">
<style>
  /* Alignment + responsive tweaks on top of the FlinkISO AdminLTE theme */
  .content-wrapper { min-height: calc(100vh - 101px); }
  .main-header .logo,
  .main-header .navbar { height: 50px; }
  .main-header .navbar-custom-menu .nav > li > a { padding: 15px; line-height: 20px; }
  .qms-sign { font-size: 11px; }

  /* Fix: FlinkISO allcss sets .box.box-primary{color:#fff}, which hides table text.
     Force readable dark text inside box bodies and tables. */
  .content-wrapper .box,
  .content-wrapper .box-body,
  .content-wrapper .box .table > thead > tr > th,
  .content-wrapper .box .table > tbody > tr > td { color: #333 !important; }
  .content-wrapper .box .table > tbody > tr > td .text-muted,
  .content-wrapper .box-body .text-muted { color: #999 !important; }
  /* Coloured solid headers keep white text */
  .box.box-solid.box-primary > .box-header,
  .box.box-solid.box-info > .box-header,
  .box.box-solid.box-danger > .box-header,
  .box.box-solid.box-warning > .box-header { color: #fff !important; }

  /* Fix: remove the forced scrollbars on .table-responsive */
  .content-wrapper .table-responsive { overflow-x: auto !important; overflow-y: visible !important; border: 0 !important; min-height: 0 !important; }

  /* Clean sidebar collapse: fully hide the sidebar (no leftover mini icon strip)
     and let the content take the full width. */
  /* .sidebar-collapse .main-sidebar { transform: translate(-230px, 0) !important; width: 230px !important; }
  .sidebar-collapse .content-wrapper,
  .sidebar-collapse .main-footer { margin-left: 0 !important; } */
  @media (max-width: 767px) {
    .main-header .logo { width: 100%; float: none; text-align: left; }
    .main-header .navbar { margin: 0; float: none; }
    .content-wrapper, .main-footer { margin-left: 0 !important; }
  }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed @if(!session('flink_user'))login-page @endif">
@if(session('flink_user'))
<div class="wrapper">

  <header class="main-header">
    <a href="/documents" class="logo">
      <span class="logo-mini"><b>Q</b>MS</span>
      <span class="logo-lg"><b>FlinkISO</b> QMS</span>
    </a>
    @php $qmsUnread = \App\Models\Qms\Notification::where('user_id', session('flink_user')['id'])->where('is_read', false)->count(); @endphp
    <nav class="navbar navbar-static-top" role="navigation">
      <a href="#" class="sidebar-toggle" id="sidebarToggle" role="button"><span class="sr-only">Toggle navigation</span></a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">
          <li><a href="/notifications" title="Notifications"><i class="fa fa-bell-o"></i>@if($qmsUnread)<span class="label label-warning">{{ $qmsUnread }}</span>@endif</a></li>
          <li><a href="#"><i class="fa fa-user"></i> <span class="hidden-xs">{{ session('flink_user')['username'] }}</span></a></li>
          <li><a href="#" onclick="document.getElementById('logoutForm').submit();return false;"><i class="fa fa-sign-out"></i> <span class="hidden-xs">Logout</span></a></li>
        </ul>
      </div>
    </nav>
  </header>
  <form id="logoutForm" method="post" action="/logout" style="display:none;">@csrf</form>

  <aside class="main-sidebar">
    <section class="sidebar">
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">QUALITY MANAGEMENT</li>
        <li class="@yield('menu_documents')"><a href="/documents"><i class="fa fa-file-text-o"></i> <span>Document Control</span></a></li>
        <li class="@yield('menu_incidents')"><a href="/incidents"><i class="fa fa-exclamation-triangle"></i> <span>Incidents</span></a></li>
        <li class="@yield('menu_capa')"><a href="/capa"><i class="fa fa-wrench"></i> <span>CAPA</span></a></li>
        <li class="@yield('menu_risks')"><a href="/risks"><i class="fa fa-shield"></i> <span>Risk Register</span></a></li>
        <li class="header">AUTOMATION</li>
        <li class="@yield('menu_workflows')"><a href="/workflows"><i class="fa fa-cogs"></i> <span>Workflow rules</span></a></li>
        <li class="@yield('menu_notifications')"><a href="/notifications"><i class="fa fa-bell-o"></i> <span>Notifications</span> @if($qmsUnread)<span class="pull-right-container"><small class="label pull-right bg-yellow">{{ $qmsUnread }}</small></span>@endif</a></li>
      </ul>
    </section>
  </aside>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>@yield('page_title', 'FlinkISO QMS') <small>@yield('page_sub')</small></h1>
      <ol class="breadcrumb">
        <li><a href="/documents"><i class="fa fa-dashboard"></i> QMS</a></li>
        @yield('breadcrumb')
      </ol>
    </section>
    <section class="content">
      @if(session('ok'))<div class="alert alert-success alert-dismissible"><button class="close" data-dismiss="alert">&times;</button>{{ session('ok') }}</div>@endif
      @if($errors->any())<div class="alert alert-danger alert-dismissible"><button class="close" data-dismiss="alert">&times;</button>{{ $errors->first() }}</div>@endif
      @yield('content')
    </section>
  </div>

  <footer class="main-footer">
    <strong>FlinkISO QMS</strong> &middot; Quality Management System
  </footer>
</div>
@else
  @yield('content')
@endif

<script src="/vendor/flinkiso/js/jquery.min.js"></script>
<script src="/vendor/flinkiso/js/bootstrap.min.js"></script>
<script>
  // Reliable sidebar toggle: mini-collapse on desktop, slide-in on mobile.
  (function () {
    var MOBILE = 767;
    document.addEventListener('click', function (e) {
      var t = e.target.closest ? e.target.closest('#sidebarToggle') : null;
      if (!t) return;
      e.preventDefault();
      var b = document.body;
      if (window.innerWidth <= MOBILE) {
        b.classList.toggle('sidebar-open');
      } else {
        b.classList.toggle('sidebar-collapse');
      }
    });
  })();
</script>
</body>
</html>
