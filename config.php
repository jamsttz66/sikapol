<?php
/**
 * SIKAPOL — Sistem Informasi Kunjungan Poliklinik
 * Lapas Kelas IIB Polewali
 *
 * Konfigurasi database & helper
 */

declare(strict_types=1);

session_start();

/* ------------------------------------------------------------------
 |  KONFIGURASI
 ------------------------------------------------------------------*/
define('APP_NAME', 'SIKAPOL');
define('APP_FULL', 'Sistem Informasi Kunjungan Poliklinik');
define('APP_INSTANSI', 'Lapas Kelas IIB Polewali');
define('APP_VERSION', '1.2.0');

/** Nama bulan & hari dalam bahasa Indonesia */
const BULAN_INDO = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];
const HARI_INDO = [
    1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu',
];

define('DB_FILE', __DIR__ . '/data/sikapol.sqlite');
define('DATA_DIR', __DIR__ . '/data');

/* Akun petugas default (username => password_hash) */
define('DEFAULT_USERS', [
    'admin'   => ['password' => 'admin123',   'nama' => 'Administrator', 'role' => 'admin'],
    'petugas' => ['password' => 'petugas123', 'nama' => 'Petugas Klinik', 'role' => 'petugas'],
]);

/* ------------------------------------------------------------------
 |  DATABASE
 ------------------------------------------------------------------*/
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (!is_dir(DATA_DIR)) {
        mkdir(DATA_DIR, 0775, true);
    }

    $pdo = new PDO('sqlite:' . DB_FILE, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');

    migrate($pdo);

    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<'SQL'
        CREATE TABLE IF NOT EXISTS users (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            username   TEXT NOT NULL UNIQUE,
            password   TEXT NOT NULL,
            nama       TEXT NOT NULL,
            role       TEXT NOT NULL DEFAULT 'petugas',
            aktif      INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        );

        CREATE TABLE IF NOT EXISTS pasien (
            id         INTEGER PRIMARY KEY AUTOINCREMENT,
            nama       TEXT NOT NULL,
            kamar      TEXT NOT NULL,
            catatan    TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now','localtime'))
        );

        CREATE INDEX IF NOT EXISTS idx_pasien_nama ON pasien(nama);

        CREATE TABLE IF NOT EXISTS kunjungan (
            id             INTEGER PRIMARY KEY AUTOINCREMENT,
            pasien_id      INTEGER NOT NULL,
            tanggal        TEXT NOT NULL,
            keluhan        TEXT NOT NULL,
            diagnosis      TEXT,
            tindakan       TEXT,
            petugas_id     INTEGER,
            created_at     TEXT NOT NULL DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (pasien_id)  REFERENCES pasien(id) ON DELETE CASCADE,
            FOREIGN KEY (petugas_id) REFERENCES users(id)  ON DELETE SET NULL
        );

        CREATE INDEX IF NOT EXISTS idx_kunj_tanggal ON kunjungan(tanggal);
        CREATE INDEX IF NOT EXISTS idx_kunj_pasien  ON kunjungan(pasien_id);
    SQL);

    seedUsers($pdo);
}

function seedUsers(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count > 0) {
        return;
    }
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, password, nama, role) VALUES (:u, :p, :n, :r)'
    );
    foreach (DEFAULT_USERS as $username => $data) {
        $stmt->execute([
            ':u' => $username,
            ':p' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':n' => $data['nama'],
            ':r' => $data['role'],
        ]);
    }
}

/* ------------------------------------------------------------------
 |  AUTENTIKASI
 ------------------------------------------------------------------*/
function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        setFlash('warning', 'Silakan masuk terlebih dahulu.');
        redirect('login.php');
    }
}

function attemptLogin(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = :u AND aktif = 1 LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => (int) $user['id'],
        'username' => $user['username'],
        'nama'     => $user['nama'],
        'role'     => $user['role'],
    ];

    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}

/* ------------------------------------------------------------------
 |  HELPER UMUM
 ------------------------------------------------------------------*/
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function get(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function verifyCsrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa. Silakan ulangi.');
    }
}

function tanggalIndo(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    if ($ts === false) {
        return (string) $date;
    }
    return date('j', $ts) . ' ' . BULAN_INDO[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * True kalau aplikasi jalan di lokal / lingkungan pengembangan.
 * Dipakai untuk menyembunyikan info akun demo di server produksi.
 */
function isLingkunganDemo(): bool
{
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    $host = explode(':', $host)[0];

    if (in_array($host, ['localhost', '127.0.0.1', '::1', ''], true)) {
        return true;
    }

    // nama host internal / tunnel preview
    foreach (['.local', '.test', 'serveousercontent.com', 'ngrok', 'trycloudflare'] as $pola) {
        if (str_contains($host, $pola)) {
            return true;
        }
    }

    return false;
}

/**
 * "2026-10" atau "2026-10-01" → "Oktober 2026"
 */
function bulanIndo(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime(strlen($date) <= 7 ? $date . '-01' : $date);
    return $ts === false
        ? (string) $date
        : BULAN_INDO[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * "2026-10-01" → "Kamis"
 */
function hariIndo(?string $date): string
{
    $ts = $date ? strtotime($date) : false;
    return $ts === false ? '-' : HARI_INDO[(int) date('N', $ts)];
}

/* ------------------------------------------------------------------
 |  QUERY DATA
 ------------------------------------------------------------------*/
function statistik(): array
{
    $pdo = db();
    return [
        'pasien'          => (int) $pdo->query('SELECT COUNT(*) FROM pasien')->fetchColumn(),
        'kunjungan'       => (int) $pdo->query('SELECT COUNT(*) FROM kunjungan')->fetchColumn(),
        'kunjungan_bulan' => (int) $pdo->query(
            "SELECT COUNT(*) FROM kunjungan WHERE strftime('%Y-%m', tanggal) = strftime('%Y-%m','now','localtime')"
        )->fetchColumn(),
        'pasien_bulan'    => (int) $pdo->query(
            "SELECT COUNT(*) FROM pasien WHERE strftime('%Y-%m', created_at) = strftime('%Y-%m','now','localtime')"
        )->fetchColumn(),
    ];
}

function kunjunganTerbaru(int $limit = 5): array
{
    $sql = 'SELECT k.*, p.nama, p.kamar, u.nama AS petugas
            FROM kunjungan k
            JOIN pasien p ON p.id = k.pasien_id
            LEFT JOIN users u ON u.id = k.petugas_id
            ORDER BY k.tanggal DESC, k.id DESC
            LIMIT :limit';
    $stmt = db()->prepare($sql);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function daftarKunjungan(string $cari = ''): array
{
    $sql = 'SELECT k.*, p.nama, p.kamar, u.nama AS petugas
            FROM kunjungan k
            JOIN pasien p ON p.id = k.pasien_id
            LEFT JOIN users u ON u.id = k.petugas_id';
    $params = [];

    if ($cari !== '') {
        $sql .= ' WHERE p.nama LIKE :cari OR p.kamar LIKE :cari OR k.keluhan LIKE :cari OR k.diagnosis LIKE :cari';
        $params[':cari'] = '%' . $cari . '%';
    }

    $sql .= ' ORDER BY k.tanggal DESC, k.id DESC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function daftarPasien(string $cari = ''): array
{
    $sql = 'SELECT p.*,
                   COUNT(k.id) AS total_kunjungan,
                   MAX(k.tanggal) AS kunjungan_terakhir
            FROM pasien p
            LEFT JOIN kunjungan k ON k.pasien_id = p.id';

    $params = [];
    if ($cari !== '') {
        $sql .= ' WHERE p.nama LIKE :cari OR p.kamar LIKE :cari';
        $params[':cari'] = '%' . $cari . '%';
    }

    $sql .= ' GROUP BY p.id ORDER BY p.nama COLLATE NOCASE ASC';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function findPasien(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM pasien WHERE id = :id');
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function riwayatPasien(int $pasienId): array
{
    $stmt = db()->prepare(
        'SELECT k.*, p.kamar, u.nama AS petugas
         FROM kunjungan k
         JOIN pasien p ON p.id = k.pasien_id
         LEFT JOIN users u ON u.id = k.petugas_id
         WHERE k.pasien_id = :id
         ORDER BY k.tanggal DESC, k.id DESC'
    );
    $stmt->execute([':id' => $pasienId]);
    return $stmt->fetchAll();
}

function findKunjungan(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT k.*, p.nama, p.kamar, u.nama AS petugas
         FROM kunjungan k
         JOIN pasien p ON p.id = k.pasien_id
         LEFT JOIN users u ON u.id = k.petugas_id
         WHERE k.id = :id'
    );
    $stmt->execute([':id' => $id]);
    return $stmt->fetch() ?: null;
}

function simpanPasien(string $nama, string $kamar, string $catatan = ''): int
{
    $pdo = db();

    // pakai pasien yang sudah ada kalau nama + kamar sama
    $stmt = $pdo->prepare('SELECT id FROM pasien WHERE nama = :n AND kamar = :k LIMIT 1');
    $stmt->execute([':n' => $nama, ':k' => $kamar]);
    $existing = $stmt->fetchColumn();

    if ($existing) {
        if ($catatan !== '') {
            $upd = $pdo->prepare('UPDATE pasien SET catatan = :c WHERE id = :id');
            $upd->execute([':c' => $catatan, ':id' => $existing]);
        }
        return (int) $existing;
    }

    $ins = $pdo->prepare('INSERT INTO pasien (nama, kamar, catatan) VALUES (:n, :k, :c)');
    $ins->execute([':n' => $nama, ':k' => $kamar, ':c' => $catatan]);

    return (int) $pdo->lastInsertId();
}

function simpanKunjungan(int $pasienId, string $tanggal, string $keluhan, string $diagnosis, string $tindakan): int
{
    $stmt = db()->prepare(
        'INSERT INTO kunjungan (pasien_id, tanggal, keluhan, diagnosis, tindakan, petugas_id)
         VALUES (:p, :t, :k, :d, :a, :u)'
    );
    $stmt->execute([
        ':p' => $pasienId,
        ':t' => $tanggal,
        ':k' => $keluhan,
        ':d' => $diagnosis,
        ':a' => $tindakan,
        ':u' => currentUser()['id'] ?? null,
    ]);

    return (int) db()->lastInsertId();
}

function updateKunjungan(int $id, string $tanggal, string $keluhan, string $diagnosis, string $tindakan): void
{
    $stmt = db()->prepare(
        'UPDATE kunjungan
         SET tanggal = :t, keluhan = :k, diagnosis = :d, tindakan = :a
         WHERE id = :id'
    );
    $stmt->execute([
        ':t'  => $tanggal,
        ':k'  => $keluhan,
        ':d'  => $diagnosis,
        ':a'  => $tindakan,
        ':id' => $id,
    ]);
}

function hapusKunjungan(int $id): void
{
    $stmt = db()->prepare('DELETE FROM kunjungan WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

function hapusPasien(int $id): void
{
    $stmt = db()->prepare('DELETE FROM pasien WHERE id = :id');
    $stmt->execute([':id' => $id]);
}

/* ------------------------------------------------------------------
 |  VALIDASI
 ------------------------------------------------------------------*/
function validasi(array $data, array $rules): array
{
    $errors = [];

    foreach ($rules as $field => $label) {
        $value = trim((string) ($data[$field] ?? ''));
        if ($value === '') {
            $errors[$field] = $label . ' wajib diisi.';
        }
    }

    return $errors;
}
