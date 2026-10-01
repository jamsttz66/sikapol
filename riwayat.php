<?php
/**
 * Riwayat Kunjungan — seluruh kunjungan yang pernah dicatat
 */
require_once __DIR__ . '/config.php';
requireLogin();

$cari    = get('cari');
$dari    = get('dari');
$sampai  = get('sampai');

$sql = 'SELECT k.*, p.nama, p.kamar, u.nama AS petugas
        FROM kunjungan k
        JOIN pasien p ON p.id = k.pasien_id
        LEFT JOIN users u ON u.id = k.petugas_id
        WHERE 1 = 1';
$params = [];

if ($cari !== '') {
    $sql .= ' AND (p.nama LIKE :cari OR p.kamar LIKE :cari OR k.keluhan LIKE :cari OR k.diagnosis LIKE :cari)';
    $params[':cari'] = '%' . $cari . '%';
}
if ($dari !== '') {
    $sql .= ' AND k.tanggal >= :dari';
    $params[':dari'] = $dari;
}
if ($sampai !== '') {
    $sql .= ' AND k.tanggal <= :sampai';
    $params[':sampai'] = $sampai;
}
$sql .= ' ORDER BY k.tanggal DESC, k.id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adaFilter = ($cari !== '' || $dari !== '' || $sampai !== '');

$active       = 'riwayat';
$pageTitle    = 'Riwayat Kunjungan';
$pageSubtitle = 'Seluruh kunjungan yang telah dicatat';

require __DIR__ . '/partials/header.php';
?>

<div class="card mb-3 no-print">
  <div class="card-body">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-12 col-md-4">
        <label class="form-label mb-1" for="cari">Kata kunci</label>
        <input type="text" class="form-control" id="cari" name="cari" value="<?= e($cari) ?>"
               placeholder="Nama pasien, kamar, keluhan...">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" for="dari">Dari tanggal</label>
        <input type="date" class="form-control" id="dari" name="dari" value="<?= e($dari) ?>">
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label mb-1" for="sampai">Sampai tanggal</label>
        <input type="date" class="form-control" id="sampai" name="sampai" value="<?= e($sampai) ?>">
      </div>
      <div class="col-12 col-md-2 d-flex gap-2">
        <button class="btn btn-sk flex-fill" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
        <?php if ($adaFilter): ?>
          <a href="riwayat.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <span><i class="bi bi-clock-history me-1"></i>
      <?= $adaFilter ? 'Hasil Pencarian' : 'Semua Riwayat' ?>
      <span class="badge badge-soft ms-1"><?= number_format(count($rows), 0, ',', '.') ?> data</span>
    </span>
    <div class="d-flex gap-2 no-print">
      <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-printer me-1"></i>Cetak
      </button>
      <a href="kunjungan.php" class="btn btn-sm btn-sk">
        <i class="bi bi-plus-lg me-1"></i>Catat Baru
      </a>
    </div>
  </div>

  <?php if (!$rows): ?>
    <div class="empty-state">
      <i class="bi bi-search"></i>
      <?= $adaFilter ? 'Tidak ada kunjungan yang cocok dengan filter.' : 'Belum ada kunjungan tercatat.' ?>
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0 align-middle">
        <thead>
          <tr>
            <th style="width:44px">#</th>
            <th style="width:120px">Tanggal</th>
            <th>Nama Pasien</th>
            <th>Kamar / Blok</th>
            <th>Keluhan</th>
            <th>Diagnosis</th>
            <th>Tindakan</th>
            <th style="width:90px" class="no-print">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $i => $row): ?>
            <tr>
              <td class="text-muted"><?= $i + 1 ?></td>
              <td class="text-nowrap fw-semibold"><?= e(tanggalIndo($row['tanggal'])) ?></td>
              <td>
                <a href="pencarian.php?q=<?= urlencode($row['nama']) ?>" class="text-decoration-none fw-semibold">
                  <?= e($row['nama']) ?>
                </a>
                <?php if ($row['petugas']): ?>
                  <div class="text-muted" style="font-size:.68rem">dicatat: <?= e($row['petugas']) ?></div>
                <?php endif; ?>
              </td>
              <td><span class="badge badge-soft"><?= e($row['kamar']) ?></span></td>
              <td><?= e($row['keluhan']) ?></td>
              <td><?= e($row['diagnosis'] ?: '-') ?></td>
              <td><?= e($row['tindakan'] ?: '-') ?></td>
              <td class="no-print">
                <div class="btn-group btn-group-sm">
                  <a href="kunjungan.php?edit=<?= (int) $row['id'] ?>"
                     class="btn btn-outline-secondary" title="Ubah"><i class="bi bi-pencil"></i></a>
                  <form method="post" action="kunjungan.php" class="d-inline"
                        onsubmit="return confirm('Hapus kunjungan ini?')">
                    <?= csrfField() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
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

<?php require __DIR__ . '/partials/footer.php'; ?>
