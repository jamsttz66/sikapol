# SIKAPOL — Sistem Informasi Kunjungan Poliklinik

**Lapas Kelas IIB Polewali** · PHP Native + Bootstrap 5

Aplikasi pencatatan kunjungan poliklinik untuk Lapas/Rutan. Petugas mencatat kunjungan pasien/WBP, lalu seluruh riwayatnya bisa dilihat dan dicari kembali.

**Daftar isi:**
[Fitur](#fitur) · [Cara Install](#cara-install) · [**Deploy ke cPanel**](#-deploy-ke-cpanel--tutor-lengkap) · [Akun Default](#akun-default) · [Keamanan](#keamanan) · [Backup](#backup)

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

### Opsi B — Shared hosting / cPanel ⭐ *paling umum untuk instansi*

Tutor lengkap ada di bagian **[Deploy ke cPanel](#-deploy-ke-cpanel--tutor-lengkap)** di bawah.

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

## 🚀 Deploy ke cPanel — Tutor Lengkap

Tutor ini pakai **cPanel** yang dipakai hampir semua shared hosting Indonesia (Hostinger, Niagahoster, Rumahweb, Domainesia, IDwebhost, dll).

> **Waktu:** ~10 menit. **Modal:** hosting + domain (opsional, bisa pakai subdomain gratis dari hosting).

### Langkah 0 — Persiapan

Siapkan file SIKAPOL jadi satu ZIP. Download dari GitHub:

1. Buka https://github.com/jamsttz66/sikapol
2. Klik tombol hijau **`< > Code`** → **Download ZIP**
3. Simpan di komputer, jangan di-extract dulu

### Langkah 1 — Masuk cPanel

1. Buka email dari hosting, cari **link cPanel** + username + password
2. Atau akses langsung: `https://namadomain.com/cpanel` (atau `:2083`)
3. Login

> Kalau lupa alamatnya, cek email "Welcome to ..." dari hosting lo.

### Langkah 2 — Upload file

**Cara A — File Manager (paling gampang, tanpa aplikasi):**

1. Di cPanel, cari **File Manager** (biasanya di bagian *Files*)
2. Masuk ke folder **`public_html`** — ini folder utama website lo
3. Klik **Upload** (ikon panah ke atas) di toolbar atas
4. Pilih file `sikapol-main.zip` yang tadi didownload → tunggu sampai 100%
5. Kembali ke File Manager, klik kanan file ZIP → **Extract** → **Extract Files**
6. Setelah ter-extract, akan muncul folder `sikapol-main`. Buka folder itu
7. **Select All** (Ctrl+A) semua isi di dalamnya → klik **Move** → arahkan ke `/public_html` → **Move Files**
8. Hapus folder `sikapol-main` yang sudah kosong + file ZIP-nya

**Cara B — FTP (kalau file-nya banyak / koneksi lebih stabil):**

1. Di cPanel cari **FTP Accounts** → buat akun FTP baru
2. Download **FileZilla** (gratis) → connect pakai:
   - Host: `ftp.namadomain.com`
   - Username & password: dari akun FTP yang lo buat
   - Port: `21`
3. Di sisi kanan (server), masuk ke `public_html`
4. Di sisi kiri (komputer), buka folder SIKAPOL yang sudah di-extract
5. **Select All** file → drag ke kanan → tunggu selesai

**Struktur akhir harus seperti ini:**

```
public_html/
├── index.php          ← dashboard
├── login.php
├── config.php
├── kunjungan.php
├── riwayat.php
├── pencarian.php
├── pasien.php
├── pengaturan.php
├── api-riwayat.php
├── riwayat-ajax.js
├── partials/
│   ├── header.php
│   └── footer.php
└── data/              ← folder ini WAJIB bisa ditulis
    └── .htaccess
```

> ⚠️ **Jangan** taruh di dalam subfolder `sikapol-main/`. File `index.php` harus langsung di dalam `public_html`.

### Langkah 3 — Set permission folder `data/`

Folder `data/` harus bisa ditulis PHP, kalau tidak database gagal dibuat.

1. Di File Manager, klik kanan folder **`data`** → **Change Permissions** / **Set Permissions**
2. Set jadi **`755`**
3. Centang **Recurse into subdirectories** (kalau ada)
4. Klik **Change Permissions**

> Kalau nanti muncul error *"unable to open database file"*, naikkan jadi **`775`** atau **`777`** (khusus folder `data` saja, jangan yang lain).

### Langkah 4 — Pilih versi PHP yang benar

SIKAPOL butuh **PHP 8.0+** dengan ekstensi `pdo_sqlite`.

1. Di cPanel cari **Select PHP Version** / **MultiPHP Manager**
2. Pilih PHP **8.1**, **8.2**, atau **8.3**
3. Klik **Set as current** / **Apply**
4. Cek **Extensions** (kalau ada tabnya) — pastikan **`pdo_sqlite`** dan **`sqlite3`** tercentang ✓

> Di cPanel yang pakai **MultiPHP INI Editor**, pastikan tidak ada `open_basedir` yang memblokir folder `data/`.

### Langkah 5 — Buka website & login

1. Buka `https://namadomain.com` di browser
2. Akan muncul halaman login SIKAPOL
3. Login pakai:

   | Role | Username | Password |
   |------|----------|----------|
   | Administrator | `admin` | `admin123` |

4. Database `sikapol.sqlite` otomatis dibuat di folder `data/`

### Langkah 6 — Amankan (WAJIB, jangan dilewat!)

**a. Ganti password default**

Klik ikon profil kanan atas → **Pengaturan** → tab **Ganti Password**.
Ganti password `admin` dan `petugas`.

**b. Pasang SSL/HTTPS**

1. cPanel → **SSL/TLS Status**
2. Centang domain lo → klik **Run AutoSSL**
3. Tunggu 5-15 menit → cek `https://namadomain.com` sudah ada gembok 🔒

**c. Verifikasi folder `data/` tidak bisa diakses publik**

Buka `https://namadomain.com/data/sikapol.sqlite` di browser. Harus muncul **403 Forbidden** atau **404**. Kalau file-nya malah ke-download, **STOP** dan perbaiki permission `.htaccess` di folder `data/` — ini bahaya, data pasien bisa diambil orang.

> Kalau `.htaccess` tidak bekerja, tambahkan di cPanel → **Indexes** → matikan *Indexing*, atau pindahkan database ke luar `public_html` dan ubah path di `config.php`.

### Langkah 7 — Backup berkala

Database lo ada di **satu file**: `data/sikapol.sqlite`.

**Backup manual:** File Manager → klik kanan `sikapol.sqlite` → **Download**.

**Backup otomatis (opsional tapi disarankan):**

1. cPanel → **Cron Jobs**
2. Tambah cron job baru:
   - Common Settings: **Once Per Day**
   - Command:
     ```bash
     cp /home/USERNAME/public_html/data/sikapol.sqlite /home/USERNAME/backup/sikapol-$(date +\%F).sqlite
     ```
3. Ganti `USERNAME` dengan username cPanel lo (lihat di kanan atas cPanel)
4. Buat folder `backup` di luar `public_html` lewat File Manager

---

### Kalau ada masalah

| Gejala | Penyebab | Solusi |
|--------|----------|--------|
| **500 Internal Server Error** | Versi PHP terlalu tua | cPanel → Select PHP Version → pilih 8.1+ |
| **Blank putih** | Error PHP tidak tampil | Sementara ubah `display_errors = On` di MultiPHP INI Editor |
| **"unable to open database file"** | Folder `data/` tidak writable | Set permission `data/` jadi `775` atau `777` |
| **Database error saat login** | Ekstensi `pdo_sqlite` mati | Select PHP Version → Extensions → centang `pdo_sqlite` |
| **Muncul daftar file, bukan website** | File ada di subfolder | Pindahkan `index.php` dkk langsung ke `public_html` |
| **Fitur live preview riwayat tidak jalan** | `.js` diblokir / 404 | Cek `riwayat-ajax.js` ada di `public_html` |
| **CSS/ tampilan polos tanpa warna** | Bootstrap CDN diblokir | Umumnya tidak masalah; pastikan server bisa akses internet |
| **Link login selalu balik ke login** | Cookie/session bermasalah | Cek folder session hosting; hubungi support hosting |

---

### Deploy via cPanel API (tanpa File Manager / FTP)

Kalau lo punya akses cPanel API token, file bisa di-upload lewat terminal:

```bash
# endpoint upload Fileman
curl -X POST "https://namadomain.com:2083/execute/Fileman/upload_files" \
  -H "Authorization: cpanel USERNAME:TOKEN" \
  -F "dir=/home/USERNAME/public_html" \
  -F "file-1=@config.php"
```

> Cara ini dipakai kalau File Manager sering gagal upload file besar. Token dibuat di cPanel → **Manage API Tokens**.

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
