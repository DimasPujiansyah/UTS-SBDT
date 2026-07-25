# Sistem Basis Data Terdistribusi — MySQL Master–Replica

Dokumen ini menjelaskan implementasi konsep **Distributed Database (Master–Replica)**
untuk Tugas Akhir *Sistem Penjualan Minimarket Terdistribusi*.

## 1. Arsitektur

```
                     ┌─────────────────────────┐
                     │   Aplikasi Laravel       │
                     │  (Filament Admin Panel)  │
                     └───────────┬──────────────┘
                                 │
                 read/write splitting (config/database.php)
                                 │
              ┌──────────────────┴───────────────────┐
              │                                       │
        WRITE (INSERT/UPDATE/DELETE)            READ (SELECT)
              │                                       │
              ▼                                       ▼
     ┌──────────────────┐   binlog replication   ┌──────────────────┐
     │   DB MASTER       │ ─────────────────────▶ │   DB REPLICA      │
     │  db-master:3306   │   (async, GTID-based)  │  db-replica:3306  │
     │  server-id = 1     │                        │  server-id = 2    │
     │  read_only = OFF   │                        │  read_only = ON   │
     └──────────────────┘                        └──────────────────┘
```

- **Master**: satu-satunya node yang menerima perintah tulis (INSERT/UPDATE/DELETE). Mencatat setiap perubahan ke *binary log* (`log-bin`).
- **Replica**: menyalin (mereplikasi) *binary log* dari Master secara asinkron, lalu menerapkannya ke datanya sendiri. Dikonfigurasi `read_only = ON` agar tidak bisa ditulis langsung — memastikan konsistensi data terpusat di Master.
- **Laravel** memakai fitur bawaan *read/write connections* sehingga aplikasi tidak perlu tahu index replica mana yang dipakai — semua otomatis.

## 2. File yang Ditambahkan

| File | Fungsi |
|---|---|
| `template/db/master-replica/my-master.cnf` | Konfigurasi MySQL/MariaDB node Master (server-id, binlog, GTID) |
| `template/db/master-replica/my-replica.cnf` | Konfigurasi MySQL/MariaDB node Replica (server-id, read_only) |
| `template/db/master-replica/init-master.sql` | Membuat user replikasi (`replica_user`) otomatis saat Master pertama kali start |
| `template/db/master-replica/docker-compose.master-replica.yml` | Menjalankan container `db-master` dan `db-replica` |
| `src/config/database.php` | Menambahkan opsi `read` / `write` / `sticky` pada koneksi `mysql` & `mariadb` |
| `src/.env.example` | Menambahkan `DB_HOST_WRITE`, `DB_HOST_READ`, kredensial replikasi |

## 3. Konfigurasi Laravel (`config/database.php`)

Laravel secara native mendukung *read/write splitting* per koneksi:

```php
'mariadb' => [
    'driver' => 'mariadb',
    'read' => env('DB_HOST_READ') ? [
        'host' => array_map('trim', explode(',', env('DB_HOST_READ'))),
    ] : null,
    'write' => env('DB_HOST_WRITE') ? [
        'host' => array_map('trim', explode(',', env('DB_HOST_WRITE'))),
    ] : null,
    'sticky' => true,
    // ...kredensial & opsi lain tetap sama untuk kedua node
],
```

- `sticky = true` memastikan bahwa **dalam satu request yang sama**, setelah melakukan WRITE, query SELECT berikutnya tetap dibaca dari Master (bukan Replica) — mencegah masalah *replication lag* (data baru saja ditulis tapi belum sempat sampai ke Replica).
- Jika `DB_HOST_READ` / `DB_HOST_WRITE` dikosongkan, Laravel otomatis kembali memakai koneksi tunggal `DB_HOST` — jadi project tetap bisa jalan tanpa Master-Replica untuk keperluan development biasa.

## 4. Konfigurasi `.env`

```env
DB_CONNECTION=mariadb
DB_HOST=db                     # fallback jika read/write kosong
DB_PORT=3306
DB_DATABASE=minimarket_terdistribusi
DB_USERNAME=root
DB_PASSWORD=p455w0rd

# Aktifkan Master-Replica:
DB_HOST_WRITE=db-master
DB_HOST_READ=db-replica
# Boleh lebih dari 1 replica: DB_HOST_READ=db-replica-1,db-replica-2

DB_REPLICATION_USER=replica_user
DB_REPLICATION_PASSWORD=replica_p455w0rd
```

## 5. Langkah Menjalankan & Mengaktifkan Replikasi

1. **Jalankan kedua container database:**
   ```bash
   docker compose -f template/docker-compose.yml -f template/db/master-replica/docker-compose.master-replica.yml up -d db-master db-replica
   ```

2. **Cek posisi binlog Master:**
   ```bash
   docker exec -it minimarket_db_master mariadb -uroot -pp455w0rd -e "SHOW MASTER STATUS\G"
   ```
   Catat nilai `File` dan `Position` (tidak dibutuhkan jika memakai GTID, tapi baik untuk verifikasi).

3. **Arahkan Replica ke Master (jalankan di container replica):**
   ```bash
   docker exec -it minimarket_db_replica mariadb -uroot -pp455w0rd -e "
     STOP SLAVE;
     CHANGE MASTER TO
       MASTER_HOST='db-master',
       MASTER_USER='replica_user',
       MASTER_PASSWORD='replica_p455w0rd',
       MASTER_USE_GTID=slave_pos;
     START SLAVE;
   "
   ```

4. **Verifikasi status replikasi:**
   ```bash
   docker exec -it minimarket_db_replica mariadb -uroot -pp455w0rd -e "SHOW SLAVE STATUS\G"
   ```
   Pastikan `Slave_IO_Running: Yes` dan `Slave_SQL_Running: Yes`.

5. **Jalankan migrasi ke Master saja** (Replica akan otomatis menyalin skema & data via replikasi):
   ```bash
   php artisan migrate --database=mariadb
   ```

## 6. Cara Menulis ke Master / Membaca dari Replica

Tidak perlu kode khusus — cukup gunakan Eloquent/Query Builder seperti biasa:

```php
// Otomatis ke MASTER (perintah tulis)
Product::create([...]);
Sale::where('id', 1)->update([...]);

// Otomatis ke REPLICA (perintah baca)
$products = Product::all();
```

Jika ingin memaksa satu query tertentu membaca langsung dari Master (misalnya untuk laporan yang butuh data ter-update detik itu juga):
```php
DB::connection('mariadb')->getPdo(); // memaksa koneksi write/master pada request tersebut
```

## 7. Cara Menguji Replikasi

1. Tambahkan 1 baris data lewat aplikasi (misalnya tambah Kategori baru lewat Filament).
2. Cek langsung di **Master**:
   ```bash
   docker exec -it minimarket_db_master mariadb -uroot -pp455w0rd minimarket_terdistribusi -e "SELECT * FROM categories ORDER BY id DESC LIMIT 1;"
   ```
3. Cek di **Replica** (tunggu 1–2 detik karena replikasi bersifat asinkron):
   ```bash
   docker exec -it minimarket_db_replica mariadb -uroot -pp455w0rd minimarket_terdistribusi -e "SELECT * FROM categories ORDER BY id DESC LIMIT 1;"
   ```
   Data yang sama harus muncul di kedua node.
4. Uji **read-only** di Replica — perintah tulis langsung ke Replica harus ditolak:
   ```bash
   docker exec -it minimarket_db_replica mariadb -uroot -pp455w0rd minimarket_terdistribusi -e "INSERT INTO categories (name, slug, created_at, updated_at) VALUES ('Tes', 'tes', NOW(), NOW());"
   ```
   Hasil yang diharapkan: error `--read-only` (membuktikan Replica benar-benar read-only dan hanya menerima data lewat replikasi dari Master).

## 8. Catatan untuk Presentasi/Laporan SBDT

- Model distribusi yang dipakai: **Master–Slave (Primary–Replica) Replication**, replikasi **asinkron berbasis GTID**, granularitas **row-based binlog**.
- Tujuan: memisahkan beban baca (laporan, dashboard, pencarian barang — sering & berat) dari beban tulis (transaksi penjualan), serta menyediakan redundansi data.
- Konsistensi: **eventual consistency** pada Replica (ada jeda replikasi beberapa milidetik–detik), namun Master selalu strongly consistent karena satu-satunya sumber tulis.
- Jika perlu multi-Replica untuk load-balancing baca, cukup tambah service baru di `docker-compose.master-replica.yml` dan tambahkan hostnya ke `DB_HOST_READ` (dipisah koma) — Laravel akan memilih replica secara round-robin otomatis.
