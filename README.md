# SIKAPOL — Sistem Informasi Kunjungan Poliklinik

**Lapas Kelas IIB Polewali** · PHP Native + Bootstrap 5

Aplikasi pencatatan kunjungan poliklinik untuk Lapas/Rutan. Petugas mencatat kunjungan pasien/WBP, lalu seluruh riwayatnya bisa dilihat dan dicari kembali.

---

## Fitur

| # | Fitur | Keterangan |
|---|-------|------------|
| 1 | 🔐 **Login Petugas** | Username + password, password ter-hash (bcrypt), proteksi CSRF, rate-limit percobaan gagal |
| 2 | 👤 **Data Pasien** | Nama pasien/WBP + kamar/blok, tambah/ubah/hapus |
| 3 | 📝 **Pencatatan Kunjungan** | Nama pasien, kamar/blok, tanggal, keluhan, diagnosis, tindakan/catatan |
| 4 | 📋 **Riwayat Kunjungan** | Tabel lengkap 8 kolom + filter kata kunci dan rentang tanggal + cetak |
| 5 | 🔎 **Pencarian Pasien** | Cari by nama/kamar → profil pasien + seluruh riwayat kunjungannya |
| 6 | 📊 **Dashboard** | Jumlah pasien, jumlah kunjungan, kunjungan terbaru, ringkasan bulan ini |

**Bonus:**
- Live preview riwayat saat mengetik nama pasien di form (AJAX) — terlihat kalau pasien sudah pernah datang
- Auto-daftar pasien baru saat nama belum ada di database
- Responsif (HP/tablet/desktop), sidebar collapse di layar kecil
- Print-friendly untuk halaman riwayat

---

## Cara Install

### Syarat
- PHP **8.0+** dengan ekstensi `pdo_sqlite` (biasanya sudah aktif)
- Web server: Apache / Nginx / PHP built-in server
- **Tidak butuh MySQL** — database otomatis dibuat

### Opsi A — Pakai PHP built-in server (tes lokal)

```bash
cd sikapol
php -S localhost:8000
```

Buka `http://localhost:8000` → otomatis diarahkan ke halaman login.

### Opsi B — Shared hosting / cPanel

1. Upload seluruh isi folder `sikapol/` ke `public_html/` (atau subfolder, mis. `public_html/sikapol/`)
2. Pastikan folder `data/` bisa ditulis PHP (permission `755` atau `775`)
3. Buka domain-nya di browser → login

> **Penting:** jangan taruh di subfolder yang namanya bentrok. Semua path relatif, jadi aman di mana saja.

### Opsi C — VPS / Nginx

```nginx
server {
    listen 80;
    server_name sikapol.contoh.id;
    root /var/www/sikapol;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # blokir akses langsung ke folder data
    location ^~ /data/ { deny all; }
}
```

---

## Akun Default

> ⚠️ **GANTI PASSWORD INI setelah login pertama!**

| Role | Username | Password |
|------|----------|----------|
| Administrator | `admin` | `admin123` |
| Petugas | `petugas` | `petugas123` |

Akun dibuat otomatis saat aplikasi pertama kali dijalankan. Ganti lewat menu **Pengaturan** (ikon di kanan atas).

---

## Struktur File

```
sikapol/
├── index.php            # Dashboard (jumlah pasien, kunjungan, terbaru)
├── login.php            # Halaman login petugas
├── logout.php           # Keluar sesi
├── kunjungan.php        # Form pencatatan + edit + hapus kunjungan
├── riwayat.php          # Tabel seluruh riwayat + filter + cetak
├── pencarian.php        # Pencarian pasien + detail riwayatnya
├── pasien.php           # Daftar & pengelolaan data pasien
├── pengaturan.php       # Ganti password, kelola akun petugas
├── api-riwayat.php      # Endpoint JSON untuk live preview riwayat
├── riwayat-ajax.js      # Script live preview
├── config.php           # Koneksi DB, helper, autentikasi, CSRF
├── partials/
│   ├── header.php        # Sidebar + topbar layout
│   └── footer.php        # Penutup + script
└── data/
    ├── sikapol.sqlite    # Database (dibuat otomatis)
    └── .htaccess         # Proteksi folder
```

---

## Skema Database

```sql
users       (id, username, password, nama, role, aktif, dibuat)
pasien      (id, nama, kamar, catatan, dibuat, diubah)
kunjungan   (id, pasien_id → pasien, tanggal, keluhan, diagnosis,
             tindakan, petugas_id → users, dibuat, diubah)
```

- `users.role` → `admin` | `petugas`
- Data pasien di-*de-duplikasi* per nama+kamar, jadi 1 pasien punya banyak kunjungan
- Hapus pasien → riwayat kunjungannya ikut terhapus (`ON DELETE CASCADE`)

---

## Keamanan

- Password disimpan sebagai **hash bcrypt** (`password_hash`), bukan plaintext
- **CSRF token** di setiap form POST
- **Prepared statement** (PDO) di semua query → aman dari SQL injection
- Semua output di-escape pakai `htmlspecialchars` → aman dari XSS
- **Rate limit login**: maksimal 5 percobaan gagal per 5 menit
- Session regenerate setelah login berhasil
- Folder `data/` diproteksi `.htaccess`

---

## Backup

Cukup salin **satu file**: `data/sikapol.sqlite`

```bash
cp data/sikapol.sqlite backup/sikapol-$(date +%F).sqlite
```

---

## Kustomisasi

- **Nama instansi** → ubah `APP_FULL` di `config.php`
- **Warna tema** → ubah `--sk-primary` di `partials/header.php`
- **Pakai MySQL** → ganti `db()` di `config.php` jadi DSN `mysql:host=...;dbname=...`

---

Dibuat dengan PHP native + Bootstrap 5 + SQLite. Tanpa framework, tanpa dependensi eksternal.
