/**
 * SIKAPOL — live preview riwayat pasien pada form kunjungan.
 */
(function () {
  'use strict';

  var input   = document.getElementById('nama');
  var panel   = document.getElementById('isiRiwayat');
  var counter = document.getElementById('jumlahRiwayat');
  if (!input || !panel) return;

  var timer = null;
  var lastQuery = null;

  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function render(data) {
    if (!data || !data.items || data.items.length === 0) {
      counter.textContent = '0';
      panel.innerHTML = '<div class="empty-state"><i class="bi bi-inbox"></i>' +
        (data && data.pasien
          ? 'Pasien <b>' + esc(data.pasien) + '</b> belum memiliki riwayat kunjungan.'
          : 'Pasien belum pernah tercatat. Kunjungan ini akan menjadi yang pertama.') +
        '</div>';
      return;
    }

    counter.textContent = data.items.length;

    var html = '';
    if (data.pasien) {
      html += '<div class="d-flex align-items-center gap-2 mb-3">' +
        '<span class="avatar">' + esc(data.pasien.charAt(0).toUpperCase()) + '</span>' +
        '<div class="lh-sm"><div class="fw-semibold">' + esc(data.pasien) + '</div>' +
        '<div class="text-muted" style="font-size:.72rem">' + esc(data.kamar || '') +
        ' &middot; ' + data.items.length + ' kunjungan ditemukan</div></div></div>';
    }

    html += '<div class="vstack gap-2">';
    data.items.forEach(function (k) {
      html += '<div class="border rounded-3 p-2 px-3" style="font-size:.82rem">' +
        '<div class="d-flex justify-content-between align-items-start gap-2">' +
          '<span class="fw-semibold">' + esc(k.tanggal) + '</span>' +
          (k.kamar ? '<span class="badge badge-soft">' + esc(k.kamar) + '</span>' : '') +
        '</div>' +
        '<div class="text-muted mt-1"><b>Keluhan:</b> ' + esc(k.keluhan || '-') + '</div>' +
        (k.diagnosis ? '<div class="text-muted"><b>Diagnosis:</b> ' + esc(k.diagnosis) + '</div>' : '') +
        (k.tindakan ? '<div class="text-muted"><b>Tindakan:</b> ' + esc(k.tindakan) + '</div>' : '') +
      '</div>';
    });
    html += '</div>';
    panel.innerHTML = html;
  }

  function load(q) {
    if (q === lastQuery) return;
    lastQuery = q;

    if (q.length < 2) {
      counter.textContent = '-';
      panel.innerHTML = '<div class="empty-state" style="padding:2rem 1rem">' +
        '<i class="bi bi-person-bounding-box"></i>' +
        '<div class="fw-semibold mb-1" style="color:#33514b">Riwayat pasien muncul di sini</div>' +
        '<div style="font-size:.79rem;line-height:1.6">Mulai ketik nama pasien, riwayat kunjungan ' +
        'sebelumnya akan otomatis ditampilkan \u2014 supaya tidak terjadi pencatatan ganda.</div></div>';
      return;
    }

    panel.innerHTML = '<div class="text-center py-4 text-muted" style="font-size:.82rem">' +
      '<span class="spinner-border spinner-border-sm me-2"></span>Memuat riwayat...</div>';

    fetch('api-riwayat.php?q=' + encodeURIComponent(q))
      .then(function (r) { return r.json(); })
      .then(render)
      .catch(function () {
        panel.innerHTML = '<div class="empty-state"><i class="bi bi-x-circle"></i>' +
          'Gagal memuat riwayat. Coba lagi.</div>';
      });
  }

  input.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(function () { load(input.value.trim()); }, 320);
  });

  input.addEventListener('change', function () { load(input.value.trim()); });
})();
