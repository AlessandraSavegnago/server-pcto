-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 02, 2025 alle 15:41
-- Versione del server: 10.4.28-MariaDB
-- Versione PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `DeviceTreeDatabase`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `devices`
--

CREATE TABLE `devices` (
  `id` int(4) UNSIGNED ZEROFILL NOT NULL,
  `name` varchar(255) NOT NULL,
  `id_sup` int(4) UNSIGNED ZEROFILL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `devices`
--

INSERT INTO `devices` (`id`, `name`, `id_sup`) VALUES
(0001, 'parent', NULL),
(0002, 'device2', 0001),
(0003, 'device234', 0002),
(0004, 'device592', 0002),
(0055, 'device5', 0001),
(0405, 'device1', 0001),
(2040, 'device2040', 0004),
(3020, 'device3020', 0004),
(4040, 'device4040', 0001),
(4140, 'device4040', 0001),
(4440, 'device4040', 0001),
(6000, 'device08', 0405),
(6040, 'devic6040', 0001),
(6968, 'device6968', 0001),
(7800, 'devic98', 6000),
(8940, 'device4040', 0001),
(9810, 'devNi', 0055);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_id_sup` (`id_sup`);

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `fk_id_sup` FOREIGN KEY (`id_sup`) REFERENCES `devices` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
