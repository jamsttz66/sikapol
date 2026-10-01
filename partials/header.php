<?php
/**
 * Layout: header + sidebar + topbar
 * Variabel opsional: $pageTitle, $pageSubtitle, $active
 */
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$user      = currentUser();
$active    = $active    ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$flashes   = getFlashes();

$menu = [
    ['key' => 'dashboard',  'file' => 'index.php',            'icon' => 'speedometer2',      'label' => 'Dashboard'],
    ['key' => 'kunjungan',  'file' => 'kunjungan.php',        'icon' => 'clipboard2-pulse',  'label' => 'Pencatatan Kunjungan'],
    ['key' => 'riwayat',    'file' => 'riwayat.php',          'icon' => 'clock-history',     'label' => 'Riwayat Kunjungan'],
    ['key' => 'pencarian',  'file' => 'pencarian.php',        'icon' => 'search',            'label' => 'Pencarian Pasien'],
    ['key' => 'pasien',     'file' => 'pasien.php',           'icon' => 'people',            'label' => 'Data Pasien'],
];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root{
    --sk-primary:#0d6e5f;
    --sk-primary-dark:#0a5a4d;
    --sk-sidebar:#0f2d2a;
    --sk-sidebar-soft:#173f3a;
    --sk-accent:#22c55e;
  }
  body{background:#f2f5f7;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
  .sidebar{
    position:fixed;top:0;left:0;bottom:0;width:250px;z-index:1040;
    background:var(--sk-sidebar);color:#cfe8e3;overflow-y:auto;
    transition:transform .25s ease;
  }
  .sidebar .brand{
    padding:1.1rem 1.15rem;display:flex;align-items:center;gap:.65rem;
    border-bottom:1px solid rgba(255,255,255,.08);
  }
  .sidebar .brand .logo{
    width:38px;height:38px;border-radius:10px;flex:0 0 38px;
    background:linear-gradient(140deg,var(--sk-accent),#0ea5e9);
    display:grid;place-items:center;color:#062b25;font-size:1.15rem;
  }
  .sidebar .brand b{display:block;color:#fff;font-size:1rem;letter-spacing:.02em;line-height:1.1}
  .sidebar .brand small{font-size:.63rem;color:#8fb8b0;letter-spacing:.06em}
  .sidebar .nav-link{
    color:#a9cdc7;font-size:.87rem;padding:.62rem 1.15rem;border-radius:0;
    display:flex;align-items:center;gap:.65rem;border-left:3px solid transparent;
  }
  .sidebar .nav-link:hover{background:var(--sk-sidebar-soft);color:#fff}
  .sidebar .nav-link.active{
    background:var(--sk-sidebar-soft);color:#fff;font-weight:600;
    border-left-color:var(--sk-accent);
  }
  .sidebar .nav-link i{font-size:1.02rem;width:1.2rem;text-align:center}
  .sidebar .nav-section{
    padding:.9rem 1.15rem .35rem;font-size:.62rem;letter-spacing:.14em;
    color:#6a9c93;text-transform:uppercase;font-weight:700;
  }
  .sidebar .sidebar-foot{
    padding:.85rem 1.15rem;font-size:.68rem;color:#6a9c93;
    border-top:1px solid rgba(255,255,255,.08);margin-top:.5rem;
  }
  .main{margin-left:250px;min-height:100vh;display:flex;flex-direction:column}
  .topbar{
    background:#fff;border-bottom:1px solid #e3e9ee;padding:.7rem 1.25rem;
    display:flex;align-items:center;gap:.9rem;position:sticky;top:0;z-index:1030;
  }
  .topbar h1{font-size:1.05rem;font-weight:700;margin:0;color:#12312c}
  .topbar .sub{font-size:.74rem;color:#7b8c96;margin:0}
  .content{padding:1.25rem;flex:1}
  .card{border:1px solid #e3e9ee;border-radius:12px;box-shadow:0 1px 2px rgba(16,40,36,.04)}
  .card-header{background:#fbfdfd;border-bottom:1px solid #e9eff3;font-weight:600;font-size:.88rem;color:#12312c}
  .stat-card .icon{
    width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:1.3rem;
  }
  .stat-card .value{font-size:1.65rem;font-weight:800;line-height:1;color:#12312c}
  .stat-card .label{font-size:.75rem;color:#7b8c96}
  .table>:not(caption)>*>*{padding:.6rem .75rem;font-size:.85rem;vertical-align:middle}
  .table thead th{
    background:#f6f9fa;font-size:.72rem;letter-spacing:.05em;text-transform:uppercase;
    color:#5d7280;font-weight:700;border-bottom:1px solid #e3e9ee;
  }
  .badge-soft{background:#e7f5f1;color:#0d6e5f;font-weight:600}
  .btn-sk{background:var(--sk-primary);border-color:var(--sk-primary);color:#fff}
  .btn-sk:hover,.btn-sk:focus{background:var(--sk-primary-dark);border-color:var(--sk-primary-dark);color:#fff}
  .btn-outline-sk{border-color:var(--sk-primary);color:var(--sk-primary)}
  .btn-outline-sk:hover{background:var(--sk-primary);color:#fff}
  .form-label{font-size:.82rem;font-weight:600;color:#294a44}
  .form-control,.form-select{font-size:.86rem;border-color:#d6e0e6}
  .form-control:focus,.form-select:focus{
    border-color:var(--sk-primary);box-shadow:0 0 0 .18rem rgba(13,110,95,.14);
  }
  .empty-state{padding:2.5rem 1rem;text-align:center;color:#8a9aa4}
  .empty-state i{font-size:2.4rem;opacity:.35;display:block;margin-bottom:.6rem}
  .avatar{
    width:34px;height:34px;border-radius:50%;background:#e7f5f1;color:#0d6e5f;
    display:grid;place-items:center;font-weight:700;font-size:.78rem;flex:0 0 34px;
  }
  .sidebar-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1035}
  @media (max-width:991.98px){
    .sidebar{transform:translateX(-100%)}
    .sidebar.show{transform:none}
    .main{margin-left:0}
    .sidebar-backdrop.show{display:block}
  }
  @media print{
    .sidebar,.topbar,.no-print{display:none!important}
    .main{margin-left:0}
    body{background:#fff}
  }
</style>
</head>
<body>

<aside class="sidebar" id="sidebar">
  <div class="brand">
    <span class="logo"><i class="bi bi-heart-pulse-fill"></i></span>
    <span>
      <b><?= e(APP_NAME) ?></b>
      <small><?= e(APP_INSTANSI) ?></small>
    </span>
  </div>

  <nav class="nav flex-column mt-2">
    <div class="nav-section">Menu Utama</div>
    <?php foreach ($menu as $item): ?>
      <a class="nav-link <?= $active === $item['key'] ? 'active' : '' ?>" href="<?= e($item['file']) ?>">
        <i class="bi bi-<?= e($item['icon']) ?>"></i>
        <span><?= e($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-foot">
    <?= e(APP_FULL) ?><br>
    v<?= e(APP_VERSION) ?>
  </div>
</aside>
<div class="sidebar-backdrop" id="backdrop"></div>

<div class="main">
  <header class="topbar">
    <button class="btn btn-sm btn-light d-lg-none" id="toggleSidebar" aria-label="Menu">
      <i class="bi bi-list"></i>
    </button>
    <div class="me-auto">
      <h1><?= e($pageTitle) ?></h1>
      <?php if (!empty($pageSubtitle)): ?>
        <p class="sub"><?= e($pageSubtitle) ?></p>
      <?php endif; ?>
    </div>
    <div class="dropdown">
      <button class="btn btn-sm btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown">
        <span class="avatar"><?= e(strtoupper(substr($user['nama'], 0, 1))) ?></span>
        <span class="d-none d-sm-inline text-start lh-sm">
          <span class="d-block fw-semibold" style="font-size:.8rem"><?= e($user['nama']) ?></span>
          <span class="d-block text-muted" style="font-size:.68rem"><?= e(ucfirst($user['role'])) ?></span>
        </span>
        <i class="bi bi-chevron-down small"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><span class="dropdown-item-text small text-muted">
          Masuk sebagai <b><?= e($user['username']) ?></b>
        </span></li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="logout.php"
             onclick="return confirm('Keluar dari sistem?')">
            <i class="bi bi-box-arrow-right me-1"></i> Keluar
          </a>
        </li>
      </ul>
    </div>
  </header>

  <div class="content">
    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show py-2" role="alert">
        <i class="bi bi-info-circle me-1"></i><?= e($flash['message']) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
