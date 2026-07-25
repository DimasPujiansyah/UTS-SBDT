# Sistem Penjualan Minimarket Terdistribusi

> Pengembangan aplikasi **Point of Sale (POS)** berbasis Laravel dengan implementasi **MySQL Master–Replica Replication** sebagai studi kasus mata kuliah **Sistem Basis Data Terdistribusi (SBDT)**.

---

## 📖 Deskripsi

Sistem Penjualan Minimarket Terdistribusi merupakan aplikasi kasir (Point of Sale) yang dikembangkan menggunakan framework **Laravel**. Sistem ini mendukung pengelolaan transaksi penjualan, produk, pelanggan, supplier, dan laporan penjualan. Selain itu, sistem menerapkan konsep **Basis Data Terdistribusi** menggunakan **MySQL Master–Replica Replication** untuk meningkatkan ketersediaan data (availability), redundansi (redundancy), dan kemudahan pemulihan data (recovery).

Proyek ini dibuat sebagai tugas akhir mata kuliah **Sistem Basis Data Terdistribusi (SBDT)**.

---

## ✨ Fitur Utama

* Login Admin
* Dashboard Penjualan
* Manajemen Produk
* Manajemen Kategori
* Manajemen Supplier
* Manajemen Pelanggan
* Manajemen Stok Barang
* Transaksi Penjualan
* Riwayat Transaksi
* Laporan Penjualan
* Filter Laporan Berdasarkan Tanggal
* Export Laporan ke PDF
* Export Laporan ke Excel
* Dashboard Statistik
* Grafik Penjualan
* Pencarian Produk
* Notifikasi Stok Menipis
* Profil Admin

---

## 🛠 Teknologi yang Digunakan

* Laravel
* PHP
* MySQL
* Bootstrap
* JavaScript
* HTML5
* CSS3

---

## 🗄 Konsep Basis Data Terdistribusi

Sistem menerapkan arsitektur **Master–Replica Replication**.

```
                +----------------------+
                |     Web Laravel      |
                +----------+-----------+
                           |
                     WRITE / READ
                           |
                 +---------+---------+
                 |   MySQL MASTER    |
                 +---------+---------+
                           |
                Binary Log Replication
                           |
                 +---------+---------+
                 |   MySQL REPLICA   |
                 +-------------------+
```

### Master Server

* INSERT
* UPDATE
* DELETE

### Replica Server

* READ ONLY
* Backup Data
* Reporting
* Redundancy
* High Availability

---

## 📂 Struktur Project

```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
artisan
composer.json
package.json
README.md
```

---

## 🚀 Cara Instalasi

### Clone Repository

```bash
git clone https://github.com/username/Sistem_Penjualan_Minimarket_Terdistribusi.git
```

### Masuk ke Folder

```bash
cd Sistem_Penjualan_Minimarket_Terdistribusi
```

### Install Dependency

```bash
composer install
```

```bash
npm install
```

### Copy File Environment

```bash
cp .env.example .env
```

### Generate Application Key

```bash
php artisan key:generate
```

### Konfigurasi Database

Edit file `.env`

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=minimarket
DB_USERNAME=root
DB_PASSWORD=
```

### Jalankan Migration

```bash
php artisan migrate
```

### Jalankan Seeder

```bash
php artisan db:seed
```

### Menjalankan Project

```bash
php artisan serve
```

Akses aplikasi:

```
http://127.0.0.1:8000
```

---

## 🔄 Implementasi Replikasi

### Master

* Menyimpan seluruh transaksi
* Mengelola perubahan data
* Mengirim Binary Log

### Replica

* Menyalin data dari Master
* Digunakan untuk laporan
* Backup Database
* High Availability

---

## 📊 Modul Sistem

* Dashboard
* Produk
* Kategori
* Supplier
* Pelanggan
* Penjualan
* Laporan
* Profil Admin
* Statistik
* Grafik Penjualan

---

## 🎯 Tujuan Pengembangan

* Mengimplementasikan konsep Sistem Basis Data Terdistribusi.
* Meningkatkan ketersediaan data.
* Menjamin sinkronisasi antar server.
* Mendukung proses transaksi minimarket secara efisien.
* Menyediakan laporan penjualan yang akurat.

---

## 👨‍💻 Pengembang

**Nama:** Dimas Pujiansyah
**NIM:** 20230801317

Program Studi Teknik Informatika
Universitas Esa Unggul

---

## 📚 Mata Kuliah

**Sistem Basis Data Terdistribusi (SBDT)**