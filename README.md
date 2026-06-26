# 🥬 Supplier Sayur Azam Heri
## Panduan Instalasi XAMPP

---

## 📋 Persyaratan
- XAMPP (PHP 7.4+ / 8.x, MySQL 5.7+, Apache)
- Browser modern (Chrome, Firefox, Edge)

---

## 🚀 Langkah Instalasi

### 1. Salin Folder ke XAMPP
```
Salin folder `supplier-sayur` ke:
  Windows : C:\xampp\htdocs\supplier-sayur
  Mac/Linux: /opt/lampp/htdocs/supplier-sayur
```

### 2. Buat Database
1. Buka browser → http://localhost/phpmyadmin
2. Klik **"New"** di panel kiri
3. Nama database: `supplier_sayur` → klik **Create**
4. Pilih database `supplier_sayur` → klik tab **Import**
5. Klik **Choose File** → pilih file `database.sql` dari folder ini
6. Klik **Go** / **Import**

### 3. Konfigurasi Koneksi
Buka file `includes/config.php` dan sesuaikan jika perlu:
```php
define('DB_HOST', 'localhost');   // biasanya tidak perlu diubah
define('DB_USER', 'root');        // username MySQL XAMPP
define('DB_PASS', '');            // password (default XAMPP kosong)
define('DB_NAME', 'supplier_sayur');
define('APP_URL', 'http://localhost/supplier-sayur');
```

### 4. Jalankan Aplikasi
1. Buka **XAMPP Control Panel**
2. Start **Apache** dan **MySQL**
3. Buka browser → **http://localhost/supplier-sayur**

---

## 🔐 Akun Default

| Role     | Email                        | Password   |
|----------|------------------------------|------------|
| Admin    | admin@suppliersayur.com      | admin123   |
| Customer | sari@warung.com              | demo123    |

---

## 📁 Struktur Folder

```
supplier-sayur/
├── index.php               ← Homepage
├── login.php               ← Halaman login
├── register.php            ← Halaman daftar
├── logout.php              ← Proses logout
├── produk.php              ← Katalog produk
├── keranjang.php           ← Keranjang belanja
├── checkout.php            ← Proses pemesanan
├── pesanan.php             ← Riwayat & status pesanan
├── chat.php                ← Chat dengan supplier
├── kontak.php              ← Halaman kontak
├── profil.php              ← Edit profil user
├── database.sql            ← File SQL database
│
├── includes/
│   ├── config.php          ← Konfigurasi + helper functions
│   ├── header.php          ← Navbar + head HTML
│   └── footer.php          ← Footer + scripts
│
├── ajax/
│   └── keranjang.php       ← Handler AJAX keranjang
│
├── admin/
│   ├── index.php           ← Dashboard admin
│   ├── produk.php          ← Kelola produk
│   ├── pesanan.php         ← Kelola pesanan
│   ├── laporan.php         ← Laporan & export CSV
│   ├── customers.php       ← Data customers
│   ├── chat_admin.php      ← Balas pesan customer
│   └── includes/
│       ├── admin_header.php
│       └── admin_footer.php
│
├── assets/
│   ├── css/
│   │   └── main.css        ← Stylesheet utama
│   ├── js/
│   │   └── main.js         ← JavaScript utama
│   └── img/
│       └── default-product.svg
│
└── uploads/
    └── produk/             ← Foto produk yang diupload
```

---

## 🎯 Fitur Lengkap

### Customer (Warung)
- ✅ Register & Login akun warung
- ✅ Browse produk dengan filter kategori
- ✅ Pencarian produk
- ✅ Tambah produk ke keranjang
- ✅ Harga grosir otomatis (min. 10 kg)
- ✅ Checkout dengan data profil otomatis
- ✅ Generate nomor Virtual Account
- ✅ Tracking status pesanan (timeline visual)
- ✅ Chat langsung dengan supplier
- ✅ Rating & ulasan setelah pesanan selesai
- ✅ Edit profil

### Admin (Supplier)
- ✅ Dashboard statistik & grafik penjualan
- ✅ Kelola produk (CRUD + upload gambar)
- ✅ Monitor stok hampir habis
- ✅ Kelola & update status pesanan
- ✅ Batalkan pesanan + kembalikan stok otomatis
- ✅ Laporan transaksi bulanan
- ✅ Export laporan ke CSV
- ✅ Data customers
- ✅ Balas pesan chat customer

---

## 🛠️ Troubleshooting

**Error koneksi database:**
- Pastikan MySQL sudah dijalankan di XAMPP
- Cek konfigurasi di `includes/config.php`

**Halaman tidak ditemukan:**
- Pastikan folder ada di `htdocs/supplier-sayur`
- Cek `APP_URL` di `includes/config.php`

**Upload gambar gagal:**
- Pastikan folder `uploads/produk/` ada dan writable
- Beri permission: `chmod 755 uploads/produk/` (Linux/Mac)

**Session tidak berfungsi:**
- Pastikan tidak ada output sebelum `session_start()`
- Cek error di XAMPP error log

---

## 📞 Kontak
Supplier Sayur Azam Heri
📱 0812-3456-7890
✉️ info@suppliersayur.com
