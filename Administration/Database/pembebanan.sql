-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 20 Feb 2024 pada 09.55
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
-- Database: `pembebanan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admindata`
--

CREATE TABLE `admindata` (
  `AdminID` int(11) NOT NULL,
  `AdminName` varchar(50) NOT NULL,
  `AdminUserName` varchar(50) NOT NULL,
  `AdminPassword` varchar(60) NOT NULL,
  `AdminLevel` varchar(30) NOT NULL,
  `AdminStatus` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bendahara`
--

CREATE TABLE `bendahara` (
  `BendaharaID` varchar(50) NOT NULL,
  `TransaksiID` int(11) NOT NULL,
  `AdminID` int(11) NOT NULL,
  `CatatanBendahara` varchar(50) NOT NULL,
  `TanggalBayarBendahara` datetime NOT NULL,
  `PICSelesaiTransfer` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembebanan`
--

CREATE TABLE `pembebanan` (
  `PembebananID` varchar(50) NOT NULL,
  `TransaksiID` int(11) DEFAULT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `Bisma` varchar(50) DEFAULT NULL,
  `SumberDana` varchar(50) DEFAULT NULL,
  `Akun` varchar(50) DEFAULT NULL,
  `Detail` varchar(50) DEFAULT NULL,
  `Anggaran` float DEFAULT NULL,
  `Realisasi` float DEFAULT NULL,
  `TotalRealisasi` float DEFAULT NULL,
  `Saldo` float DEFAULT NULL,
  `TanggalSelesaiPembebanan` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `spm`
--

CREATE TABLE `spm` (
  `SPMID` varchar(50) NOT NULL,
  `TransaksiID` int(11) NOT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `KetPengajuan` varchar(200) DEFAULT NULL,
  `NomorSPM` varchar(50) DEFAULT NULL,
  `TanggalSPM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `TransaksiID` int(11) NOT NULL,
  `userid` int(11) NOT NULL,
  `TanggalPengajuan` datetime DEFAULT current_timestamp(),
  `NomorSTJenisKeg` varchar(50) NOT NULL,
  `Catatan` varchar(200) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `userdata`
--

CREATE TABLE `userdata` (
  `UserID` int(11) NOT NULL,
  `fullName` varchar(250) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `PIC` blob DEFAULT NULL,
  `phoneNumber` varchar(13) DEFAULT NULL,
  `email` varchar(30) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(60) DEFAULT NULL,
  `bidang` varchar(250) DEFAULT NULL,
  `address` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `verification`
--

CREATE TABLE `verification` (
  `VerificationID` varchar(50) NOT NULL,
  `TransaksiID` int(11) NOT NULL,
  `PembebananID` varchar(50) NOT NULL,
  `AdminID` int(11) DEFAULT NULL,
  `TanggalDiserahkan` datetime DEFAULT NULL,
  `TanggalSelesaiVerifikasi` datetime DEFAULT NULL,
  `TanggalSelesaiTTD` datetime DEFAULT NULL,
  `CatatanVerifikasi` varchar(250) DEFAULT NULL,
  `UpdateStatusSPJ` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admindata`
--
ALTER TABLE `admindata`
  ADD PRIMARY KEY (`AdminID`),
  ADD UNIQUE KEY `AdminUserName` (`AdminUserName`);

--
-- Indeks untuk tabel `bendahara`
--
ALTER TABLE `bendahara`
  ADD PRIMARY KEY (`BendaharaID`),
  ADD KEY `fk_AdminBen` (`AdminID`),
  ADD KEY `fk_TransakBen` (`TransaksiID`);

--
-- Indeks untuk tabel `pembebanan`
--
ALTER TABLE `pembebanan`
  ADD PRIMARY KEY (`PembebananID`),
  ADD KEY `fk_TransakPem` (`TransaksiID`),
  ADD KEY `fk_AdminAng` (`AdminID`);

--
-- Indeks untuk tabel `spm`
--
ALTER TABLE `spm`
  ADD PRIMARY KEY (`SPMID`),
  ADD KEY `TransaksiID` (`TransaksiID`),
  ADD KEY `fk_AdminSPM` (`AdminID`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`TransaksiID`),
  ADD KEY `transaksi_fk` (`userid`);

--
-- Indeks untuk tabel `userdata`
--
ALTER TABLE `userdata`
  ADD PRIMARY KEY (`UserID`);

--
-- Indeks untuk tabel `verification`
--
ALTER TABLE `verification`
  ADD PRIMARY KEY (`VerificationID`),
  ADD KEY `TransaksiID` (`TransaksiID`),
  ADD KEY `PembebananID` (`PembebananID`),
  ADD KEY `fk_AdminVer` (`AdminID`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `TransaksiID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `userdata`
--
ALTER TABLE `userdata`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bendahara`
--
ALTER TABLE `bendahara`
  ADD CONSTRAINT `fk_AdminBen` FOREIGN KEY (`AdminID`) REFERENCES `admindata` (`AdminID`),
  ADD CONSTRAINT `fk_TransakBen` FOREIGN KEY (`TransaksiID`) REFERENCES `transaksi` (`TransaksiID`);

--
-- Ketidakleluasaan untuk tabel `pembebanan`
--
ALTER TABLE `pembebanan`
  ADD CONSTRAINT `fk_AdminAng` FOREIGN KEY (`AdminID`) REFERENCES `admindata` (`AdminID`),
  ADD CONSTRAINT `fk_TransakPem` FOREIGN KEY (`TransaksiID`) REFERENCES `transaksi` (`TransaksiID`);

--
-- Ketidakleluasaan untuk tabel `spm`
--
ALTER TABLE `spm`
  ADD CONSTRAINT `fk_AdminSPM` FOREIGN KEY (`AdminID`) REFERENCES `admindata` (`AdminID`),
  ADD CONSTRAINT `spm_ibfk_1` FOREIGN KEY (`TransaksiID`) REFERENCES `transaksi` (`TransaksiID`);

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_fk` FOREIGN KEY (`userid`) REFERENCES `userdata` (`UserID`);

--
-- Ketidakleluasaan untuk tabel `verification`
--
ALTER TABLE `verification`
  ADD CONSTRAINT `fk_AdminVer` FOREIGN KEY (`AdminID`) REFERENCES `admindata` (`AdminID`),
  ADD CONSTRAINT `verification_ibfk_1` FOREIGN KEY (`TransaksiID`) REFERENCES `transaksi` (`TransaksiID`),
  ADD CONSTRAINT `verification_ibfk_2` FOREIGN KEY (`PembebananID`) REFERENCES `pembebanan` (`PembebananID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
