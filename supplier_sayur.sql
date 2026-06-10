-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 10 Jun 2026 pada 05.19
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `supplier_sayur`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pengirim` enum('customer','supplier') NOT NULL,
  `pesan` text NOT NULL,
  `dibaca` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `chat`
--

INSERT INTO `chat` (`id`, `user_id`, `pengirim`, `pesan`, `dibaca`, `created_at`) VALUES
(1, 2, 'supplier', 'Halo! Selamat datang di Supplier Sayur Azam Heri 🥬 Ada yang bisa kami bantu?', 1, '2026-05-21 06:38:39'),
(2, 2, 'customer', 'Halo, apakah stok cabai masih ada?', 1, '2026-05-21 06:38:39'),
(3, 2, 'supplier', 'Stok cabai merah masih tersedia 150 kg. Silakan order langsung ya! 😊', 1, '2026-05-21 06:38:39'),
(4, 3, 'customer', 'halo', 1, '2026-06-03 04:25:39'),
(5, 3, 'supplier', 'Selamat datang di Supplier Sayur Azam Heri! Ada yang bisa kami bantu?', 1, '2026-06-03 04:25:39'),
(6, 4, 'customer', 'stok masih ada?', 1, '2026-06-06 00:49:08'),
(7, 4, 'supplier', 'Selamat datang di Supplier Sayur Azam Heri! Ada yang bisa kami bantu?', 1, '2026-06-06 00:49:08'),
(8, 4, 'supplier', 'Saat ini stok masih tersedia.', 0, '2026-06-07 04:56:24'),
(9, 13, 'customer', 'halo', 1, '2026-06-08 22:41:35'),
(10, 13, 'supplier', 'Selamat datang di Supplier Sayur Azam Heri! Ada yang bisa kami bantu?', 1, '2026-06-08 22:41:35');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 'Cabai Merah Keriting', 45000.00, 5, 225000.00),
(2, 1, 4, 'Kol Putih', 12000.00, 5, 60000.00),
(3, 2, 7, 'Wortel', 14000.00, 5, 70000.00),
(4, 2, 8, 'Kentang', 18000.00, 5, 90000.00),
(6, 4, 1, 'Cabai Merah Keriting', 45000.00, 1, 45000.00),
(10, 6, 1, 'Cabai Merah Keriting', 45000.00, 5, 225000.00),
(11, 6, 10, 'Bawang Merah', 32000.00, 4, 128000.00),
(12, 6, 5, 'Bayam Segar', 5000.00, 2, 10000.00),
(13, 6, 9, 'Tomat Segar', 12000.00, 3, 36000.00),
(14, 7, 3, 'Timun Segar', 8000.00, 1, 8000.00),
(15, 7, 4, 'Kol Putih', 12000.00, 1, 12000.00),
(16, 8, 2, 'Cabai Rawit Hijau', 38000.00, 3, 114000.00),
(17, 8, 3, 'Timun Segar', 8000.00, 1, 8000.00),
(18, 9, 5, 'Bayam Segar', 4000.00, 10, 40000.00),
(19, 9, 4, 'Kol Putih', 9000.00, 10, 90000.00),
(20, 10, 1, 'Cabai Merah Keriting', 45000.00, 5, 225000.00),
(21, 10, 4, 'Kol Putih', 12000.00, 3, 36000.00),
(22, 10, 24, 'Sereh', 8000.00, 4, 32000.00),
(23, 10, 21, 'Jahe', 15000.00, 2, 30000.00),
(24, 10, 23, 'Laos (Lengkuas)', 10000.00, 3, 30000.00),
(25, 11, 2, 'Cabai Rawit Hijau', 38000.00, 4, 152000.00),
(26, 11, 3, 'Timun Segar', 8000.00, 3, 24000.00),
(27, 11, 7, 'Wortel', 14000.00, 3, 42000.00),
(28, 11, 8, 'Kentang', 18000.00, 5, 90000.00),
(29, 11, 9, 'Tomat Merah', 12000.00, 2, 24000.00),
(30, 11, 14, 'Bawang Putih', 28000.00, 2, 56000.00),
(31, 12, 11, 'Daun Singkong', 3000.00, 3, 9000.00),
(32, 12, 25, 'Alpukat', 25000.00, 2, 50000.00),
(33, 12, 10, 'Bawang Merah', 32000.00, 3, 96000.00),
(34, 13, 28, 'Jambu Kristal', 18000.00, 2, 36000.00),
(35, 13, 27, 'Stroberi', 45000.00, 2, 90000.00),
(36, 13, 26, 'Buah Naga', 22000.00, 1, 22000.00),
(37, 13, 25, 'Alpukat', 25000.00, 1, 25000.00),
(38, 13, 16, 'Jeruk Peras', 15000.00, 1, 15000.00),
(39, 14, 18, 'Daun Pepaya', 4000.00, 3, 12000.00),
(40, 14, 19, 'Daun Kemangi', 5000.00, 2, 10000.00),
(41, 14, 20, 'Daun Pisang', 3000.00, 3, 9000.00),
(42, 14, 11, 'Daun Singkong', 3000.00, 5, 15000.00),
(43, 15, 27, 'Stroberi', 40000.00, 13, 520000.00),
(44, 15, 28, 'Jambu Kristal', 15000.00, 23, 345000.00),
(45, 16, 31, 'Daun Salam', 3000.00, 1, 3000.00),
(46, 16, 32, 'Daun Jeruk', 4000.00, 1, 4000.00),
(47, 16, 30, 'Cabe Hijau Kecil', 30000.00, 1, 30000.00);

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumentasi`
--

CREATE TABLE `dokumentasi` (
  `id` int(11) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `tipe` enum('url','upload') DEFAULT 'url',
  `urutan` int(11) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `dokumentasi`
--

INSERT INTO `dokumentasi` (`id`, `judul`, `keterangan`, `gambar`, `tipe`, `urutan`, `aktif`, `created_at`) VALUES
(1, 'Pemilihan Sayuran di Pasar Induk', 'Pemilihan langsung di pasar', 'dok_1780457409_449.jpeg', 'upload', 1, 1, '2026-05-21 06:38:39'),
(2, 'Proses Penyortiran & QC', 'Sortir kualitas terbaik', 'dok_1780445180_752.jpeg', 'upload', 2, 1, '2026-05-21 06:38:39'),
(3, 'Pengemasan Higienis', 'Kemas bersih & rapi', 'dok_1780446343_263.jpeg', 'upload', 3, 1, '2026-05-21 06:38:39'),
(4, 'Distribusi ke Warung', 'Pengiriman barang', 'dok_1780446385_300.jpeg', 'upload', 4, 1, '2026-05-21 06:38:39'),
(5, 'Sayuran Segar Siap Jual', 'Segar sampai di tangan Anda', 'dok_1780445257_334.jpeg', 'upload', 5, 1, '2026-05-21 06:38:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(10) DEFAULT '?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama`, `slug`, `icon`) VALUES
(1, 'Sayuran Hijau', 'sayuran-hijau', '🥬'),
(2, 'Bumbu & Cabai', 'bumbu', '🌶️'),
(3, 'Umbi & Akar', 'umbi', '🥔'),
(4, 'Buah Sayur', 'buah-sayur', '🍅'),
(5, 'Buah Segar', 'buah-segar', '🍓'),
(6, 'Rempah & Empon', 'rempah', '🫚');

-- --------------------------------------------------------

--
-- Struktur dari tabel `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `keranjang`
--

INSERT INTO `keranjang` (`id`, `user_id`, `produk_id`, `jumlah`, `created_at`) VALUES
(31, 3, 1, 5, '2026-06-07 00:07:15'),
(32, 3, 10, 4, '2026-06-07 00:07:15'),
(33, 3, 5, 2, '2026-06-07 00:07:15'),
(34, 3, 9, 3, '2026-06-07 00:07:15');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengaturan`
--

CREATE TABLE `pengaturan` (
  `id` int(11) NOT NULL,
  `kunci` varchar(100) NOT NULL,
  `nilai` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pengaturan`
--

INSERT INTO `pengaturan` (`id`, `kunci`, `nilai`, `updated_at`) VALUES
(1, 'qris_file', '', '2026-05-29 01:19:14'),
(2, 'qris_nama', 'Azam Heri Supplier', '2026-05-29 01:19:14'),
(3, 'qris_hp', '', '2026-05-29 01:19:14'),
(10, 'hero_bg_file', 'hero_1780402493.webp', '2026-06-02 12:14:53'),
(15, 'hero_bg_url', '', '2026-05-30 09:45:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `kode_pesanan` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_penerima` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `metode_bayar` varchar(50) DEFAULT 'Virtual Account',
  `nomor_va` varchar(30) DEFAULT NULL,
  `status` enum('menunggu_bayar','diproses','dikirim','selesai','dibatalkan') DEFAULT 'menunggu_bayar',
  `alasan_batal` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `dibatal_oleh` enum('admin','customer') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode_pesanan`, `user_id`, `nama_penerima`, `no_hp`, `alamat_pengiriman`, `catatan`, `total_harga`, `metode_bayar`, `nomor_va`, `status`, `alasan_batal`, `created_at`, `updated_at`, `dibatal_oleh`) VALUES
(1, 'ORD-2025-001', 2, 'Ibu Sari', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', NULL, 285000.00, 'Virtual Account', '8801234567890001', 'selesai', NULL, '2026-05-21 06:38:39', '2026-05-21 06:38:39', NULL),
(2, 'ORD-2025-002', 2, 'Ibu Sari', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', NULL, 150000.00, 'Virtual Account', '8801234567890002', 'selesai', '', '2026-05-21 06:38:39', '2026-06-08 23:08:17', NULL),
(4, 'ORD-2026-9A6702', 2, 'Ibu Sari', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', '', 45000.00, 'Virtual Account Bank Mandiri', '88017285091697', 'selesai', '', '2026-05-30 09:47:05', '2026-06-08 23:08:09', NULL),
(6, 'ORD-2026-914E3F', 3, 'dimas', '085423678543', 'WWWWW', '', 399000.00, 'Virtual Account Bank Mandiri', '88013596621007', 'selesai', '', '2026-06-03 03:57:13', '2026-06-03 03:57:57', NULL),
(7, 'ORD-2026-83316E', 2, 'Ibu Sari', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', '', 20000.00, 'Bayar di Tempat (COD)', NULL, 'dibatalkan', 'stok habis', '2026-06-03 04:33:44', '2026-06-03 04:43:45', 'admin'),
(8, 'ORD-2026-ABED1D', 2, 'Ibu Sari', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', '', 122000.00, 'Virtual Account Bank Mandiri', '88013161141369', 'diproses', NULL, '2026-06-03 14:31:38', '2026-06-10 02:21:55', NULL),
(9, 'ORD-2026-5C571D', 3, 'dimas', '085423678543', 'Binu-kampus kijang, kemanggisan', '', 130000.00, 'Bayar di Tempat (COD)', NULL, 'dibatalkan', 'Tidak jadi beli', '2026-06-06 00:31:33', '2026-06-06 00:31:51', 'customer'),
(10, 'ORD-2026-32F71E', 4, 'Budi Santoso', '081312345601', 'Jl. Mawar No. 3, Depok', 'antar jam 6 pagi', 353000.00, 'Virtual Account Bank Mandiri', '88013361170348', 'selesai', '', '2026-06-06 00:50:59', '2026-06-07 04:54:33', NULL),
(11, 'ORD-2026-FA4EDB', 8, 'Rudi Hermawan', '081312345605', 'Jl. Flamboyan No. 9, Bandung', '', 388000.00, 'Virtual Account Bank BCA', '88015381603397', 'selesai', '', '2026-06-06 00:53:51', '2026-06-10 02:22:52', NULL),
(12, 'ORD-2026-92577A', 3, 'dimas', '085423678543', 'Kampus kijang-Binus, Kemanggisan', '', 155000.00, 'Virtual Account Bank BNI', '88010453136585', 'selesai', '', '2026-06-07 00:07:05', '2026-06-08 23:07:58', NULL),
(13, 'ORD-2026-48DEFF', 13, 'Fitri Handayani', '081312345610', 'Jl. Seruni No. 2, Tangerang Selatan', '', 188000.00, 'Virtual Account Bank BRI', '88015907033870', 'dikirim', '', '2026-06-08 22:45:56', '2026-06-10 02:23:02', NULL),
(14, 'ORD-2026-110BF2', 12, 'Agus Setiawan', '081312345609', 'Jl. Teratai No. 6, Bogor', 'antar jam 5 pagi', 46000.00, 'Bayar di Tempat (COD)', NULL, 'dikirim', '', '2026-06-08 23:06:41', '2026-06-08 23:07:47', NULL),
(15, 'ORD-2026-47BA49', 9, 'Ani Kusuma', '081312345606', 'Jl. Dahlia No. 11, Jakarta Timur', 'warung ada disamping indomaret', 865000.00, 'Virtual Account Bank Mandiri', '88016979428468', 'diproses', NULL, '2026-06-08 23:15:16', '2026-06-09 03:45:02', NULL),
(16, 'ORD-2026-9647E8', 11, 'Rina Sulistyo', '081312345608', 'Jl. Kamboja No. 18, Depok', '', 37000.00, 'Bayar di Tempat (COD)', NULL, 'diproses', '', '2026-06-10 02:16:41', '2026-06-10 02:21:44', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `produk`
--

CREATE TABLE `produk` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(10,2) NOT NULL,
  `harga_grosir` decimal(10,2) DEFAULT NULL,
  `min_grosir` int(11) DEFAULT 10,
  `stok` int(11) DEFAULT 0,
  `satuan` varchar(20) DEFAULT 'kg',
  `gambar` varchar(255) DEFAULT 'default.jpg',
  `badge` varchar(50) DEFAULT 'Segar',
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `produk`
--

INSERT INTO `produk` (`id`, `kategori_id`, `nama`, `deskripsi`, `harga`, `harga_grosir`, `min_grosir`, `stok`, `satuan`, `gambar`, `badge`, `status`, `created_at`) VALUES
(1, 2, 'Cabai Merah Keriting', 'Cabai merah segar dari petani lokal, pedas dan aromatik. Cocok untuk sambal dan masakan berbumbu.', 45000.00, 40000.00, 10, 79, 'kg', 'produk_1780630287.jpg', 'Best Seller', 'aktif', '2026-05-21 06:38:39'),
(2, 2, 'Cabai Rawit Hijau', 'Cabai rawit hijau segar, tingkat kepedasan tinggi. Cocok untuk sambal hijau dan pelengkap masakan.', 38000.00, 34000.00, 10, 51, 'kg', 'produk_1780630262.webp', 'Segar', 'aktif', '2026-05-21 06:38:39'),
(3, 4, 'Timun Segar', 'Timun lokal pilihan, renyah dan segar. Cocok untuk lalapan, salad, dan minuman segar.', 8000.00, 6500.00, 10, 96, 'kg', 'produk_1780630238.webp', 'Segar', 'aktif', '2026-05-21 06:38:39'),
(4, 1, 'Kol Putih', 'Kol segar berkualitas tinggi. Cocok untuk cap cay, sop, dan aneka tumisan.', 12000.00, 9000.00, 10, 37, 'kg', 'produk_1780630218.jpg', 'Promo', 'aktif', '2026-05-21 06:38:39'),
(5, 1, 'Bayam Segar', 'Bayam pilihan segar, kaya zat besi dan nutrisi. Cocok untuk sayur bening dan tumis.', 5000.00, 4000.00, 10, 88, 'kg', 'produk_1780630201.jpg', 'Organik', 'aktif', '2026-05-21 06:38:39'),
(6, 1, 'Kangkung', 'Kangkung air segar langsung dari kebun. Cocok untuk tumis kangkung dan cap cay.', 4000.00, 3200.00, 10, 50, 'kg', 'produk_1780630175.jpg', 'Segar', 'aktif', '2026-05-21 06:38:39'),
(7, 3, 'Wortel', 'Wortel manis segar dari Cipanas, ukuran premium. Kaya vitamin A untuk kesehatan mata.', 14000.00, 11000.00, 10, 87, 'kg', 'produk_1780631368.jpg', 'Premium', 'aktif', '2026-05-21 06:38:39'),
(8, 3, 'Kentang', 'Kentang granola segar, cocok untuk berbagai masakan seperti sop, balado, dan goreng.', 18000.00, 15000.00, 10, 46, 'kg', 'produk_1780631330.jpg', 'Best Seller', 'aktif', '2026-05-21 06:38:39'),
(9, 4, 'Tomat Merah', 'Tomat merah matang sempurna dari petani lokal. Cocok untuk sambal, sop, dan jus.', 12000.00, 9500.00, 10, 66, 'kg', 'produk_1780630118.jpg', 'Segar', 'aktif', '2026-05-21 06:38:39'),
(10, 2, 'Bawang Merah', 'Bawang merah lokal pilihan, kering dan harum. Bumbu wajib untuk semua masakan Indonesia.', 32000.00, 28000.00, 10, 93, 'kg', 'produk_1780631347.jpg', 'Segar', 'aktif', '2026-05-21 06:38:39'),
(11, 1, 'Daun Singkong', 'Daun singkong muda segar. Cocok untuk gulai, santan, dan sayur masak lemak.', 3000.00, 2500.00, 10, 22, 'kg', 'produk_1780631309.png', 'Murah', 'aktif', '2026-05-21 06:38:39'),
(12, 4, 'Terong Ungu', 'Terong ungu segar berkualitas tinggi. Cocok untuk balado terong dan aneka masakan.', 9000.00, 7000.00, 10, 0, 'kg', 'produk_1780629104.jpg', '', 'aktif', '2026-05-21 06:38:39'),
(13, 2, 'Cabe Setan (Ori)', 'Cabe setan asli, tingkat kepedasan ekstrem. Cocok untuk sambal dan masakan pedas level tinggi.', 55000.00, 50000.00, 10, 36, 'kg', 'produk_1780629081.png', 'Panas Banget', 'aktif', '2026-06-05 00:45:55'),
(14, 2, 'Bawang Putih', 'Bawang putih lokal pilihan, harum dan segar. Bumbu dasar wajib untuk semua masakan Indonesia.', 28000.00, 24000.00, 10, 98, 'kg', 'produk_1780629060.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(15, 4, 'Tomat Hijau', 'Tomat hijau segar, rasa sedikit asam. Cocok untuk sambal tomat hijau dan pelengkap masakan.', 10000.00, 8000.00, 10, 60, 'kg', 'produk_1780629035.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(16, 4, 'Jeruk Peras', 'Jeruk peras lokal segar, kaya vitamin C. Cocok untuk minuman segar dan campuran masakan.', 15000.00, 12000.00, 10, 74, 'kg', 'produk_1780629012.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(17, 4, 'Jeruk Nipis', 'Jeruk nipis segar, asam dan harum. Cocok untuk minuman, sambal, dan penyedap masakan.', 12000.00, 9500.00, 10, 65, 'kg', 'produk_1780628989.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(18, 1, 'Daun Pepaya', 'Daun pepaya muda segar, cocok untuk lalapan, tumis, dan masakan tradisional penuh gizi.', 4000.00, 3000.00, 10, 42, 'kg', 'produk_1780628961.jpg', 'Organik', 'aktif', '2026-06-05 00:45:55'),
(19, 1, 'Daun Kemangi', 'Daun kemangi segar beraroma khas. Cocok untuk lalapan, pepes, dan berbagai masakan Sunda.', 5000.00, 4000.00, 10, 33, 'ikat', 'produk_1780628936.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(20, 1, 'Daun Pisang', 'Daun pisang segar lebar dan lentur. Cocok untuk pembungkus nasi, pepes, dan olahan tradisional.', 3000.00, 2500.00, 10, 27, 'lembar', 'produk_1780628889.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(21, 6, 'Jahe', 'Jahe segar berkualitas, hangat dan menyehatkan. Cocok untuk minuman jahe, masakan, dan jamu.', 15000.00, 12000.00, 10, 58, 'kg', 'produk_1780628858.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(22, 6, 'Kunyit', 'Kunyit segar berwarna kuning cerah. Cocok untuk bumbu kari, jamu kunyit asam, dan pewarna alami.', 12000.00, 9000.00, 10, 39, 'kg', 'produk_1780628826.jpg', 'Organik', 'aktif', '2026-06-05 00:45:55'),
(23, 6, 'Laos (Lengkuas)', 'Lengkuas segar beraroma khas. Bumbu wajib untuk opor, rendang, dan masakan bersantan.', 10000.00, 8000.00, 10, 41, 'kg', 'produk_1780628772.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(24, 6, 'Sereh', 'Sereh segar beraroma harum. Cocok untuk bumbu masakan, minuman wedang, dan pengusir nyamuk alami.', 8000.00, 6000.00, 10, 46, 'ikat', 'produk_1780628721.png', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(25, 5, 'Alpukat', 'Alpukat mentega segar, lembut dan creamy. Cocok untuk jus, es buah, dan topping makanan.', 25000.00, 22000.00, 10, 47, 'kg', 'produk_1780631288.jpg', 'Premium', 'aktif', '2026-06-05 00:45:55'),
(26, 5, 'Buah Naga', 'Buah naga merah segar, manis dan kaya antioksidan. Cocok untuk jus dan campuran es buah.', 22000.00, 19000.00, 10, 34, 'kg', 'produk_1780628650.jpg', 'Segar', 'aktif', '2026-06-05 00:45:55'),
(27, 5, 'Stroberi', 'Stroberi segar dari Lembang, manis asam dan menggugah selera. Cocok untuk jus dan topping kue.', 45000.00, 40000.00, 10, 10, 'kg', 'produk_1780627370.jpg', 'Premium', 'aktif', '2026-06-05 00:45:55'),
(28, 5, 'Jambu Kristal', 'Jambu kristal putih renyah dan manis, hampir tanpa biji. Cocok dimakan langsung atau dibuat jus.', 18000.00, 15000.00, 10, 5, 'kg', 'produk_1780641342.jpg', 'Best Seller', 'aktif', '2026-06-05 00:45:55'),
(30, 2, 'Cabe Hijau Kecil', 'Cabe hijau kecil segar, pedas dan aromatik. Cocok untuk sambal hijau, tumisan, dan pelengkap masakan sehari-hari.', 30000.00, 26000.00, 10, 39, 'kg', 'produk_1781061178.jpg', 'Segar', 'aktif', '2026-06-06 23:01:59'),
(31, 1, 'Daun Salam', 'Daun salam segar beraroma khas. Bumbu wajib untuk masakan berkuah seperti rendang, gulai, semur, dan nasi uduk.', 3000.00, 2500.00, 10, 29, 'ikat', 'produk_1781061140.png', 'Segar', 'aktif', '2026-06-06 23:01:59'),
(32, 1, 'Daun Jeruk', 'Daun jeruk purut segar beraroma harum. Cocok untuk bumbu opor, rendang, tom yam, dan berbagai masakan berbumbu rempah.', 4000.00, 3000.00, 10, 22, 'ikat', 'produk_1781061090.jpg', 'Segar', 'aktif', '2026-06-06 23:01:59'),
(33, 5, 'Pisang', 'Pisang kepok/Raja segar berkualitas. Cocok untuk dikonsumsi langsung, digoreng, kolak, atau bahan kue tradisional.', 12000.00, 10000.00, 10, 60, 'kg', 'produk_1781061065.jpg', 'Segar', 'aktif', '2026-06-06 23:01:59'),
(34, 5, 'Semangka', 'Semangka merah segar manis, daging tebal dan berair. Cocok untuk jus, rujak, es buah, dan konsumsi langsung.', 8000.00, 6500.00, 10, 75, 'kg', 'produk_1781061011.jpg', 'Manis', 'aktif', '2026-06-06 23:01:59'),
(35, 2, 'Bawang Kating', 'Bawang kating (bawang putih tunggal) pilihan, beraroma kuat dan tajam. Bumbu spesial untuk masakan rendang dan gulai.', 35000.00, 30000.00, 10, 35, 'kg', 'produk_1781060976.jpg', 'Premium', 'aktif', '2026-06-06 23:01:59');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bintang` tinyint(1) NOT NULL CHECK (`bintang` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `rating`
--

INSERT INTO `rating` (`id`, `pesanan_id`, `user_id`, `bintang`, `komentar`, `created_at`) VALUES
(1, 1, 2, 5, 'Sayurannya selalu segar dan tepat waktu! Sangat puas dengan pelayanannya.', '2026-05-21 06:38:39'),
(2, 6, 3, 4, 'sayuran cukup segar, pengiriman cepat, pembayaran blum bisa cash', '2026-06-03 04:24:21'),
(3, 12, 3, 5, 'sayuran masih segar dalam perjalanan', '2026-06-09 03:42:54'),
(4, 2, 2, 5, 'sayur aman gk ada yg kurang, pengiriman cepat', '2026-06-10 02:26:03'),
(5, 4, 2, 5, 'cabe masih segar', '2026-06-10 02:26:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_stok`
--

CREATE TABLE `riwayat_stok` (
  `id` int(11) NOT NULL,
  `produk_id` int(11) NOT NULL,
  `jenis` enum('masuk','keluar') NOT NULL,
  `jumlah` int(11) NOT NULL,
  `stok_awal` int(11) NOT NULL,
  `stok_akhir` int(11) NOT NULL,
  `keterangan` varchar(200) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `riwayat_stok`
--

INSERT INTO `riwayat_stok` (`id`, `produk_id`, `jenis`, `jumlah`, `stok_awal`, `stok_akhir`, `keterangan`, `created_at`, `admin_id`) VALUES
(1, 35, 'keluar', 35, 70, 35, 'Update stok via admin', '2026-06-07 06:12:28', NULL),
(2, 34, 'keluar', 75, 150, 75, 'Update stok via admin', '2026-06-07 06:13:47', NULL),
(3, 33, 'keluar', 60, 120, 60, 'Update stok via admin', '2026-06-07 06:15:28', NULL),
(4, 32, 'keluar', 27, 50, 23, 'Update stok via admin', '2026-06-07 06:18:40', NULL),
(5, 31, 'keluar', 30, 60, 30, 'Update stok via admin', '2026-06-07 06:19:31', NULL),
(6, 30, 'keluar', 40, 80, 40, 'Update stok via admin', '2026-06-07 06:20:02', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_warung` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `foto_profil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `nama_warung`, `email`, `password`, `no_hp`, `alamat`, `role`, `foto`, `created_at`, `foto_profil`) VALUES
(1, 'Admin Azam Heri', 'Supplier Sayur Azam Heri', 'admin@suppliersayur.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'Jl. H. Salihun No. 33, RT03, RW03, Kebon Jeruk Jakarta Barat, DKI Jakarta', 'admin', NULL, '2026-05-21 06:38:38', NULL),
(2, 'Ibu Sari', 'Warung Sari', 'sari@warung.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081298765432', 'Jl. Raya Bekasi No. 5, Bekasi', 'customer', NULL, '2026-05-21 06:38:38', NULL),
(3, 'dimas', 'Warung Sumber Rezeki', 'sumberrezeki@mail.com', '$2y$10$CTV74hTzDy3ovGGv0qFDYusFOz8qG68mkhfahr0dLuvMAix1VI/Nm', '085423678543', 'Kampus kijang-Binus, Kemanggisan', 'customer', NULL, '2026-06-03 03:42:09', NULL),
(4, 'Budi Santoso', 'Warung Budi Jaya', 'budijaya@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345601', 'Jl. Mawar No. 3, Depok', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(5, 'Dewi Rahayu', 'Warung Dewi Rejeki', 'dewireeki@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345602', 'Jl. Melati No. 7, Bogor', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(6, 'Hendra Wijaya', 'Warung Hendra Makmur', 'hendramakmur@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345603', 'Jl. Kenanga No. 15, Tangerang', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(7, 'Siti Aminah', 'Warung Siti Barokah', 'sitibarokah@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345604', 'Jl. Anggrek No. 22, Bekasi', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(8, 'Rudi Hermawan', 'Warung Rudi Sejahtera', 'rudisejahtera@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345605', 'Jl. Flamboyan No. 9, Bandung', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(9, 'Ani Kusuma', 'Warung Ani Berkah', 'anikerkah@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345606', 'Jl. Dahlia No. 11, Jakarta Timur', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(10, 'Joko Prabowo', 'Warung Joko Lestari', 'jokolestari@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345607', 'Jl. Cempaka No. 4, Cikarang', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(11, 'Rina Sulistyo', 'Warung Rina Mandiri', 'rinamandiri@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345608', 'Jl. Kamboja No. 18, Depok', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(12, 'Agus Setiawan', 'Warung Agus Sentosa', 'agussentosa@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345609', 'Jl. Teratai No. 6, Bogor', 'customer', NULL, '2026-06-05 00:45:55', NULL),
(13, 'Fitri Handayani', 'Warung Fitri Nusantara', 'fitrinusantara@mail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081312345610', 'Jl. Seruni No. 2, Tangerang Selatan', 'customer', NULL, '2026-06-05 00:45:55', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indeks untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_produk` (`user_id`,`produk_id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kunci` (`kunci`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produk_id` (`produk_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT untuk tabel `dokumentasi`
--
ALTER TABLE `dokumentasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `pengaturan`
--
ALTER TABLE `pengaturan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=362;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT untuk tabel `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `chat`
--
ALTER TABLE `chat`
  ADD CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_ibfk_2` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `riwayat_stok`
--
ALTER TABLE `riwayat_stok`
  ADD CONSTRAINT `riwayat_stok_ibfk_1` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
