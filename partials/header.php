<?php
/**
 * SIKAPOL — layout utama (sidebar + topbar + konten)
 * Variabel opsional: $pageTitle, $pageSubtitle, $active
 */
require_once dirname(__DIR__) . '/config.php';
requireLogin();

$user      = currentUser();
$active    = $active    ?? '';
$pageTitle = $pageTitle ?? 'Dashboard';
$flashes   = getFlashes();

$menu = [
    ['key' => 'dashboard',  'file' => 'index.php',      'icon' => 'grid-1x2',        'label' => 'Dashboard'],
    ['key' => 'kunjungan',  'file' => 'kunjungan.php',  'icon' => 'clipboard2-pulse', 'label' => 'Pencatatan Kunjungan'],
    ['key' => 'riwayat',    'file' => 'riwayat.php',    'icon' => 'clock-history',   'label' => 'Riwayat Kunjungan'],
    ['key' => 'pencarian',  'file' => 'pencarian.php',  'icon' => 'search',          'label' => 'Pencarian Pasien'],
    ['key' => 'pasien',     'file' => 'pasien.php',     'icon' => 'people',          'label' => 'Data Pasien'],
];
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#0b3d38">
<title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  /* ============ DESIGN TOKENS ============ */
  :root{
    --brand-900:#06322e;
    --brand-800:#0a4a43;
    --brand-700:#0d625a;
    --brand-600:#0f7a6f;
    --brand-500:#12a08f;
    --brand-100:#e3f5f1;
    --brand-50:#f0faf8;
    --accent:#f59e0b;
    --ink-900:#0d201d;
    --ink-700:#33514b;
    --ink-500:#6b8480;
    --ink-300:#9db0ac;
    --line:#e2ebe9;
    --surface:#ffffff;
    --canvas:#f1f5f6;
    --danger:#dc2626;
    --warn:#d97706;
    --info:#0284c7;
    --ok:#059669;
    --radius:14px;
    --radius-sm:10px;
    --shadow-xs:0 1px 2px rgba(9,44,40,.05);
    --shadow-sm:0 2px 8px rgba(9,44,40,.06);
    --shadow-md:0 8px 24px rgba(9,44,40,.09);
    --sb-w:264px;
  }

  *{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
  body{
    background:var(--canvas);
    font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    color:var(--ink-900);font-size:.9rem;
  }
  h1,h2,h3,h4,h5,h6{color:var(--ink-900);font-weight:700;letter-spacing:-.01em}
  a{text-decoration:none}
  ::selection{background:var(--brand-100);color:var(--brand-800)}

  /* ============ SCROLLBAR ============ */
  ::-webkit-scrollbar{width:9px;height:9px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:#c4d4d1;border-radius:99px;border:2px solid var(--canvas)}
  ::-webkit-scrollbar-thumb:hover{background:#a6bcb8}

  /* ============ SIDEBAR ============ */
  .sidebar{
    position:fixed;top:0;left:0;bottom:0;width:var(--sb-w);z-index:1040;
    background:linear-gradient(178deg,var(--brand-900) 0%,#082a27 100%);
    display:flex;flex-direction:column;
    transition:transform .28s cubic-bezier(.4,0,.2,1);
  }
  .sidebar::after{
    content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(600px 240px at 0% 0%,rgba(18,160,143,.20),transparent 70%);
  }
  .sidebar .brand{
    padding:1.15rem 1.2rem;display:flex;align-items:center;gap:.75rem;
    border-bottom:1px solid rgba(255,255,255,.07);position:relative;z-index:1;
  }
  .sidebar .brand .logo{
    width:42px;height:42px;border-radius:12px;flex:0 0 42px;
    background:linear-gradient(142deg,var(--brand-500),#0891b2);
    display:grid;place-items:center;color:#fff;font-size:1.22rem;
    box-shadow:0 4px 14px rgba(18,160,143,.34);
  }
  .sidebar .brand b{
    display:block;color:#fff;font-size:1.06rem;font-weight:800;
    letter-spacing:.03em;line-height:1.15;
  }
  .sidebar .brand small{
    font-size:.615rem;color:#7fb5ad;letter-spacing:.05em;display:block;margin-top:1px;
  }
  .sidebar .nav-wrap{flex:1;overflow-y:auto;padding:.5rem 0 1rem;position:relative;z-index:1}
  .sidebar .nav-wrap::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-color:transparent}
  .sidebar .nav-section{
    padding:1rem 1.2rem .4rem;font-size:.6rem;letter-spacing:.16em;
    color:#5f918a;text-transform:uppercase;font-weight:800;
  }
  .sidebar .nav-link{
    color:#a8cfc9;font-size:.855rem;font-weight:500;
    padding:.6rem 1rem .6rem 1.2rem;border-radius:0;
    display:flex;align-items:center;gap:.75rem;
    border-left:3px solid transparent;position:relative;
    transition:background .15s,color .15s,border-color .15s;
  }
  .sidebar .nav-link i{font-size:1.05rem;width:1.25rem;text-align:center;flex:0 0 1.25rem}
  .sidebar .nav-link:hover{background:rgba(255,255,255,.055);color:#fff}
  .sidebar .nav-link.active{
    background:rgba(18,160,143,.16);color:#fff;font-weight:600;
    border-left-color:var(--brand-500);
  }
  .sidebar .nav-link.active i{color:#4ade9f}
  .sidebar .sidebar-foot{
    padding:.85rem 1.2rem;font-size:.645rem;color:#5f918a;line-height:1.55;
    border-top:1px solid rgba(255,255,255,.07);position:relative;z-index:1;
  }
  .sidebar .sidebar-foot b{color:#8fc4bc;font-weight:600}

  /* ============ MAIN ============ */
  .main{margin-left:var(--sb-w);min-height:100vh;display:flex;flex-direction:column}

  .topbar{
    background:rgba(255,255,255,.9);backdrop-filter:saturate(180%) blur(12px);
    border-bottom:1px solid var(--line);padding:.75rem 1.5rem;
    display:flex;align-items:center;gap:1rem;position:sticky;top:0;z-index:1030;
  }
  .topbar .page-head h1{
    font-size:1.14rem;font-weight:800;margin:0;line-height:1.25;
  }
  .topbar .page-head .sub{
    font-size:.735rem;color:var(--ink-500);margin:0;font-weight:500;
  }
  .topbar .crumb{
    font-size:.665rem;color:var(--ink-300);text-transform:uppercase;
    letter-spacing:.1em;font-weight:700;margin-bottom:.15rem;
  }

  /* user chip */
  .user-chip{
    display:flex;align-items:center;gap:.6rem;background:#fff;
    border:1px solid var(--line);border-radius:99px;padding:.3rem .55rem .3rem .3rem;
    transition:box-shadow .16s,border-color .16s,transform .16s;
  }
  .user-chip:hover{box-shadow:var(--shadow-sm);border-color:#cfe0dd;transform:translateY(-1px)}
  .user-chip .avatar{width:32px;height:32px;flex:0 0 32px;font-size:.78rem}
  .user-chip .nm{font-size:.78rem;font-weight:700;color:var(--ink-900);line-height:1.15}
  .user-chip .rl{font-size:.645rem;color:var(--ink-500);font-weight:600}

  .content{padding:1.5rem;flex:1;max-width:1560px;width:100%}

  /* ============ CARDS ============ */
  .card{
    border:1px solid var(--line);border-radius:var(--radius);
    box-shadow:var(--shadow-xs);background:var(--surface);overflow:hidden;
  }
  .card-header{
    background:#fff;border-bottom:1px solid var(--line);
    font-weight:700;font-size:.855rem;color:var(--ink-900);
    padding:.85rem 1.1rem;display:flex;align-items:center;gap:.5rem;
  }
  .card-header i{color:var(--brand-600)}
  .card-body{padding:1.1rem}

  /* ============ STAT CARDS ============ */
  .stat{
    position:relative;background:#fff;border:1px solid var(--line);
    border-radius:var(--radius);padding:1.15rem 1.2rem;
    box-shadow:var(--shadow-xs);transition:transform .18s,box-shadow .18s;
    overflow:hidden;height:100%;
  }
  .stat:hover{transform:translateY(-3px);box-shadow:var(--shadow-md)}
  .stat::before{
    content:"";position:absolute;top:0;left:0;right:0;height:3px;
    background:var(--tone,var(--brand-600));opacity:.9;
  }
  .stat .ico{
    width:46px;height:46px;border-radius:13px;display:grid;place-items:center;
    font-size:1.32rem;background:var(--tone-bg,var(--brand-100));color:var(--tone,var(--brand-700));
    margin-bottom:.85rem;
  }
  .stat .val{
    font-size:1.95rem;font-weight:800;line-height:1;color:var(--ink-900);
    letter-spacing:-.02em;font-variant-numeric:tabular-nums;
  }
  .stat .lbl{
    font-size:.7rem;color:var(--ink-500);font-weight:700;
    text-transform:uppercase;letter-spacing:.08em;margin-top:.35rem;
  }
  .stat .hint{font-size:.7rem;color:var(--ink-500);margin-top:.45rem;font-weight:600}
  .stat .hint b{color:var(--ok);font-weight:700}

  /* ============ TABLE ============ */
  .table-responsive{border-radius:0;max-height:none}
  .table{margin:0;--bs-table-bg:transparent}
  /* teks panjang dipotong 2 baris biar tinggi baris konsisten */
  .clamp-2{
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
    overflow:hidden;text-overflow:ellipsis;line-height:1.4;
  }
  .table>:not(caption)>*>*{padding:.68rem .85rem;font-size:.845rem;vertical-align:middle}
  .table thead th{
    background:#f7fafb;font-size:.675rem;letter-spacing:.075em;text-transform:uppercase;
    color:var(--ink-500);font-weight:800;border-bottom:1px solid var(--line);
    white-space:nowrap;position:sticky;top:0;
  }
  .table tbody tr{transition:background .12s}
  .table-hover tbody tr:hover>*{background:var(--brand-50)}
  .table tbody td{border-color:#eef4f3;color:var(--ink-700)}
  .table .fw-semibold,.table strong{color:var(--ink-900)}

  /* ============ BADGES ============ */
  .badge-soft{
    background:var(--brand-100);color:var(--brand-700);
    font-weight:700;font-size:.7rem;padding:.32em .62em;border-radius:6px;
    letter-spacing:.01em;
  }
  .badge-soft.neutral{background:#eef2f3;color:#5b6f6c}
  .badge{font-weight:700;letter-spacing:.01em}

  /* ============ BUTTONS ============ */
  .btn{font-weight:600;font-size:.855rem;border-radius:var(--radius-sm);transition:all .15s}
  .btn-sm{font-size:.79rem;padding:.32rem .68rem;border-radius:8px}
  .btn-sk{
    background:var(--brand-700);border-color:var(--brand-700);color:#fff;
    box-shadow:0 1px 2px rgba(13,98,90,.25);
  }
  .btn-sk:hover,.btn-sk:focus,.btn-sk:active{
    background:var(--brand-800);border-color:var(--brand-800);color:#fff;
    box-shadow:0 4px 12px rgba(13,98,90,.24);transform:translateY(-1px);
  }
  .btn-outline-sk{border-color:#c9dcd9;color:var(--brand-700);background:#fff}
  .btn-outline-sk:hover{background:var(--brand-50);border-color:var(--brand-500);color:var(--brand-800)}
  .btn-light{background:#fff;border-color:var(--line);color:var(--ink-700)}
  .btn-light:hover{background:#f7fafb;border-color:#cfe0dd}

  /* ============ FORMS ============ */
  .form-label{font-size:.79rem;font-weight:700;color:var(--ink-700);margin-bottom:.35rem}
  .form-control,.form-select{
    font-size:.865rem;border-color:#d5e2df;border-radius:var(--radius-sm);
    padding:.5rem .75rem;color:var(--ink-900);background:#fff;
  }
  .form-control::placeholder{color:var(--ink-300)}
  .form-control:focus,.form-select:focus{
    border-color:var(--brand-500);box-shadow:0 0 0 .2rem rgba(18,160,143,.13);
  }
  .form-control-sm,.form-select-sm{font-size:.81rem;padding:.38rem .6rem}
  .input-group-text{background:#f7fafb;border-color:#d5e2df;color:var(--ink-500)}
  .form-text{font-size:.72rem;color:var(--ink-500)}

  /* ============ MISC ============ */
  .empty-state{padding:3rem 1.25rem;text-align:center;color:var(--ink-300);font-size:.86rem}
  .empty-state i{font-size:2.5rem;opacity:.4;display:block;margin-bottom:.7rem;color:var(--brand-500)}
  .avatar{
    width:34px;height:34px;border-radius:50%;flex:0 0 34px;
    background:linear-gradient(140deg,var(--brand-100),#d6f0eb);
    color:var(--brand-700);display:grid;place-items:center;
    font-weight:800;font-size:.78rem;letter-spacing:.02em;
  }
  .avatar.avatar-sm{width:30px;height:30px;flex:0 0 30px;font-size:.82rem}
  .avatar-lg{width:76px;height:76px;flex:0 0 76px;font-size:1.9rem}
  .alert{border-radius:var(--radius-sm);border:1px solid transparent;font-size:.845rem;font-weight:500}
  .alert-success{background:#eafaf3;border-color:#c3ead9;color:#0a6b4d}
  .alert-danger{background:#fdeeee;border-color:#f6cfcf;color:#a02020}
  .alert-warning{background:#fef7e8;border-color:#f6e2b8;color:#8a5a06}
  .alert-info{background:#eaf5fd;border-color:#c6e2f7;color:#0b5686}
  .nav-pills .nav-link{
    background:#fff;border:1px solid var(--line);color:var(--ink-700);
    font-size:.83rem;font-weight:700;border-radius:var(--radius-sm);padding:.5rem .9rem;
  }
  .nav-pills .nav-link.active{background:var(--brand-700);border-color:var(--brand-700);color:#fff}
  .dropdown-menu{
    border:1px solid var(--line);border-radius:var(--radius-sm);
    box-shadow:var(--shadow-md);font-size:.84rem;padding:.35rem;min-width:13rem;
  }
  .dropdown-item{border-radius:7px;padding:.45rem .65rem;font-weight:500}
  .dropdown-item:hover{background:var(--brand-50)}
  .dropdown-divider{margin:.3rem 0;border-color:var(--line)}
  .info-inline{
    display:flex;align-items:center;gap:.55rem;
    background:var(--brand-50);border:1px solid #d7ece8;border-radius:var(--radius-sm);
    padding:.5rem .8rem;font-size:.76rem;color:var(--ink-700);line-height:1.5;
  }
  .info-inline i{color:var(--brand-600);font-size:.92rem;flex:0 0 auto}
  .section-title{
    font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;
    color:var(--ink-500);margin:0 0 .75rem;
  }

  /* ============ SIDEBAR TOGGLE / BACKDROP ============ */
  .sidebar-backdrop{
    display:none;position:fixed;inset:0;background:rgba(6,50,46,.45);
    z-index:1035;backdrop-filter:blur(2px);
  }
  @media (max-width:991.98px){
    .sidebar{transform:translateX(-100%);box-shadow:none}
    .sidebar.show{transform:none;box-shadow:var(--shadow-md)}
    .main{margin-left:0}
    .sidebar-backdrop.show{display:block}
    .content{padding:1.1rem}
    .topbar{padding:.7rem 1.1rem}
  }
  @media (max-width:575.98px){
    .stat .val{font-size:1.6rem}
    .content{padding:.9rem}
  }
  @media print{
    .sidebar,.topbar,.no-print,.sidebar-backdrop{display:none!important}
    .main{margin-left:0}
    body{background:#fff}
    .card{border-color:#ccc;box-shadow:none;break-inside:avoid}
    .content{padding:0;max-width:none}
    .table thead th{position:static}
  }
  @media (prefers-reduced-motion:reduce){
    *{transition:none!important;animation:none!important}
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

  <div class="nav-wrap">
    <nav class="nav flex-column">
      <div class="nav-section">Menu Utama</div>
      <?php foreach ($menu as $item): ?>
        <a class="nav-link <?= $active === $item['key'] ? 'active' : '' ?>"
           href="<?= e($item['file']) ?>">
          <i class="bi bi-<?= e($item['icon']) ?>"></i>
          <span><?= e($item['label']) ?></span>
        </a>
      <?php endforeach; ?>

      <div class="nav-section">Akun</div>
      <a class="nav-link <?= $active === 'pengaturan' ? 'active' : '' ?>" href="pengaturan.php">
        <i class="bi bi-gear"></i><span>Pengaturan</span>
      </a>
    </nav>
  </div>

  <div class="sidebar-foot">
    <b><?= e(APP_FULL) ?></b><br>
    Versi <?= e(APP_VERSION) ?> &middot; <?= date('Y') ?>
  </div>
</aside>
<div class="sidebar-backdrop" id="backdrop"></div>

<div class="main">
  <header class="topbar">
    <button class="btn btn-sm btn-light d-lg-none" id="toggleSidebar" aria-label="Buka menu">
      <i class="bi bi-list"></i>
    </button>

    <div class="page-head me-auto">
      <h1><?= e($pageTitle) ?></h1>
      <?php if (!empty($pageSubtitle)): ?>
        <p class="sub"><?= e($pageSubtitle) ?></p>
      <?php endif; ?>
    </div>

    <div class="dropdown">
      <button class="user-chip" data-bs-toggle="dropdown" aria-expanded="false">
        <span class="avatar"><?= e(strtoupper(substr($user['nama'], 0, 1))) ?></span>
        <span class="d-none d-sm-block text-start">
          <span class="d-block nm"><?= e($user['nama']) ?></span>
          <span class="d-block rl"><?= e(ucfirst($user['role'])) ?></span>
        </span>
        <i class="bi bi-chevron-down" style="font-size:.7rem;color:var(--ink-300)"></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><span class="dropdown-item-text" style="font-size:.75rem;color:var(--ink-500)">
          Masuk sebagai <b style="color:var(--ink-900)"><?= e($user['username']) ?></b>
        </span></li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item" href="pengaturan.php">
            <i class="bi bi-gear me-2"></i>Pengaturan akun
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item text-danger" href="logout.php"
             onclick="return confirm('Keluar dari sistem?')">
            <i class="bi bi-box-arrow-right me-2"></i>Keluar
          </a>
        </li>
      </ul>
    </div>
  </header>

  <div class="content">
    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'info-circle' ?> me-1"></i>
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Tutup"></button>
      </div>
    <?php endforeach; ?>
