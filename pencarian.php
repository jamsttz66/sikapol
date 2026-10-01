<?php
/**
 * Pencarian Pasien — cari nama, lalu lihat seluruh riwayat kunjungannya
 */
require_once __DIR__ . '/config.php';
requireLogin();

$q       = get('q');
$hasil   = $q !== '' ? daftarPasien($q) : [];
$detail  = null;
$riwayat = [];

/* kalau hasilnya tepat satu, langsung tampilkan detailnya */
if (count($hasil) === 1) {
    $detail  = $hasil[0];
    $riwayat = riwayatPasien((int) $detail['id']);
}

/* atau kalau ada parameter id */
$idp = (int) get('id', '0');
if ($idp > 0) {
    $detail = findPasien($idp);
    if ($detail) {
        $riwayat = riwayatPasien($idp);
    }
}

$active       = 'pencarian';
$pageTitle    = 'Pencarian Pasien';
$pageSubtitle = 'Cari pasien berdasarkan nama atau kamar';

require __DIR__ . '/partials/header.php';
?>

<div class="row justify-content-center mb-3">
  <div class="col-12 col-lg-8">
    <div class="card">
      <div class="card-body">
        <form method="get" class="d-flex gap-2">
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control form-control-lg" name="q" value="<?= e($q) ?>"
                   placeholder="Ketik nama pasien atau kamar/blok..." autofocus autocomplete="off"
                   style="font-size:.95rem">
            <button class="btn btn-sk px-4" type="submit">Cari</button>
          </div>
        </form>
        <div class="form-text mt-2">
          Contoh: <a href="pencarian.php?q=Ahmad" class="text-decoration-none">Ahmad</a>,
          <a href="pencarian.php?q=Blok" class="text-decoration-none">Blok</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($q === '' && $idp === 0): ?>
  <div class="card">
    <div class="empty-state">
      <i class="bi bi-person-search"></i>
      Masukkan nama pasien untuk mencari dan melihat seluruh riwayat kunjungannya.
    </div>
  </div>

<?php elseif (!$detail && !$hasil): ?>
  <div class="card">
    <div class="empty-state">
      <i class="bi bi-emoji-frown"></i>
      Tidak ada pasien dengan kata kunci <b><?= e($q) ?></b>.
      <div class="mt-2">
        <a href="pasien.php" class="btn btn-sm btn-outline-sk">Lihat semua pasien</a>
        <a href="kunjungan.php" class="btn btn-sm btn-sk">Catat kunjungan baru</a>
      </div>
    </div>
  </div>

<?php elseif ($detail): ?>
  <!-- DETAIL SATU PASIEN -->
  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body text-center">
          <div class="mx-auto mb-3" style="width:72px;height:72px;border-radius:50%;
               background:#e7f5f1;color:#0d6e5f;display:grid;place-items:center;
               font-size:1.8rem;font-weight:800">
            <?= e(strtoupper(substr($detail['nama'], 0, 1))) ?>
          </div>
          <h5 class="fw-bold mb-1" style="color:#12312c"><?= e($detail['nama']) ?></h5>
          <span class="badge badge-soft mb-3"><?= e($detail['kamar']) ?></span>

          <div class="row text-center border-top pt-3 g-0">
            <div class="col-6 border-end">
              <div class="fw-bold fs-5" style="color:#0d6e5f"><?= count($riwayat) ?></div>
              <div class="text-muted" style="font-size:.72rem">Total Kunjungan</div>
            </div>
            <div class="col-6">
              <div class="fw-bold" style="font-size:.9rem;color:#12312c">
                <?= $riwayat ? e(tanggalIndo($riwayat[0]['tanggal'])) : '-' ?>
              </div>
              <div class="text-muted" style="font-size:.72rem">Kunjungan Terakhir</div>
            </div>
          </div>

          <div class="d-grid gap-2 mt-3">
            <a href="kunjungan.php" class="btn btn-sm btn-sk">
              <i class="bi bi-plus-lg me-1"></i>Catat Kunjungan Baru
            </a>
            <a href="riwayat.php?cari=<?= urlencode($detail['nama']) ?>" class="btn btn-sm btn-outline-sk">
              <i class="bi bi-clock-history me-1"></i>Riwayat Lengkap
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-header">
          <i class="bi bi-clock-history me-1"></i>Riwayat Kunjungan
          <span class="badge badge-soft ms-1"><?= count($riwayat) ?></span>
        </div>

        <?php if (!$riwayat): ?>
          <div class="empty-state">
            <i class="bi bi-inbox"></i>Pasien ini belum memiliki riwayat kunjungan.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead>
                <tr>
                  <th style="width:120px">Tanggal</th>
                  <th>Keluhan</th>
                  <th>Diagnosis</th>
                  <th>Tindakan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($riwayat as $k): ?>
                  <tr>
                    <td class="text-nowrap fw-semibold"><?= e(tanggalIndo($k['tanggal'])) ?></td>
                    <td><?= e($k['keluhan']) ?></td>
                    <td><?= e($k['diagnosis'] ?: '-') ?></td>
                    <td><?= e($k['tindakan'] ?: '-') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- HASIL PENCARIAN BANYAK -->
  <div class="card">
    <div class="card-header">
      <i class="bi bi-list-ul me-1"></i>Ditemukan <?= count($hasil) ?> pasien
      untuk kata kunci <b><?= e($q) ?></b>
    </div>
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Nama Pasien</th>
            <th>Kamar / Blok</th>
            <th class="text-center">Total Kunjungan</th>
            <th>Kunjungan Terakhir</th>
            <th style="width:110px"></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($hasil as $p): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <span class="avatar"><?= e(strtoupper(substr($p['nama'], 0, 1))) ?></span>
                  <span class="fw-semibold"><?= e($p['nama']) ?></span>
                </div>
              </td>
              <td><span class="badge badge-soft"><?= e($p['kamar']) ?></span></td>
              <td class="text-center">
                <span class="badge bg-secondary-subtle text-secondary-emphasis">
                  <?= (int) $p['total_kunjungan'] ?>×
                </span>
              </td>
              <td class="text-muted">
                <?= $p['kunjungan_terakhir'] ? e(tanggalIndo($p['kunjungan_terakhir'])) : '<span class="text-muted">Belum ada</span>' ?>
              </td>
              <td class="text-end">
                <a href="pencarian.php?id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-sk">
                  <i class="bi bi-eye me-1"></i>Detail
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/partials/footer.php'; ?>
