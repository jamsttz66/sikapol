<?php
require_once __DIR__ . '/config.php';

$active       = 'dashboard';
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan aktivitas poliklinik hari ini';

$stat   = statistik();
$recent = kunjunganTerbaru(6);
$user   = currentUser();

/* kartu statistik dengan tone warna sendiri */
$statCards = [
    [
        'label' => 'Total Pasien',
        'value' => $stat['pasien'],
        'icon'  => 'people-fill',
        'tone'  => '#0d625a',
        'bg'    => '#e3f5f1',
        'hint'  => '+' . $stat['pasien_bulan'] . ' bulan ini',
    ],
    [
        'label' => 'Total Kunjungan',
        'value' => $stat['kunjungan'],
        'icon'  => 'clipboard2-pulse-fill',
        'tone'  => '#0284c7',
        'bg'    => '#e4f2fb',
        'hint'  => '+' . $stat['kunjungan_bulan'] . ' bulan ini',
    ],
    [
        'label' => 'Kunjungan Bulan Ini',
        'value' => $stat['kunjungan_bulan'],
        'icon'  => 'calendar2-check-fill',
        'tone'  => '#d97706',
        'bg'    => '#fdf3e4',
        'hint'  => bulanIndo(date('Y-m')),
    ],
    [
        'label' => 'Rata-rata / Pasien',
        'value' => $stat['pasien'] > 0 ? round($stat['kunjungan'] / $stat['pasien'], 1) : 0,
        'icon'  => 'graph-up-arrow',
        'tone'  => '#7c3aed',
        'bg'    => '#f1ebfe',
        'hint'  => 'kunjungan per pasien',
    ],
];

require __DIR__ . '/partials/header.php';
?>

<!-- ============ STAT CARDS ============ -->
<div class="row g-3 mb-3">
  <?php foreach ($statCards as $card): ?>
    <div class="col-12 col-sm-6 col-xl-3">
      <div class="stat" style="--tone:<?= e($card['tone']) ?>;--tone-bg:<?= e($card['bg']) ?>">
        <div class="ico"><i class="bi bi-<?= e($card['icon']) ?>"></i></div>
        <div class="val"><?= number_format((float) $card['value'], 0, ',', '.') ?></div>
        <div class="lbl"><?= e($card['label']) ?></div>
        <div class="hint"><?= e($card['hint']) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="row g-3">
  <!-- ============ KUNJUNGAN TERBARU ============ -->
  <div class="col-12 col-xl-8">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-clock-history"></i>
        <span>Kunjungan Terbaru</span>
        <span class="badge badge-soft neutral ms-1"><?= count($recent) ?></span>
        <a href="riwayat.php" class="btn btn-sm btn-outline-sk ms-auto">
          Lihat semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>

      <?php if (!$recent): ?>
        <div class="empty-state">
          <i class="bi bi-inbox"></i>
          <div class="fw-semibold mb-1" style="color:var(--ink-700)">Belum ada kunjungan</div>
          <div class="mb-3">Catat kunjungan pertama untuk memulai.</div>
          <a href="kunjungan.php" class="btn btn-sm btn-sk">
            <i class="bi bi-plus-lg me-1"></i>Catat Kunjungan
          </a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead>
              <tr>
                <th style="width:112px">Tanggal</th>
                <th>Pasien</th>
                <th class="d-none d-md-table-cell">Kamar / Blok</th>
                <th class="d-none d-lg-table-cell">Keluhan</th>
                <th class="d-none d-xl-table-cell">Tindakan</th>
                <th style="width:56px"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent as $row): ?>
                <tr>
                  <td class="text-nowrap fw-semibold"><?= e(tanggalIndo($row['tanggal'])) ?></td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span class="avatar avatar-sm"><?= e(strtoupper(substr($row['nama'], 0, 1))) ?></span>
                      <div class="lh-sm">
                        <a href="pencarian.php?q=<?= urlencode($row['nama']) ?>"
                           class="fw-semibold" style="color:var(--ink-900)"><?= e($row['nama']) ?></a>
                        <div class="d-md-none" style="font-size:.7rem;color:var(--ink-500)">
                          <?= e($row['kamar']) ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="badge badge-soft"><?= e($row['kamar']) ?></span>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <div class="clamp-2" style="max-width:220px" title="<?= e($row['keluhan']) ?>"><?= e($row['keluhan']) ?></div>
                  </td>
                  <td class="d-none d-xl-table-cell" style="color:var(--ink-500)">
                    <div class="clamp-2" style="max-width:190px" title="<?= e($row['tindakan']) ?>"><?= e($row['tindakan'] ?: '—') ?></div>
                  </td>
                  <td class="text-end">
                    <a href="kunjungan.php?edit=<?= (int) $row['id'] ?>"
                       class="btn btn-sm btn-light" title="Ubah">
                      <i class="bi bi-pencil"></i>
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

  <!-- ============ SIDEBAR KANAN ============ -->
  <div class="col-12 col-xl-4">
    <!-- sambutan -->
    <div class="card mb-3" style="background:linear-gradient(150deg,#0a4a43,#06322e);border-color:transparent">
      <div class="card-body text-white">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="avatar avatar-lg" style="background:rgba(255,255,255,.16);color:#fff">
            <?= e(strtoupper(substr($user['nama'], 0, 1))) ?>
          </span>
          <div>
            <div style="font-size:.68rem;letter-spacing:.12em;text-transform:uppercase;color:#7fb5ad;font-weight:700">
              Selamat bertugas
            </div>
            <div class="fw-bold" style="font-size:1.05rem;color:#fff"><?= e($user['nama']) ?></div>
            <div style="font-size:.73rem;color:#8fc4bc"><?= e(ucfirst($user['role'])) ?></div>
          </div>
        </div>
        <div class="pt-3" style="border-top:1px solid rgba(255,255,255,.14)">
          <div style="font-size:.72rem;color:#8fc4bc">Hari ini</div>
          <div class="fw-bold" style="font-size:.92rem;color:#fff"><?= e(hariIndo(date('Y-m-d'))) ?>, <?= e(tanggalIndo(date('Y-m-d'))) ?></div>
        </div>
      </div>
    </div>

    <!-- aksi cepat -->
    <div class="card mb-3">
      <div class="card-header"><i class="bi bi-lightning-charge-fill"></i><span>Aksi Cepat</span></div>
      <div class="card-body d-grid gap-2">
        <a href="kunjungan.php" class="btn btn-sk">
          <i class="bi bi-plus-circle me-1"></i>Catat Kunjungan Baru
        </a>
        <a href="pencarian.php" class="btn btn-outline-sk">
          <i class="bi bi-search me-1"></i>Cari Pasien
        </a>
        <a href="pasien.php" class="btn btn-outline-sk">
          <i class="bi bi-person-plus me-1"></i>Kelola Data Pasien
        </a>
      </div>
    </div>

    <!-- info sistem -->
    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle"></i><span>Tentang Sistem</span></div>
      <div class="card-body">
        <p style="font-size:.82rem;color:var(--ink-500);line-height:1.6" class="mb-3">
          <b style="color:var(--ink-900)"><?= e(APP_NAME) ?></b> mencatat kunjungan
          pasien / WBP ke poliklinik, lengkap dengan keluhan, diagnosis, dan tindakan.
        </p>
        <div class="d-grid gap-2" style="font-size:.79rem">
          <div class="d-flex align-items-center gap-2" style="color:var(--ink-700)">
            <i class="bi bi-check-circle-fill" style="color:var(--ok)"></i>Data tersimpan otomatis
          </div>
          <div class="d-flex align-items-center gap-2" style="color:var(--ink-700)">
            <i class="bi bi-check-circle-fill" style="color:var(--ok)"></i>Riwayat lengkap per pasien
          </div>
          <div class="d-flex align-items-center gap-2" style="color:var(--ink-700)">
            <i class="bi bi-check-circle-fill" style="color:var(--ok)"></i>Pencarian cepat
          </div>
          <div class="d-flex align-items-center gap-2" style="color:var(--ink-700)">
            <i class="bi bi-check-circle-fill" style="color:var(--ok)"></i>Cetak riwayat kunjungan
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
