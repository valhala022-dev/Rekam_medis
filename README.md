# Sistem Informasi Rekam Medis

c untuk Uji Kompetensi Pemrograman Web, SMK Negeri 3 Tuban.

Panduan ini ditulis untuk pemula. Ikuti langkahnya **urut dari atas ke bawah**, jangan ada yang dilewati.

---

## Daftar Isi

1. [Apa saja yang dibutuhkan](#1-apa-saja-yang-dibutuhkan)
2. [Langkah 1: Siapkan file proyek](#2-langkah-1-siapkan-file-proyek)
3. [Langkah 2: Buka terminal dan masuk ke folder proyek](#3-langkah-2-buka-terminal-dan-masuk-ke-folder-proyek)
4. [Langkah 3: Pasang program dan buat database (setup)](#4-langkah-3-pasang-program-dan-buat-database-setup)
5. [Langkah 4: Jalankan aplikasi](#5-langkah-4-jalankan-aplikasi)
6. [Langkah 5: Buka aplikasi di browser](#6-langkah-5-buka-aplikasi-di-browser)
7. [Cara memakai aplikasi](#7-cara-memakai-aplikasi)
8. [Menjalankan lagi di hari berikutnya](#8-menjalankan-lagi-di-hari-berikutnya)
9. [Cara mematikan aplikasi](#9-cara-mematikan-aplikasi)
10. [Kalau ada error (pemecahan masalah)](#10-kalau-ada-error-pemecahan-masalah)
11. [Informasi teknis](#11-informasi-teknis)

---

## 1. Apa saja yang dibutuhkan

- Akses ke **code-server** (VS Code di browser) yang berjalan di **Alpine Linux**.
- Koneksi internet (untuk memasang program dan memuat tampilan Tailwind CSS).
- File proyek `rekam_medis.zip` (atau folder `rekam_medis` yang sudah jadi).

Anda **tidak perlu** memasang XAMPP atau Laragon. Semuanya berjalan langsung di dalam Alpine Linux.

> **Istilah singkat**
> - **Terminal**: jendela hitam tempat mengetik perintah.
> - **Folder proyek**: folder `rekam_medis` yang isinya `index.php`, `config.php`, `database.sql`, dan seterusnya.
> - **MariaDB**: program database tempat data disimpan.
> - **PHP**: program yang menjalankan kode aplikasi.

---

## 2. Langkah 1: Siapkan file proyek

Tujuannya: semua file proyek berada di folder `/www/rekam_medis`.

### Cara A. Dari file zip

1. Di code-server, buka panel **Explorer** (ikon dua lembar kertas di sisi kiri).
2. Seret file `rekam_medis.zip` ke folder `/www` pada Explorer, atau klik kanan lalu **Upload**.
3. Buka terminal (lihat Langkah 2), lalu ketik:

   ```sh
   apk add unzip
   cd /www
   unzip -o rekam_medis.zip
   ```

### Cara B. Kalau sudah berupa folder

Seret seluruh folder `rekam_medis` ke dalam `/www`.

### Cek apakah sudah benar

Ketik di terminal:

```sh
ls -R /www/rekam_medis
```

Hasilnya **harus** seperti ini:

```
/www/rekam_medis:
README.md   config.php   database.sql   dokter.php   includes
index.php   laporan.php  obat.php       pasien.php   rekam_medis.php   setup.sh

/www/rekam_medis/includes:
footer.php   functions.php   header.php
```

Kalau ada file yang kurang, ulangi langkah ini sampai lengkap.

> **Awas:** kalau setelah unzip file-nya malah ada di `/www/rekam_medis/rekam_medis/`
> (folder di dalam folder), gunakan folder yang paling dalam pada langkah berikutnya.

---

## 3. Langkah 2: Buka terminal dan masuk ke folder proyek

1. Di code-server, klik menu **Terminal** lalu **New Terminal** (atau tekan `` Ctrl + ` ``).
2. Ketik perintah ini lalu tekan **Enter**:

   ```sh
   cd /www/rekam_medis
   ```

3. Pastikan Anda sudah di folder yang benar:

   ```sh
   ls
   ```

   Anda harus melihat `index.php` dan `setup.sh` di daftar.

> **Penting:** kalau Anda pernah menghapus dan membuat ulang folder ini, tutup terminal lama
> dan buka terminal baru. Kalau tidak, terminal bisa "nyangkut" di folder lama yang sudah kosong.

---

## 4. Langkah 3: Pasang program dan buat database (setup)

Cukup satu perintah:

```sh
sh setup.sh
```

Skrip ini otomatis:

1. Memasang PHP dan MariaDB.
2. Menyalakan MariaDB.
3. Membuat database `db_rekam_medis` beserta tabel dan data contoh.
4. Membuat user database khusus untuk aplikasi.

Tunggu sampai selesai (bisa 1 sampai 3 menit). Berhasil kalau muncul tulisan terakhir:

```
Selesai. Database dan user aplikasi sudah siap.
```

> **Peringatan:** menjalankan `sh setup.sh` **lagi** akan mengembalikan data rekam medis
> ke data awal (semua data yang sudah Anda tambahkan hilang).
> Database milik proyek lain di server yang sama **tidak** disentuh.
> Cukup jalankan sekali saja.

Kalau muncul error, lihat [bagian 10](#10-kalau-ada-error-pemecahan-masalah).

---

## 5. Langkah 4: Jalankan aplikasi

Ketik di terminal:

```sh
php -S 0.0.0.0:8000 -t /www/rekam_medis
```

Berhasil kalau muncul tulisan seperti:

```
PHP 8.2.x Development Server (http://0.0.0.0:8000) started
```

**Jangan tutup terminal ini** dan jangan tekan apa pun. Selama terminal terbuka, aplikasi menyala.
Tulisan yang muncul di sini adalah catatan (log) setiap kali halaman dibuka, itu normal.

---

## 6. Langkah 5: Buka aplikasi di browser

Aplikasi berjalan di port **8000**. Cara membukanya ada tiga, coba berurutan sampai berhasil:

### Cara 1: Lewat alamat proxy code-server (paling mudah)

Lihat alamat code-server Anda di browser, misalnya:

```
https://alamat-code-server-anda.com/
```

Tambahkan `proxy/8000/` di belakangnya:

```
https://alamat-code-server-anda.com/proxy/8000/
```

Kalau muncul "Not Found" dari proxy, coba `absproxy/8000/`:

```
https://alamat-code-server-anda.com/absproxy/8000/
```

### Cara 2: Lewat tab PORTS di code-server

1. Di bagian bawah code-server, klik tab **PORTS**.
2. Cari baris port **8000**, lalu klik ikon globe/browser (Open in Browser).

### Cara 3: Port lain

Kalau port 8000 diblokir sekolah, jalankan di port lain, misalnya 8080:

```sh
php -S 0.0.0.0:8080 -t /www/rekam_medis
```

Lalu buka lewat `.../proxy/8080/`.

### Tampilan yang benar

Anda akan melihat **Dashboard** dengan 4 kartu: Total Pasien (3), Total Dokter (2),
Total Jenis Obat (3), dan Total Rekam Medis (3), serta menu di sebelah kiri.

---

## 7. Cara memakai aplikasi

| Menu | Fungsi |
| :--- | :--- |
| **Dashboard** | Ringkasan jumlah pasien, dokter, obat, dan rekam medis (angka selalu real-time dari database). |
| **Data Pasien** | Tambah, ubah, dan hapus pasien. |
| **Data Dokter** | Tambah, ubah, dan hapus dokter. |
| **Data Obat** | Tambah, ubah, dan hapus obat. |
| **Rekam Medis** | Catat pemeriksaan. Pasien, dokter, dan obat **dipilih dari dropdown**, tidak diketik. |
| **Laporan Riwayat** | Tabel riwayat pemeriksaan lengkap (nama pasien, dokter, obat). Ada tombol **Cetak**. |

**Menambah data:** isi formulir di bagian atas halaman lalu klik **Simpan**.
**Mengubah data:** klik tombol **Edit** di baris data, ubah isinya, lalu klik **Simpan Perubahan**.
**Menghapus data:** klik **Hapus**, lalu pilih **OK** pada kotak konfirmasi.

Setiap aksi menampilkan pesan di pojok kanan atas: hijau **Sukses** atau merah **Gagal**.

**Aturan relasi:** pasien, dokter, atau obat yang **sudah dipakai** di rekam medis **tidak bisa dihapus**.
Muncul pesan "Gagal: Data sedang digunakan". Hapus dulu rekam medis yang memakainya, baru datanya bisa dihapus.

**Di HP atau tablet:** menu samping berubah menjadi tombol garis tiga (hamburger) di kiri atas.
Tabel yang lebar bisa digeser ke samping, dan laporan tampil sebagai kartu di layar kecil.

---

## 8. Menjalankan lagi di hari berikutnya

Kalau code-server atau komputer dimatikan lalu dinyalakan lagi, **jangan** jalankan `sh setup.sh` lagi
(data akan kembali ke awal). Cukup lakukan 3 hal ini:

1. Buka terminal baru, lalu masuk ke folder:

   ```sh
   cd /www/rekam_medis
   ```

2. Cek apakah MariaDB masih menyala:

   ```sh
   ls /run/mysqld/
   ```

   - Kalau ada tulisan `mysqld.sock`, MariaDB menyala. Lanjut ke langkah 3.
   - Kalau kosong atau error, nyalakan MariaDB dulu:

     ```sh
     mkdir -p /run/mysqld && chown mysql:mysql /run/mysqld
     mysqld_safe --user=mysql > /tmp/mysqld.log 2>&1 &
     ```

     Tunggu sekitar 10 detik.

3. Jalankan aplikasi:

   ```sh
   php -S 0.0.0.0:8000 -t /www/rekam_medis
   ```

Lalu buka lagi di browser seperti [Langkah 5](#6-langkah-5-buka-aplikasi-di-browser).

---

## 9. Cara mematikan aplikasi

Klik di terminal yang sedang menjalankan PHP, lalu tekan **Ctrl + C**.
Aplikasi berhenti dan data tetap tersimpan di database.

---

## 10. Kalau ada error (pemecahan masalah)

| Pesan / gejala | Artinya | Solusi |
| :--- | :--- | :--- |
| `can't open database.sql: no such file` | File `database.sql` tidak ada di folder. | Salin ulang semua file proyek, cek dengan `ls`, lalu jalankan `cd /www/rekam_medis` dan `sh setup.sh` lagi. |
| `Failed to listen on 0.0.0.0:8000 (reason: Address in use)` | Port 8000 masih dipakai PHP lama. | Jalankan `pkill -f "php -S"` lalu ulangi perintah `php -S ...`. Atau pakai port lain, misalnya 8080. |
| Browser menampilkan `404` dan log tertulis `No such file or directory` | PHP berjalan di folder yang salah atau kosong. | Pakai opsi `-t`: `php -S 0.0.0.0:8000 -t /www/rekam_medis`. Pastikan `index.php` ada (`ls /www/rekam_medis`). |
| Halaman **"Koneksi database gagal"** dengan detail `Connection refused` | MariaDB mati, atau Alpine menolak koneksi TCP. | Pastikan `config.php` versi terbaru (cek: `grep -c mysqld.sock /www/rekam_medis/config.php`, hasilnya harus lebih dari 0). Pastikan MariaDB menyala (lihat bagian 8). |
| Halaman **"Koneksi database gagal"** dengan detail `Access denied` atau `Unknown database` | User atau database belum dibuat. | Jalankan `sh setup.sh`. Atau buat manual (lihat di bawah tabel ini). |
| Halaman putih atau error `Class "mysqli" not found` | Ekstensi PHP untuk MariaDB belum terpasang. | Jalankan `apk add php82-mysqli php82-session php82-json`, lalu jalankan ulang PHP. |
| Port 8000 tidak bisa dibuka dari browser | Port diblokir sekolah. | Coba `/proxy/8000/` atau `/absproxy/8000/`, atau tab **PORTS**, atau port lain (bagian 6). |
| Tampilan berantakan (tidak ada warna/gaya) | Tailwind CSS tidak termuat karena tidak ada internet. | Pastikan komputer yang membuka browser terhubung ke internet, lalu refresh. |
| Ketik `$db = db();` di terminal lalu error | Itu kode PHP, bukan perintah terminal. | Tidak perlu diketik. Kode itu sudah ada di dalam file aplikasi. |

**Membuat user database secara manual** (bila `setup.sh` bermasalah):

```sh
mariadb -uroot < /www/rekam_medis/database.sql
mariadb -uroot -e "CREATE USER IF NOT EXISTS 'rekam'@'localhost' IDENTIFIED BY 'rekam123'; GRANT ALL PRIVILEGES ON db_rekam_medis.* TO 'rekam'@'localhost'; FLUSH PRIVILEGES;"
```

**Memeriksa isi database** (untuk memastikan datanya ada):

```sh
mariadb -uroot -e "SELECT COUNT(*) FROM db_rekam_medis.pasien;"
```

Hasil yang benar pada awal pemakaian adalah angka `3`.

---

## 11. Informasi teknis

### Struktur file

| File | Fungsi |
| :--- | :--- |
| `setup.sh` | Skrip pemasangan otomatis (paket, MariaDB, database, user). |
| `database.sql` | Skema `db_rekam_medis` (4 tabel + foreign key) dan data uji. |
| `config.php` | Koneksi database (socket MariaDB lebih dulu, TCP sebagai cadangan). |
| `includes/functions.php` | Fungsi bantu (escape HTML, notifikasi, CSRF, hapus dengan cek relasi). |
| `includes/header.php`, `footer.php` | Tampilan bersama: sidebar, notifikasi, JavaScript. |
| `index.php` | Dashboard 4 kartu (`SELECT COUNT(*)`). |
| `pasien.php`, `dokter.php`, `obat.php` | CRUD data master. |
| `rekam_medis.php` | CRUD rekam medis dengan dropdown dinamis. |
| `laporan.php` | Riwayat pemeriksaan dengan `INNER JOIN` 4 tabel. |

### Pengaturan database

Default di `config.php`: user `rekam`, password `rekam123`, database `db_rekam_medis`.
Bisa diganti lewat environment variable `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASS`, `DB_NAME`.

### Data awal

3 pasien (Pasien A, B, C), 2 dokter (Dr. Andi, Dr. Budi), 3 obat (Paracetamol, Amoxicillin, Sirup Batuk),
dan 3 contoh rekam medis agar Laporan dan Dashboard langsung terisi.

### Test plan (PRD bagian 6)

| No. | Fitur | Hasil yang diharapkan | Status |
| :-: | :--- | :--- | :-: |
| 1 | Tambah pasien | Data tersimpan dan muncul di tabel pasien. | [x] |
| 2 | Edit dokter | Perubahan nama/spesialisasi tersimpan. | [x] |
| 3 | Hapus obat | Ditolak bila sudah dipakai rekam medis, berhasil bila belum. | [x] |
| 4 | Tambah rekam medis | Tersimpan dengan foreign key yang valid. | [x] |
| 5 | Dropdown relasi | Pilihan pasien, dokter, obat diambil dari database. | [x] |
| 6 | JOIN riwayat | Laporan menampilkan nama pasien, dokter, obat (bukan ID). | [x] |
| 7 | Dashboard | Angka ringkasan sama dengan isi database. | [x] |
# Rekam_medis
