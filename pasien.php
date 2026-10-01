<?php
/**
 * Data Pasien — daftar & pengelolaan data pasien
 */
require_once __DIR__ . '/config.php';
requireLogin();

$cari   = get('cari');
$errors = [];

/* -------- tambah / ubah pasien -------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $aksi = post('aksi');

    if ($aksi === 'hapus') {
        $id = (int) post('id');
        if ($id > 0) {
            hapusPasien($id);
            setFlash('success', 'Data pasien beserta riwayatnya berhasil dihapus.');
        }
        redirect('pasien.php');
    }

    if ($aksi === 'simpan') {
        $data    = ['nama' => post('nama'), 'kamar' => post('kamar'), 'catatan' => post('catatan')];
        $errors  = validasi($data, ['nama' => 'Nama pasien', 'kamar' => 'Kamar / blok']);
        $editId  = (int) post('id');

        if (!$errors) {
            $pdo = db();
            if ($editId > 0) {
                $stmt = $pdo->prepare('UPDATE pasien SET nama = :n, kamar = :k, catatan = :c WHERE id = :id');
                $stmt->execute([':n' => $data['nama'], ':k' => $data['kamar'], ':c' => $data['catatan'], ':id' => $editId]);
                setFlash('success', 'Data pasien berhasil diperbarui.');
            } else {
                $pdo->prepare('INSERT INTO pasien (nama, kamar, catatan) VALUES (:n, :k, :c)')
                    ->execute([':n' => $data['nama'], ':k' => $data['kamar'], ':c' => $data['catatan']]);
                setFlash('success', 'Pasien baru berhasil ditambahkan.');
            }
            redirect('pasien.php');
        }
    }
}

$editId   = (int) get('edit', '0');
$editData = $editId > 0 ? findPasien($editId) : null;

$rows = daftarPasien($cari);

$active       = 'pasien';
$pageTitle    = 'Data Pasien';
$pageSubtitle = 'Daftar pasien / WBP yang terdata di poliklinik';

require __DIR__ . '/partials/header.php';
?>

<div class="row g-3">
  <div class="col-12 col-xl-4">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-person-<?= $editData ? 'gear' : 'plus' ?> me-1"></i>
        <?= $editData ? 'Ubah Data Pasien' : 'Tambah Pasien Baru' ?>
      </div>
      <div class="card-body">
        <?php if ($errors): ?>
          <div class="alert alert-danger py-2" style="font-size:.82rem">
            <?php foreach ($errors as $m): ?><div><?= e($m) ?></div><?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" novalidate>
          <?= csrfField() ?>
          <input type="hidden" name="aksi" value="simpan">
          <input type="hidden" name="id" value="<?= (int) ($editData['id'] ?? 0) ?>">

          <div class="mb-3">
            <label class="form-label" for="nama">Nama Pasien / WBP <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="nama" name="nama"
                   value="<?= e($editData['nama'] ?? '') ?>" placeholder="Nama lengkap" required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="kamar">Kamar / Blok <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="kamar" name="kamar"
                   value="<?= e($editData['kamar'] ?? '') ?>" placeholder="Contoh: Blok B - Kamar 07" required>
          </div>

          <div class="mb-3">
            <label class="form-label" for="catatan">Catatan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="2"
                      placeholder="Keterangan tambahan (opsional)"><?= e($editData['catatan'] ?? '') ?></textarea>
          </div>

          <div class="d-flex gap-2">
            <button class="btn btn-sk" type="submit">
              <i class="bi bi-save me-1"></i><?= $editData ? 'Simpan Perubahan' : 'Tambah Pasien' ?>
            </button>
            <?php if ($editData): ?>
              <a href="pasien.php" class="btn btn-outline-secondary">Batal</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-header d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <span><i class="bi bi-people me-1"></i>Daftar Pasien
          <span class="badge badge-soft ms-1"><?= number_format(count($rows), 0, ',', '.') ?></span>
        </span>
        <form method="get" class="d-flex gap-2" style="max-width:280px">
          <input type="text" class="form-control form-control-sm" name="cari"
                 value="<?= e($cari) ?>" placeholder="Cari nama / kamar">
          <button class="btn btn-sm btn-outline-sk" type="submit"><i class="bi bi-search"></i></button>
        </form>
      </div>

      <?php if (!$rows): ?>
        <div class="empty-state">
          <i class="bi bi-people"></i>
          <?= $cari !== '' ? 'Tidak ada pasien yang cocok.' : 'Belum ada data pasien.' ?>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th style="width:44px">#</th>
                <th>Nama Pasien / WBP</th>
                <th>Kamar / Blok</th>
                <th class="text-center">Kunjungan</th>
                <th>Terakhir</th>
                <th style="width:100px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rows as $i => $p): ?>
                <tr>
                  <td class="text-muted"><?= $i + 1 ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar"><?= e(strtoupper(substr($p['nama'], 0, 1))) ?></span>
                      <div class="lh-sm">
                        <a href="pencarian.php?id=<?= (int) $p['id'] ?>"
                           class="fw-semibold text-decoration-none"><?= e($p['nama']) ?></a>
                        <?php if (!empty($p['catatan'])): ?>
                          <div class="text-muted" style="font-size:.68rem"><?= e($p['catatan']) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td><span class="badge badge-soft"><?= e($p['kamar']) ?></span></td>
                  <td class="text-center"><?= (int) $p['total_kunjungan'] ?>×</td>
                  <td class="text-muted">
                    <?= $p['kunjungan_terakhir'] ? e(tanggalIndo($p['kunjungan_terakhir'])) : '—' ?>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <a href="pasien.php?edit=<?= (int) $p['id'] ?>"
                         class="btn btn-outline-secondary" title="Ubah"><i class="bi bi-pencil"></i></a>
                      <form method="post" class="d-inline"
                            onsubmit="return confirm('Hapus pasien ini beserta seluruh riwayat kunjungannya?')">
                        <?= csrfField() ?>
                        <input type="hidden" name="aksi" value="hapus">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button class="btn btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
