<?php
require_once __DIR__ . '/config.php';

$active       = 'dashboard';
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan data poliklinik';

$stat   = statistik();
$recent = kunjunganTerbaru(5);

$statCards = [
    ['label' => 'Jumlah Pasien',       'value' => $stat['pasien'],          'icon' => 'people-fill',            'class' => 'bg-success-subtle text-success-emphasis', 'note' => '+' . $stat['pasien_bulan'] . ' bulan ini'],
    ['label' => 'Jumlah Kunjungan',    'value' => $stat['kunjungan'],       'icon' => 'clipboard2-pulse-fill',  'class' => 'bg-primary-subtle text-primary-emphasis',  'note' => '+' . $stat['kunjungan_bulan'] . ' bulan ini'],
    ['label' => 'Kunjungan Bulan Ini', 'value' => $stat['kunjungan_bulan'], 'icon' => 'calendar2-check-fill',   'class' => 'bg-info-subtle text-info-emphasis',        'note' => date('F Y')],
    ['label' => 'Pasien Terdaftar',    'value' => $stat['pasien'],          'icon' => 'person-vcard-fill',      'class' => 'bg-warning-subtle text-warning-emphasis',  'note' => 'Total terdata'],
];

require __DIR__ . '/partials/header.php';
?>

<div class="row g-3 mb-3">
  <?php foreach ($statCards as $i => $card): ?>
    <?php if ($i === 3) continue; // hindari duplikat kartu ?>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="card stat-card h-100">
        <div class="card-body d-flex align-items-center gap-3">
          <span class="icon <?= e($card['class']) ?>"><i class="bi bi-<?= e($card['icon']) ?>"></i></span>
          <div>
            <div class="value"><?= number_format((float) $card['value'], 0, ',', '.') ?></div>
            <div class="label"><?= e($card['label']) ?></div>
            <div class="text-muted" style="font-size:.66rem"><?= e($card['note']) ?></div>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-1"></i>Kunjungan Terbaru</span>
        <a href="riwayat.php" class="btn btn-sm btn-outline-sk">Lihat semua</a>
      </div>

      <?php if (!$recent): ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          Belum ada kunjungan tercatat.
          <div class="mt-2">
            <a href="kunjungan.php" class="btn btn-sm btn-sk">
              <i class="bi bi-plus-lg me-1"></i>Catat Kunjungan
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th style="width:110px">Tanggal</th>
                <th>Pasien</th>
                <th class="d-none d-md-table-cell">Kamar / Blok</th>
                <th class="d-none d-lg-table-cell">Keluhan</th>
                <th class="d-none d-lg-table-cell">Diagnosis</th>
                <th style="width:70px"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $row): ?>
                <tr>
                  <td class="text-nowrap"><?= e(tanggalIndo($row['tanggal'])) ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar"><?= e(strtoupper(substr($row['nama'], 0, 1))) ?></span>
                      <div class="lh-sm">
                        <div class="fw-semibold"><?= e($row['nama']) ?></div>
                        <div class="text-muted d-md-none" style="font-size:.7rem"><?= e($row['kamar']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="badge badge-soft"><?= e($row['kamar']) ?></span>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <div class="text-wrap" style="max-width:200px"><?= e($row['keluhan']) ?></div>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <div class="text-wrap" style="max-width:180px"><?= e($row['diagnosis'] ?: '-') ?></div>
                  </td>
                  <td class="text-end">
                    <a href="kunjungan.php?edit=<?= (int) $row['id'] ?>"
                       class="btn btn-sm btn-outline-secondary" title="Detail / edit">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-lightning-charge me-1"></i>Aksi Cepat</div>
      <div class="card-body d-grid gap-2">
        <a href="kunjungan.php" class="btn btn-sk">
          <i class="bi bi-plus-circle me-1"></i>Catat Kunjungan Baru
        </a>
        <a href="pasien.php" class="btn btn-outline-sk">
          <i class="bi bi-person-plus me-1"></i>Data Pasien
        </a>
        <a href="pencarian.php" class="btn btn-outline-sk">
          <i class="bi bi-search me-1"></i>Cari Pasien
        </a>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle me-1"></i>Tentang Sistem</div>
      <div class="card-body" style="font-size:.82rem;color:#5d7280">
        <p class="mb-2"><b><?= e(APP_NAME) ?></b> mencatat kunjungan pasien / WBP ke poliklinik,
        lengkap dengan keluhan, diagnosis, dan tindakan yang diberikan.</p>
        <ul class="mb-0 ps-3">
          <li>Data tersimpan otomatis</li>
          <li>Riwayat per pasien</li>
          <li>Pencarian cepat</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
