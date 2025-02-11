-- phpMyAdmin SQL Dump
-- Serverversion: 10.11.6-MariaDB-0+deb12u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databas: `fbg_intranet_prod`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `fbg_evolution_copy`
--

CREATE TABLE `fbg_evolution_copy` (
  `document` varchar(36) NOT NULL,
  `folder` varchar(36) NOT NULL,
  `version` varchar(10) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='Lista över lokala kopior av Evolution-dokument';

-- --------------------------------------------------------

--
-- Tabellstruktur `fbg_evolution_documents`
--

CREATE TABLE `fbg_evolution_documents` (
  `id` varchar(36) NOT NULL,
  `folderid` varchar(36) DEFAULT NULL,
  `rootfolderid` varchar(36) DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci DEFAULT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `version` varchar(10) DEFAULT NULL,
  `createdate` datetime NOT NULL DEFAULT current_timestamp(),
  `evolution_type` varchar(36) DEFAULT NULL,
  `documentmeta` longtext DEFAULT NULL,
  `ts_update` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Tabellstruktur `fbg_evolution_folders`
--

CREATE TABLE `fbg_evolution_folders` (
  `id` varchar(36) NOT NULL,
  `parent` varchar(36) NOT NULL,
  `level` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `evolution_type` varchar(36) DEFAULT NULL,
  `unitcode` varchar(10) DEFAULT NULL,
  `publishtype` varchar(10) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `ts_update` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Index för dumpade tabeller
--

--
-- Index för tabell `fbg_evolution_copy`
--
ALTER TABLE `fbg_evolution_copy`
  ADD PRIMARY KEY (`document`),
  ADD KEY `version` (`version`),
  ADD KEY `folder` (`folder`);

--
-- Index för tabell `fbg_evolution_documents`
--
ALTER TABLE `fbg_evolution_documents`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `fbg_evolution_documents` ADD FULLTEXT KEY `name` (`name`);

--
-- Index för tabell `fbg_evolution_folders`
--
ALTER TABLE `fbg_evolution_folders`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
