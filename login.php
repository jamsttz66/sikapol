<?php
/**
 * Halaman Login Petugas
 */
require_once __DIR__ . '/config.php';

if (currentUser()) {
    redirect('index.php');
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = post('username');
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (attemptLogin($username, $password)) {
        setFlash('success', 'Selamat datang, ' . currentUser()['nama'] . '.');
        redirect('index.php');
    } else {
        $error = 'Username atau password salah.';
    }
}

$flashes = getFlashes();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#06322e">
<title>Masuk &middot; <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root{
    --brand-900:#06322e; --brand-800:#0a4a43; --brand-700:#0d625a;
    --brand-500:#12a08f; --brand-100:#e3f5f1; --brand-50:#f0faf8;
    --ink-900:#0d201d; --ink-500:#6b8480; --line:#e2ebe9;
    --radius:14px; --radius-sm:10px;
  }
  *{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
  body{
    min-height:100vh;margin:0;display:grid;place-items:center;padding:1.25rem;
    font-family:"Plus Jakarta Sans",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
    background:
      radial-gradient(900px 480px at 10% 8%, rgba(18,160,143,.22), transparent 62%),
      radial-gradient(820px 460px at 90% 92%, rgba(8,145,178,.20), transparent 62%),
      linear-gradient(165deg,#06322e,#04211f);
    color:var(--ink-900);
  }
  .login-wrap{
    width:100%;max-width:960px;display:grid;grid-template-columns:1.02fr .98fr;
    background:#fff;border-radius:20px;overflow:hidden;
    box-shadow:0 32px 80px rgba(2,20,18,.42);
  }

  /* ---- panel kiri (branding) ---- */
  .login-side{
    position:relative;padding:2.6rem 2.15rem;display:flex;flex-direction:column;
    justify-content:space-between;color:#dff3ef;overflow:hidden;
    background:linear-gradient(158deg,var(--brand-700),var(--brand-900) 78%);
  }
  .login-side::after{
    content:"";position:absolute;inset:0;pointer-events:none;
    background:radial-gradient(520px 260px at 100% 0%, rgba(18,160,143,.34), transparent 68%);
  }
  .login-side > *{position:relative;z-index:1}
  .login-side .logo{
    width:54px;height:54px;border-radius:15px;margin-bottom:1.35rem;
    background:linear-gradient(142deg,var(--brand-500),#0891b2);
    display:grid;place-items:center;color:#fff;font-size:1.62rem;
    box-shadow:0 8px 22px rgba(18,160,143,.36);
  }
  .login-side h1{
    font-size:1.72rem;font-weight:800;color:#fff;letter-spacing:-.02em;line-height:1.12;margin:0;
  }
  .login-side .tagline{font-size:.86rem;color:#a9cdc7;margin:.75rem 0 0;line-height:1.6}
  .login-side .instansi{
    display:inline-flex;align-items:center;gap:.42rem;margin-top:1.1rem;
    background:rgba(255,255,255,.10);border:1px solid rgba(255,255,255,.16);
    border-radius:99px;padding:.3rem .78rem;font-size:.73rem;font-weight:600;color:#d6f0eb;
  }
  .login-side ul{list-style:none;padding:0;margin:1.75rem 0 0;font-size:.82rem}
  .login-side li{display:flex;gap:.6rem;align-items:flex-start;margin-bottom:.62rem;color:#cfe8e3}
  .login-side li i{color:#4ade9f;margin-top:.12rem;font-size:.9rem}
  .login-side .foot{
    font-size:.69rem;color:#7fada5;border-top:1px solid rgba(255,255,255,.13);
    padding-top:1rem;margin-top:1.6rem;letter-spacing:.02em;
  }

  /* ---- panel kanan (form) ---- */
  .login-form{padding:2.6rem 2.15rem;display:flex;flex-direction:column;justify-content:center}
  .login-form h2{font-size:1.32rem;font-weight:800;letter-spacing:-.015em;margin:0}
  .login-form .sub{font-size:.82rem;color:var(--ink-500);margin:.4rem 0 1.7rem}
  .form-label{font-size:.79rem;font-weight:700;color:#33514b;margin-bottom:.35rem}
  .form-control{
    font-size:.88rem;border-color:#d5e2df;border-radius:var(--radius-sm);
    padding:.6rem .8rem;color:var(--ink-900);
  }
  .form-control::placeholder{color:#9db0ac}
  .form-control:focus{border-color:var(--brand-500);box-shadow:0 0 0 .2rem rgba(18,160,143,.13)}
  .input-group-text{background:#f7fafb;border-color:#d5e2df;color:var(--ink-500)}
  .btn-sk{
    background:var(--brand-700);border-color:var(--brand-700);color:#fff;
    font-weight:700;font-size:.9rem;padding:.66rem 1rem;border-radius:var(--radius-sm);
    box-shadow:0 2px 6px rgba(13,98,90,.24);transition:all .16s;
  }
  .btn-sk:hover,.btn-sk:focus{
    background:var(--brand-800);border-color:var(--brand-800);color:#fff;
    box-shadow:0 7px 18px rgba(13,98,90,.28);transform:translateY(-1px);
  }
  .alert{border-radius:var(--radius-sm);font-size:.82rem;border:1px solid transparent;font-weight:500}
  .alert-danger{background:#fdeeee;border-color:#f6cfcf;color:#a02020}
  .alert-success{background:#eafaf3;border-color:#c3ead9;color:#0a6b4d}
  .demo-box{
    background:var(--brand-50);border:1px dashed #bcded8;border-radius:var(--radius-sm);
    padding:.75rem .9rem;font-size:.75rem;color:#33514b;margin-top:1.5rem;line-height:1.75;
  }
  .demo-box b{color:var(--brand-800)}
  .demo-box code{
    background:#fff;border:1px solid #d5e2df;border-radius:5px;
    padding:.08rem .38rem;color:var(--brand-700);font-weight:600;
  }
  .help-box{
    display:flex;align-items:flex-start;gap:.6rem;margin-top:1.5rem;
    background:#f7fafb;border:1px solid var(--line);border-radius:var(--radius-sm);
    padding:.75rem .9rem;font-size:.76rem;color:var(--ink-500);line-height:1.6;
  }
  .help-box i{color:var(--brand-600);font-size:.95rem;margin-top:.05rem}

  @media (max-width:840px){
    .login-wrap{grid-template-columns:1fr;max-width:460px}
    .login-side{padding:1.9rem 1.6rem}
    .login-side ul{display:none}
    .login-side .foot{margin-top:1.1rem}
    .login-form{padding:2rem 1.6rem}
    .login-side h1{font-size:1.5rem}
  }
</style>
</head>
<body>

<div class="login-wrap">
  <!-- ============ BRANDING ============ -->
  <div class="login-side">
    <div>
      <div class="logo"><i class="bi bi-heart-pulse-fill"></i></div>
      <h1><?= e(APP_NAME) ?></h1>
      <p class="tagline"><?= e(APP_FULL) ?></p>
      <span class="instansi">
        <i class="bi bi-building"></i><?= e(APP_INSTANSI) ?>
      </span>

      <ul>
        <li><i class="bi bi-check-circle-fill"></i><span>Pencatatan kunjungan pasien &amp; WBP</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Riwayat kunjungan tersimpan rapi</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Pencarian cepat berdasarkan nama</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Dashboard ringkas &amp; mudah dibaca</span></li>
      </ul>
    </div>
    <div class="foot">Versi <?= e(APP_VERSION) ?> &middot; <?= date('Y') ?></div>
  </div>

  <!-- ============ FORM ============ -->
  <div class="login-form">
    <h2>Masuk Petugas</h2>
    <p class="sub">Silakan masuk menggunakan akun petugas klinik.</p>

    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> py-2 mb-3">
        <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'info-circle' ?> me-1"></i>
        <?= e($flash['message']) ?>
      </div>
    <?php endforeach; ?>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger py-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i><?= e($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" novalidate>
      <?= csrfField() ?>

      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="username" name="username"
                 value="<?= e($username) ?>" placeholder="Masukkan username" required autofocus>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="password">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password"
                 placeholder="Masukkan password" required>
          <button class="btn btn-outline-secondary" type="button" id="togglePw" tabindex="-1"
                  aria-label="Lihat password">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-sk w-100 mt-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
      </button>
    </form>

    <?php if (isLingkunganDemo()): ?>
      <div class="demo-box">
        <b><i class="bi bi-info-circle me-1"></i>Akun demo</b><br>
        Admin: <code>admin</code> / <code>admin123</code><br>
        Petugas: <code>petugas</code> / <code>petugas123</code><br>
        <span style="color:#8a5a06"><i class="bi bi-exclamation-triangle me-1"></i>Ganti password setelah login pertama.</span>
      </div>
    <?php else: ?>
      <div class="help-box">
        <i class="bi bi-shield-lock"></i>
        <span>Lupa password? Hubungi administrator sistem untuk reset akun.</span>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  document.getElementById('togglePw').addEventListener('click', function () {
    var pw = document.getElementById('password'),
        ic = document.getElementById('eyeIcon');
    var show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    ic.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
  });
</script>
</body>
</html>
