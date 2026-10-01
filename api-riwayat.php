<?php
/**
 * API: riwayat kunjungan berdasarkan nama pasien (untuk live preview).
 */
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!currentUser()) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

$q = get('q');

if (mb_strlen($q) < 2) {
    echo json_encode(['items' => [], 'pasien' => null, 'kamar' => null]);
    exit;
}

$stmt = db()->prepare(
    'SELECT id, nama, kamar FROM pasien
     WHERE nama LIKE :q
     ORDER BY (nama = :exact) DESC, nama COLLATE NOCASE ASC
     LIMIT 1'
);
$stmt->execute([':q' => '%' . $q . '%', ':exact' => $q]);
$pasien = $stmt->fetch() ?: null;

if (!$pasien) {
    echo json_encode(['items' => [], 'pasien' => null, 'kamar' => null]);
    exit;
}

$items = [];
foreach (riwayatPasien((int) $pasien['id']) as $k) {
    $items[] = [
        'id'        => (int) $k['id'],
        'tanggal'   => tanggalIndo($k['tanggal']),
        'kamar'     => $k['kamar'],
        'keluhan'   => $k['keluhan'],
        'diagnosis' => $k['diagnosis'],
        'tindakan'  => $k['tindakan'],
    ];
}

echo json_encode([
    'pasien' => $pasien['nama'],
    'kamar'  => $pasien['kamar'],
    'items'  => $items,
], JSON_UNESCAPED_UNICODE);
