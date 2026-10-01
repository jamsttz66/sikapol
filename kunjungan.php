<?php
/**
 * Pencatatan Kunjungan — form input + edit + hapus
 */
require_once __DIR__ . '/config.php';
requireLogin();

$errors   = [];
$editId   = (int) get('edit', '0');
$editData = $editId > 0 ? findKunjungan($editId) : null;

/* ---------------- PROSES SIMPAN / UPDATE ---------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $aksi = post('aksi');

    if ($aksi === 'hapus') {
        $id = (int) post('id');
        if ($id > 0) {
            hapusKunjungan($id);
            setFlash('success', 'Data kunjungan berhasil dihapus.');
        }
        redirect('kunjungan.php');
    }

    $data = [
        'nama'      => post('nama'),
        'kamar'     => post('kamar'),
        'tanggal'   => post('tanggal'),
        'keluhan'   => post('keluhan'),
        'diagnosis' => post('diagnosis'),
        'tindakan'  => post('tindakan'),
    ];

    $errors = validasi($data, [
        'nama'    => 'Nama pasien',
        'kamar'   => 'Kamar / blok',
        'tanggal' => 'Tanggal kunjungan',
        'keluhan' => 'Keluhan',
    ]);

    if (!$errors) {
        $pasienId = simpanPasien($data['nama'], $data['kamar']);

        if ($aksi === 'update' && (int) post('id') > 0) {
            updateKunjungan((int) post('id'), $data['tanggal'], $data['keluhan'], $data['diagnosis'], $data['tindakan']);
            setFlash('success', 'Kunjungan ' . $data['nama'] . ' berhasil diperbarui.');
        } else {
            simpanKunjungan($pasienId, $data['tanggal'], $data['keluhan'], $data['diagnosis'], $data['tindakan']);
            setFlash('success', 'Kunjungan ' . $data['nama'] . ' berhasil dicatat.');
        }

        redirect('kunjungan.php');
    }
}

/* ---------------- NILAI AWAL FORM ---------------- */
$form = [
    'id'        => $editData['id']        ?? 0,
    'nama'      => $editData['nama']      ?? post('nama'),
    'kamar'     => $editData['kamar']     ?? post('kamar'),
    'tanggal'   => $editData['tanggal']   ?? post('tanggal', date('Y-m-d')),
    'keluhan'   => $editData['keluhan']   ?? post('keluhan'),
    'diagnosis' => $editData['diagnosis'] ?? post('diagnosis'),
    'tindakan'  => $editData['tindakan']  ?? post('tindakan'),
];

$isEdit = (int) $form['id'] > 0;

/* ---------------- DAFTAR SARAN PASIEN ---------------- */
$semuaPasien = daftarPasien();

$active       = 'kunjungan';
$pageTitle    = $isEdit ? 'Ubah Kunjungan' : 'Pencatatan Kunjungan';
$pageSubtitle = $isEdit
    ? 'Perbarui data kunjungan pasien'
    : 'Isi data setiap pasien yang datang ke poliklinik';

require __DIR__ . '/partials/header.php';
?>

<div class="row g-3">
  <div class="col-12 col-xl-7">
    <div class="card">
      <div class="card-header">
        <i class="bi bi-clipboard2-plus me-1"></i>
        <?= $isEdit ? 'Form Ubah Kunjungan' : 'Form Pencatatan Kunjungan' ?>
      </div>
      <div class="card-body">

        <?php if ($errors): ?>
          <div class="alert alert-danger py-2" style="font-size:.83rem">
            <b><i class="bi bi-exclamation-triangle-fill me-1"></i>Periksa kembali:</b>
            <ul class="mb-0 ps-3 mt-1">
              <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="post" novalidate id="formKunjungan">
          <?= csrfField() ?>
          <input type="hidden" name="aksi" value="<?= $isEdit ? 'update' : 'create' ?>">
          <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">

          <div class="row g-3">
            <div class="col-md-7">
              <label class="form-label" for="nama">Nama Pasien / WBP <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
                     id="nama" name="nama" list="daftarPasien" value="<?= e($form['nama']) ?>"
                     placeholder="Contoh: Ahmad Saputra" required autocomplete="off">
              <datalist id="daftarPasien">
                <?php foreach ($semuaPasien as $p): ?>
                  <option value="<?= e($p['nama']) ?>"><?= e($p['kamar']) ?></option>
                <?php endforeach; ?>
              </datalist>
              <div class="form-text">Pilih dari daftar kalau pasien sudah pernah tercatat.</div>
            </div>

            <div class="col-md-5">
              <label class="form-label" for="kamar">Kamar / Blok <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['kamar']) ? 'is-invalid' : '' ?>"
                     id="kamar" name="kamar" value="<?= e($form['kamar']) ?>"
                     placeholder="Contoh: Blok A - Kamar 03" required>
            </div>

            <div class="col-md-5">
              <label class="form-label" for="tanggal">Tanggal Kunjungan <span class="text-danger">*</span></label>
              <input type="date" class="form-control <?= isset($errors['tanggal']) ? 'is-invalid' : '' ?>"
                     id="tanggal" name="tanggal" value="<?= e($form['tanggal']) ?>" required>
            </div>

            <div class="col-12">
              <label class="form-label" for="keluhan">Keluhan <span class="text-danger">*</span></label>
              <textarea class="form-control <?= isset($errors['keluhan']) ? 'is-invalid' : '' ?>"
                        id="keluhan" name="keluhan" rows="3"
                        placeholder="Keluhan yang dirasakan pasien..." required><?= e($form['keluhan']) ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label" for="diagnosis">Diagnosis</label>
              <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2"
                        placeholder="Hasil pemeriksaan / diagnosis..."><?= e($form['diagnosis']) ?></textarea>
            </div>

            <div class="col-12">
              <label class="form-label" for="tindakan">Tindakan / Catatan</label>
              <textarea class="form-control" id="tindakan" name="tindakan" rows="2"
                        placeholder="Obat yang diberikan, tindakan, atau catatan tambahan..."><?= e($form['tindakan']) ?></textarea>
            </div>
          </div>

          <hr class="my-3">

          <div class="d-flex gap-2 flex-wrap">
            <button type="submit" class="btn btn-sk">
              <i class="bi bi-save me-1"></i><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Kunjungan' ?>
            </button>
            <?php if ($isEdit): ?>
              <a href="kunjungan.php" class="btn btn-outline-secondary">Batal</a>
            <?php else: ?>
              <button type="reset" class="btn btn-outline-secondary">Bersihkan</button>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card" id="panelRiwayat">
      <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-clock-history me-1"></i>Kunjungan Terakhir Pasien</span>
        <span class="badge badge-soft" id="jumlahRiwayat">-</span>
      </div>
      <div class="card-body" id="isiRiwayat">
        <div class="empty-state">
          <i class="bi bi-person-bounding-box"></i>
          Ketik atau pilih nama pasien untuk melihat riwayat kunjungannya.
        </div>
      </div>
    </div>
  </div>
</div>

<script src="riwayat-ajax.js"></script>
<?php require __DIR__ . '/partials/footer.php'; ?>
