<?php
/**
 * Pengaturan — ganti password & kelola akun petugas
 */
require_once __DIR__ . '/config.php';
requireLogin();

$user   = currentUser();
$errors = [];
$tab    = get('tab', 'akun');

/* ---------- ganti password sendiri ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('aksi') === 'password') {
    verifyCsrf();
    $lama    = post('lama');
    $baru    = post('baru');
    $ulang   = post('ulang');

    $row = db()->prepare('SELECT password FROM users WHERE id = :id');
    $row->execute([':id' => $user['id']]);
    $hash = (string) $row->fetchColumn();

    if (!password_verify($lama, $hash)) {
        $errors[] = 'Password lama tidak sesuai.';
    }
    if (strlen($baru) < 6) {
        $errors[] = 'Password baru minimal 6 karakter.';
    }
    if ($baru !== $ulang) {
        $errors[] = 'Konfirmasi password tidak sama.';
    }

    if (!$errors) {
        db()->prepare('UPDATE users SET password = :p WHERE id = :id')
            ->execute([':p' => password_hash($baru, PASSWORD_DEFAULT), ':id' => $user['id']]);
        setFlash('success', 'Password berhasil diganti. Gunakan password baru saat login berikutnya.');
        redirect('pengaturan.php');
    }
}

/* ---------- kelola akun (admin saja) ---------- */
$isAdmin = ($user['role'] ?? '') === 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('aksi') === 'petugas') {
    verifyCsrf();
    if (!$isAdmin) {
        setFlash('error', 'Hanya administrator yang boleh mengelola akun petugas.');
        redirect('pengaturan.php');
    }

    $aksi    = post('sub');
    $pid     = (int) post('id');

    if ($aksi === 'hapus') {
        if ($pid === (int) $user['id']) {
            setFlash('error', 'Anda tidak bisa menghapus akun sendiri.');
        } else {
            db()->prepare('DELETE FROM users WHERE id = :id')->execute([':id' => $pid]);
            setFlash('success', 'Akun petugas dihapus.');
        }
        redirect('pengaturan.php?tab=petugas');
    }

    if ($aksi === 'simpan') {
        $nama  = post('nama');
        $uname = strtolower(trim(post('username')));
        $pass  = post('password');
        $role  = post('role') === 'admin' ? 'admin' : 'petugas';

        if ($nama === '' || $uname === '') {
            $errors[] = 'Nama dan username wajib diisi.';
        } elseif (!preg_match('/^[a-z0-9_.]{3,20}$/', $uname)) {
            $errors[] = 'Username hanya huruf kecil, angka, titik, underscore (3-20 karakter).';
        }

        if ($pid === 0 && strlen($pass) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        }

        if (!$errors) {
            $pdo = db();
            if ($pid > 0) {
                $sql  = 'UPDATE users SET nama = :n, username = :u, role = :r';
                $bind = [':n' => $nama, ':u' => $uname, ':r' => $role, ':id' => $pid];
                if ($pass !== '') {
                    $sql .= ', password = :p';
                    $bind[':p'] = password_hash($pass, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id = :id';
                try {
                    $pdo->prepare($sql)->execute($bind);
                    setFlash('success', 'Akun petugas diperbarui.');
                } catch (PDOException $ex) {
                    $errors[] = 'Username sudah dipakai akun lain.';
                }
            } else {
                try {
                    $pdo->prepare('INSERT INTO users (username, password, nama, role) VALUES (:u, :p, :n, :r)')
                        ->execute([':u' => $uname, ':p' => password_hash($pass, PASSWORD_DEFAULT),
                                   ':n' => $nama, ':r' => $role]);
                    setFlash('success', 'Akun petugas baru ditambahkan.');
                } catch (PDOException $ex) {
                    $errors[] = 'Username sudah dipakai akun lain.';
                }
            }
            if (!$errors) {
                redirect('pengaturan.php?tab=petugas');
            }
        }
        $tab = 'petugas';
    }
}

$petugasList = $isAdmin ? db()->query('SELECT * FROM users ORDER BY role, nama')->fetchAll() : [];

$active       = 'pengaturan';
$pageTitle    = 'Pengaturan';
$pageSubtitle = 'Akun & keamanan';

require __DIR__ . '/partials/header.php';
?>

<ul class="nav nav-pills mb-3 gap-2">
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'akun' ? 'active btn-sk' : 'btn-outline-sk' ?>" href="pengaturan.php">
      <i class="bi bi-shield-lock me-1"></i>Ganti Password
    </a>
  </li>
  <?php if ($isAdmin): ?>
  <li class="nav-item">
    <a class="nav-link <?= $tab === 'petugas' ? 'active btn-sk' : 'btn-outline-sk' ?>" href="pengaturan.php?tab=petugas">
      <i class="bi bi-people me-1"></i>Kelola Petugas
    </a>
  </li>
  <?php endif; ?>
</ul>

<?php if ($errors): ?>
  <div class="alert alert-danger">
    <?php foreach ($errors as $m): ?><div><i class="bi bi-exclamation-circle me-1"></i><?= e($m) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($tab === 'akun'): ?>
  <div class="row g-3">
    <div class="col-12 col-lg-5">
      <div class="card">
        <div class="card-header"><i class="bi bi-key me-1"></i>Ganti Password</div>
        <div class="card-body">
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="aksi" value="password">
            <div class="mb-3">
              <label class="form-label" for="lama">Password Lama</label>
              <input type="password" class="form-control" id="lama" name="lama" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="baru">Password Baru</label>
              <input type="password" class="form-control" id="baru" name="baru" minlength="6" required>
              <div class="form-text">Minimal 6 karakter.</div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="ulang">Ulangi Password Baru</label>
              <input type="password" class="form-control" id="ulang" name="ulang" minlength="6" required>
            </div>
            <button class="btn btn-sk" type="submit"><i class="bi bi-check-lg me-1"></i>Simpan</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-7">
      <div class="card h-100">
        <div class="card-header"><i class="bi bi-person-badge me-1"></i>Akun Anda</div>
        <div class="card-body">
          <table class="table table-sm mb-0">
            <tr><th style="width:130px">Nama</th><td><?= e($user['nama']) ?></td></tr>
            <tr><th>Username</th><td><code><?= e($user['username']) ?></code></td></tr>
            <tr><th>Role</th><td>
              <span class="badge <?= $isAdmin ? 'text-bg-danger' : 'text-bg-secondary' ?>">
                <?= e(ucfirst($user['role'])) ?>
              </span>
            </td></tr>
            <tr><th>Login terakhir</th><td class="text-muted">Sesi saat ini aktif</td></tr>
          </table>
          <div class="alert alert-warning mt-3 mb-0" style="font-size:.8rem">
            <i class="bi bi-info-circle me-1"></i>
            Ganti password default (<code>admin123</code> / <code>petugas123</code>) sebelum aplikasi dipakai sungguhan.
          </div>
        </div>
      </div>
    </div>
  </div>

<?php else: ?>
  <div class="row g-3">
    <div class="col-12 col-xl-4">
      <div class="card">
        <div class="card-header"><i class="bi bi-person-plus me-1"></i>Tambah Akun Petugas</div>
        <div class="card-body">
          <form method="post">
            <?= csrfField() ?>
            <input type="hidden" name="aksi" value="petugas">
            <input type="hidden" name="sub" value="simpan">
            <input type="hidden" name="id" value="0">
            <div class="mb-3">
              <label class="form-label" for="nama">Nama Lengkap</label>
              <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="username">Username</label>
              <input type="text" class="form-control" id="username" name="username"
                     pattern="[a-z0-9_.]{3,20}" required>
              <div class="form-text">Huruf kecil, angka, titik, underscore.</div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <input type="text" class="form-control" id="password" name="password" minlength="6" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="role">Role</label>
              <select class="form-select" id="role" name="role">
                <option value="petugas">Petugas</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
            <button class="btn btn-sk w-100" type="submit"><i class="bi bi-save me-1"></i>Simpan Akun</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-8">
      <div class="card">
        <div class="card-header">
          <i class="bi bi-people me-1"></i>Daftar Akun
          <span class="badge badge-soft ms-1"><?= count($petugasList) ?></span>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead>
              <tr><th>Nama</th><th>Username</th><th>Role</th><th>Dibuat</th><th style="width:80px"></th></tr>
            </thead>
            <tbody>
              <?php foreach ($petugasList as $u): ?>
                <tr>
                  <td class="fw-semibold"><?= e($u['nama']) ?>
                    <?php if ((int) $u['id'] === (int) $user['id']): ?>
                      <span class="badge text-bg-success ms-1" style="font-size:.6rem">Anda</span>
                    <?php endif; ?>
                  </td>
                  <td><code><?= e($u['username']) ?></code></td>
                  <td>
                    <span class="badge <?= $u['role'] === 'admin' ? 'text-bg-danger' : 'text-bg-secondary' ?>">
                      <?= e(ucfirst($u['role'])) ?>
                    </span>
                  </td>
                  <td class="text-muted" style="font-size:.78rem"><?= e(tanggalIndo(substr((string) $u['dibuat'], 0, 10))) ?></td>
                  <td class="text-end">
                    <?php if ((int) $u['id'] !== (int) $user['id']): ?>
                      <form method="post" onsubmit="return confirm('Hapus akun <?= e($u['nama']) ?>?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="aksi" value="petugas">
                        <input type="hidden" name="sub" value="hapus">
                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
