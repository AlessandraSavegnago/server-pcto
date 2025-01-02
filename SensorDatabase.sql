-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Creato il: Gen 02, 2025 alle 15:42
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
-- Database: `SensorDatabase`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `lampada`
--

CREATE TABLE `lampada` (
  `idLampada` int(4) UNSIGNED ZEROFILL NOT NULL,
  `tipoLampada` int(3) NOT NULL CHECK (`tipoLampada` >= 30 and `tipoLampada` <= 300)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `lampada`
--

INSERT INTO `lampada` (`idLampada`, `tipoLampada`) VALUES
(0001, 30),
(0002, 100),
(0003, 110),
(0004, 120);

-- --------------------------------------------------------

--
-- Struttura della tabella `lampada_sensore`
--

CREATE TABLE `lampada_sensore` (
  `idLampada` int(4) UNSIGNED ZEROFILL NOT NULL,
  `idSensore` int(4) UNSIGNED ZEROFILL NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `lampada_sensore`
--

INSERT INTO `lampada_sensore` (`idLampada`, `idSensore`, `timestamp`) VALUES
(0001, 1001, '2024-11-28 12:09:00'),
(0001, 1002, '2024-11-28 12:09:00'),
(0002, 1002, '2024-11-28 14:42:43'),
(0003, 1001, '2024-11-28 07:17:22'),
(0003, 1003, '2024-11-28 07:17:22'),
(0004, 1002, '2024-11-28 11:22:42'),
(0004, 1003, '2024-11-28 11:22:42');

-- --------------------------------------------------------

--
-- Struttura della tabella `sensore`
--

CREATE TABLE `sensore` (
  `idSensore` int(4) UNSIGNED ZEROFILL NOT NULL,
  `percentuale` int(3) NOT NULL CHECK (`percentuale` >= 1 and `percentuale` <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `sensore`
--

INSERT INTO `sensore` (`idSensore`, `percentuale`) VALUES
(1001, 30),
(1002, 10),
(1003, 100);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `lampada`
--
ALTER TABLE `lampada`
  ADD PRIMARY KEY (`idLampada`);

--
-- Indici per le tabelle `lampada_sensore`
--
ALTER TABLE `lampada_sensore`
  ADD PRIMARY KEY (`idLampada`,`idSensore`),
  ADD KEY `idSensore` (`idSensore`);

--
-- Indici per le tabelle `sensore`
--
ALTER TABLE `sensore`
  ADD PRIMARY KEY (`idSensore`);

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `lampada_sensore`
--
ALTER TABLE `lampada_sensore`
  ADD CONSTRAINT `lampada_sensore_ibfk_1` FOREIGN KEY (`idLampada`) REFERENCES `lampada` (`idLampada`) ON DELETE CASCADE,
  ADD CONSTRAINT `lampada_sensore_ibfk_2` FOREIGN KEY (`idSensore`) REFERENCES `sensore` (`idSensore`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
