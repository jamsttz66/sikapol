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
<title>Masuk &middot; <?= e(APP_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root{--sk-primary:#0d6e5f;--sk-primary-dark:#0a5a4d}
  body{
    min-height:100vh;margin:0;
    background:
      radial-gradient(1000px 500px at 12% 10%, rgba(34,197,94,.20), transparent 60%),
      radial-gradient(900px 500px at 88% 90%, rgba(14,165,233,.18), transparent 60%),
      #0f2d2a;
    display:grid;place-items:center;padding:1.25rem;
    font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
  }
  .login-wrap{width:100%;max-width:930px;display:grid;grid-template-columns:1.05fr .95fr;
    background:#fff;border-radius:18px;overflow:hidden;box-shadow:0 28px 70px rgba(0,0,0,.35)}
  .login-side{
    background:linear-gradient(160deg,var(--sk-primary),#0f2d2a);
    color:#dff3ef;padding:2.4rem 2rem;display:flex;flex-direction:column;justify-content:space-between;
  }
  .login-side .logo{
    width:52px;height:52px;border-radius:14px;background:linear-gradient(140deg,#22c55e,#0ea5e9);
    display:grid;place-items:center;color:#062b25;font-size:1.6rem;margin-bottom:1.3rem;
  }
  .login-side h1{font-size:1.6rem;font-weight:800;color:#fff;letter-spacing:-.02em;line-height:1.15}
  .login-side p{font-size:.85rem;color:#a9cdc7;margin:.7rem 0 0}
  .login-side ul{list-style:none;padding:0;margin:1.6rem 0 0;font-size:.82rem}
  .login-side li{display:flex;gap:.55rem;align-items:flex-start;margin-bottom:.55rem;color:#cfe8e3}
  .login-side li i{color:#22c55e;margin-top:.15rem}
  .login-side .foot{font-size:.7rem;color:#7fada5;border-top:1px solid rgba(255,255,255,.12);padding-top:1rem}
  .login-form{padding:2.4rem 2rem}
  .login-form h2{font-size:1.2rem;font-weight:700;color:#12312c}
  .login-form .sub{font-size:.8rem;color:#7b8c96;margin-bottom:1.6rem}
  .form-label{font-size:.82rem;font-weight:600;color:#294a44}
  .form-control{font-size:.88rem;border-color:#d6e0e6;padding:.6rem .8rem}
  .form-control:focus{border-color:var(--sk-primary);box-shadow:0 0 0 .18rem rgba(13,110,95,.14)}
  .input-group-text{background:#f6f9fa;border-color:#d6e0e6;color:#5d7280}
  .btn-sk{background:var(--sk-primary);border-color:var(--sk-primary);color:#fff;font-weight:600;padding:.62rem 1rem}
  .btn-sk:hover{background:var(--sk-primary-dark);border-color:var(--sk-primary-dark);color:#fff}
  .demo-box{background:#f6f9fa;border:1px dashed #cfdde4;border-radius:10px;
    padding:.7rem .85rem;font-size:.75rem;color:#5d7280;margin-top:1.3rem}
  .demo-box code{background:#fff;border:1px solid #e3e9ee;border-radius:5px;padding:.05rem .35rem;color:#0d6e5f}
  @media (max-width:820px){
    .login-wrap{grid-template-columns:1fr}
    .login-side{padding:1.8rem 1.5rem}
    .login-side ul{display:none}
    .login-form{padding:1.8rem 1.5rem}
  }
</style>
</head>
<body>

<div class="login-wrap">
  <div class="login-side">
    <div>
      <div class="logo"><i class="bi bi-heart-pulse-fill"></i></div>
      <h1><?= e(APP_NAME) ?></h1>
      <p><?= e(APP_FULL) ?><br><span style="color:#7fada5"><?= e(APP_INSTANSI) ?></span></p>
      <ul>
        <li><i class="bi bi-check-circle-fill"></i><span>Pencatatan kunjungan pasien &amp; WBP</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Riwayat kunjungan tersimpan rapi</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Pencarian cepat berdasarkan nama</span></li>
        <li><i class="bi bi-check-circle-fill"></i><span>Dashboard ringkas &amp; mudah dibaca</span></li>
      </ul>
    </div>
    <div class="foot">v<?= e(APP_VERSION) ?> &middot; <?= date('Y') ?></div>
  </div>

  <div class="login-form">
    <h2>Masuk Petugas</h2>
    <p class="sub">Silakan masuk menggunakan akun petugas klinik.</p>

    <?php foreach ($flashes as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> py-2" style="font-size:.82rem">
        <?= e($flash['message']) ?>
      </div>
    <?php endforeach; ?>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger py-2 d-flex align-items-center gap-2" style="font-size:.82rem">
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
          <button class="btn btn-outline-secondary" type="button" id="togglePw" tabindex="-1">
            <i class="bi bi-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-sk w-100">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
      </button>
    </form>

    <div class="demo-box">
      <b><i class="bi bi-info-circle me-1"></i>Akun demo</b><br>
      Admin: <code>admin</code> / <code>admin123</code><br>
      Petugas: <code>petugas</code> / <code>petugas123</code>
    </div>
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
