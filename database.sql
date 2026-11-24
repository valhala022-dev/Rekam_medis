-- =====================================================
-- Database : db_rekam_medis
-- Aplikasi : Sistem Informasi Rekam Medis
-- Import   : mariadb < database.sql
-- =====================================================

CREATE DATABASE IF NOT EXISTS `db_rekam_medis`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_rekam_medis`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `rekam_medis`;
DROP TABLE IF EXISTS `pasien`;
DROP TABLE IF EXISTS `dokter`;
DROP TABLE IF EXISTS `obat`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------- Tabel pasien ----------
CREATE TABLE `pasien` (
  `id_pasien`     INT(11)      NOT NULL AUTO_INCREMENT,
  `no_rm`         VARCHAR(20)  NOT NULL,
  `nama_pasien`   VARCHAR(100) NOT NULL,
  `jenis_kelamin` ENUM('L','P') NOT NULL,
  `tanggal_lahir` DATE         NOT NULL,
  `alamat`        TEXT         NOT NULL,
  PRIMARY KEY (`id_pasien`),
  UNIQUE KEY `uk_no_rm` (`no_rm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Tabel dokter ----------
CREATE TABLE `dokter` (
  `id_dokter`    INT(11)      NOT NULL AUTO_INCREMENT,
  `nama_dokter`  VARCHAR(100) NOT NULL,
  `spesialisasi` VARCHAR(50)  NOT NULL,
  PRIMARY KEY (`id_dokter`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Tabel obat ----------
CREATE TABLE `obat` (
  `id_obat`    INT(11)      NOT NULL AUTO_INCREMENT,
  `nama_obat`  VARCHAR(100) NOT NULL,
  `satuan`     VARCHAR(30)  NOT NULL,
  `stok`       INT(11)      NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_obat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Tabel rekam_medis ----------
-- Relasi FK memakai RESTRICT: data master yang sudah dipakai rekam medis
-- tidak dapat dihapus (aplikasi menampilkan pesan "Gagal: Data sedang digunakan").
CREATE TABLE `rekam_medis` (
  `id_rekam`        INT(11)      NOT NULL AUTO_INCREMENT,
  `id_pasien`       INT(11)      NOT NULL,
  `id_dokter`       INT(11)      NOT NULL,
  `id_obat`         INT(11)      NOT NULL,
  `tanggal_periksa` DATE         NOT NULL,
  `keluhan`         TEXT         NOT NULL,
  `diagnosa`        TEXT         NOT NULL,
  `tindakan`        TEXT         NOT NULL,
  `resep`           VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_rekam`),
  KEY `idx_rm_pasien` (`id_pasien`),
  KEY `idx_rm_dokter` (`id_dokter`),
  KEY `idx_rm_obat`   (`id_obat`),
  CONSTRAINT `fk_rm_pasien` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rm_dokter` FOREIGN KEY (`id_dokter`) REFERENCES `dokter` (`id_dokter`)
    ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_rm_obat`   FOREIGN KEY (`id_obat`)   REFERENCES `obat` (`id_obat`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- DATA UJI (sesuai PRD 3.2)
-- =====================================================
INSERT INTO `pasien` (`no_rm`, `nama_pasien`, `jenis_kelamin`, `tanggal_lahir`, `alamat`) VALUES
('RM-0001', 'Pasien A', 'L', '1995-03-12', 'Jl. Merdeka No. 10, Tuban'),
('RM-0002', 'Pasien B', 'P', '1998-07-25', 'Jl. Sunan Bonang No. 5, Tuban'),
('RM-0003', 'Pasien C', 'L', '1989-11-02', 'Jl. Basuki Rahmat No. 21, Tuban');

INSERT INTO `dokter` (`nama_dokter`, `spesialisasi`) VALUES
('Dr. Andi',  'Umum'),
('Dr. Budi',  'Penyakit Dalam');

INSERT INTO `obat` (`nama_obat`, `satuan`, `stok`) VALUES
('Paracetamol',  'Strip',  100),
('Amoxicillin',  'Tablet', 250),
('Sirup Batuk',  'Botol',   40);

-- Contoh riwayat pemeriksaan agar laporan JOIN & dashboard langsung terisi
INSERT INTO `rekam_medis` (`id_pasien`, `id_dokter`, `id_obat`, `tanggal_periksa`, `keluhan`, `diagnosa`, `tindakan`, `resep`) VALUES
(1, 1, 1, '2026-11-02', 'Demam dan sakit kepala sejak 2 hari', 'Demam viral', 'Pemeriksaan suhu dan istirahat cukup', '3x1 sesudah makan'),
(2, 2, 2, '2026-11-05', 'Nyeri tenggorokan dan sulit menelan', 'Faringitis', 'Pemeriksaan tenggorokan', '3x1 selama 5 hari'),
(3, 1, 3, '2026-11-09', 'Batuk berdahak lebih dari 1 minggu', 'Bronkitis ringan', 'Edukasi dan observasi', '3x1 sendok makan');
