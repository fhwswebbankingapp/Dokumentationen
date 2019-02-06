-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 04. Feb 2019 um 16:24
-- Server-Version: 10.1.36-MariaDB
-- PHP-Version: 7.2.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `onlinebanking`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accountactivation`
--

CREATE TABLE `accountactivation` (
  `ID` int(10) NOT NULL,
  `KUNDE_ID` int(10) NOT NULL,
  `EXPIRY` date NOT NULL,
  `ACTIVATION_KEY` char(128) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tabelle mit den Sekundärschlüsseln zur Aktivierung neuer Act';

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `appsession`
--

CREATE TABLE `appsession` (
  `ID` int(10) NOT NULL,
  `KUNDE_ID` int(10) NOT NULL,
  `SESSION_KEY` char(128) COLLATE utf8_bin NOT NULL,
  `SECOND_SECRET` char(128) COLLATE utf8_bin NOT NULL DEFAULT 'NO_TOKEN',
  `PUSHTOKEN` varchar(512) COLLATE utf8_bin NOT NULL DEFAULT 'NO_TOKEN'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Tabelle fuer Autorisierung und Kommunikation mit der App';

--
-- Daten für Tabelle `appsession`
--

INSERT INTO `appsession` (`ID`, `KUNDE_ID`, `SESSION_KEY`, `SECOND_SECRET`, `PUSHTOKEN`) VALUES
(209, 3, '71e4aee32d68e1b2eeccae17435f9400fc53d8afbdf33074bee980ea4a64c94b0ab6736b8d664b6491535561db19930740c19e81d8b1ff4960ce082018cb5569', 'NO_TOKEN', 'NO_TOKEN'),
(217, 1, '3f4ea155cc8fb3a78edef7b947987dedc695c52e506efddf1237d937dace7d2026895a0db4b41958db65f0563b3abd8f960254636dc521e4b4f814cb7c29f110', 'NO_TOKEN', 'NO_TOKEN'),
(220, 4, '05fee17f38726144eac6cc25ea910b994ec1f8ad4d69ba24479228ce35c4e05876c765252fdd3e0e5559fc09fef1e414a57e61fcdd94ecef5636820dbaff6e95', '5bb428177d304ff2912235f1bafc53dcbd08709bef9eae9f8553a52328efbd9f48619e8a7f6379d983fe0be97cc4c52d41dd635f2d196f591e8b10f0b202816b', 'ExponentPushToken[3KnEu1PPz_W4WV8HSCGbPJ]');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `konto`
--

CREATE TABLE `konto` (
  `IBAN` varchar(25) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `KUNDE_ID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `konto`
--

INSERT INTO `konto` (`IBAN`, `BETRAG`, `KUNDE_ID`) VALUES
('123433', '200.00', 1),
('123456', '410.00', 1),
('234567', '150.00', 2),
('345678', '180.00', 3),
('456789', '200.00', 4),
('DE12500105170648489890', '170.00', 3),
('DK5750510001322617', '80.00', 2),
('EE342200221034126658', '100.00', 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `kunde`
--

CREATE TABLE `kunde` (
  `ID` int(10) NOT NULL,
  `USERNAME` varchar(20) COLLATE utf8_bin NOT NULL,
  `NAME` varchar(20) CHARACTER SET latin1 NOT NULL,
  `VORNAME` varchar(20) CHARACTER SET latin1 NOT NULL,
  `PASSWORD` char(128) CHARACTER SET ascii NOT NULL,
  `EMAIL` varchar(128) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `EXPIRY` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Daten für Tabelle `kunde`
--

INSERT INTO `kunde` (`ID`, `USERNAME`, `NAME`, `VORNAME`, `PASSWORD`, `EMAIL`, `EXPIRY`) VALUES
(1, '', 'Meier', 'Hans', 'B109F3BBBC244EB82441917ED06D618B9008DD09B3BEFD1B5E07394C706A8BB980B1D7785E5976EC049B46DF5F1326AF5A2EA6D103FD07C95385FFAB0CACBC86', '', '0000-00-00 00:00:00'),
(2, '', 'Schmidt', 'Lisa', 'B109F3BBBC244EB82441917ED06D618B9008DD09B3BEFD1B5E07394C706A8BB980B1D7785E5976EC049B46DF5F1326AF5A2EA6D103FD07C95385FFAB0CACBC86', '', '2019-01-18 23:00:00'),
(3, '', 'Mustermann', 'Max', 'B109F3BBBC244EB82441917ED06D618B9008DD09B3BEFD1B5E07394C706A8BB980B1D7785E5976EC049B46DF5F1326AF5A2EA6D103FD07C95385FFAB0CACBC86', '', '2019-01-18 23:00:00'),
(4, '', 'Hermann', 'Heinz', 'B109F3BBBC244EB82441917ED06D618B9008DD09B3BEFD1B5E07394C706A8BB980B1D7785E5976EC049B46DF5F1326AF5A2EA6D103FD07C95385FFAB0CACBC86', '', '2019-01-18 23:00:00'),
(5, '', 'Beutel', 'Dumm', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@nodomain', '2019-01-25 23:00:00'),
(6, '', 'Eimer', 'Hans', '72bd8da345342cd25fd0e3cb5257ed5cff4d7c12ae53b264da5f01cc83a025aa48e1fda3c481123faff3ab281b25bbb5ad3f04ce5646cae239504831c13a60fa', 'email@nodomain', '2019-01-18 23:00:00'),
(7, '', 'Trottel', 'Der', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@dumm', '2019-01-18 23:00:00'),
(8, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'dumm@dumm.deerer', '2019-01-18 23:00:00'),
(9, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdfasdf@sd', '2019-01-18 23:00:00'),
(10, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@wert', '2019-01-18 23:00:00'),
(11, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'saf@erd.es', '2019-01-18 23:00:00'),
(12, '', 'asdfgsdag', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', '234wf@sdas', '2019-01-18 23:00:00'),
(13, '', 'asdfasdgsdgaasdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'jker@dem', '2019-01-18 23:00:00'),
(14, '', 'asdfge', 'asdfg', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@ers.dff', '2019-01-18 23:00:00'),
(15, '', 'asder', 'asdfg', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asddezuu@fgh', '2019-01-18 23:00:00'),
(16, '', 'asdffsadfsadfsadf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'qwer@dasfasdf.asdf', '2019-01-18 23:00:00'),
(17, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'qwer@weesgdgsdsgd', '2019-01-18 23:00:00'),
(18, '', 'asdf', 'asdf23423542345235', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdfsadf@sdafsdfasadf', '2019-01-18 23:00:00'),
(19, '', 'asydffd', 'qwerya', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asfd@werrs', '2019-01-18 23:00:00'),
(20, '', 'istdas', 'wer', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'mail@dumm', '2019-01-18 23:00:00'),
(21, '', 'asasdfsdasdg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'qwer@sdafsdf.dsafsadf', '2019-01-18 23:00:00'),
(22, '', 'asdfsadfasdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@sdaf', '2019-01-18 23:00:00'),
(23, '', 'DÃ¶Ã¶Ã¶del', 'Der', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@dumm.db', '2019-01-18 23:00:00'),
(24, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@dsa', '2019-01-18 23:00:00'),
(25, '', 'asdfdfdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@sdfsdfsdaf', '2019-01-18 23:00:00'),
(26, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@asdf', '2019-01-18 23:00:00'),
(27, '', 'Hanae', 'Flaxton', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'FlaxtonHanae@harakirimail.com', '2019-01-26 23:00:00'),
(28, '', 'Goldia', 'Bonnell', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'BonellGoldia@harakirimail.com', '2019-01-26 23:00:00'),
(29, '', 'asdffg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'LesliByroniana@harakirimail.com', '2019-01-26 23:00:00'),
(30, '', 'asdffg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'LesliByroniana@harakirimail.com', '2019-01-26 23:00:00'),
(31, '', 'asdffg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'LesliByroniana@harakirimail.com', '2019-01-26 23:00:00'),
(32, '', 'asdffg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'LesliByroniana@harakirimail.com', '2019-01-26 23:00:00'),
(33, '', 'asdffg', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'LesliByroniana@harakirimail.com', '2019-01-26 23:00:00'),
(38, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'suto@utooemail.com', '0000-00-00 00:00:00'),
(39, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'suto@utooemail.com', '2019-01-28 23:00:00'),
(40, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'suto@utooemail.com', '2019-01-28 23:00:00'),
(41, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'suto@utooemail.com', '2019-01-28 23:00:00'),
(42, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'suto@utooemail.com', '2019-01-28 23:00:00'),
(43, '', 'asdf', 'asf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'hori@22office.com', '2019-01-28 23:00:00'),
(44, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'rivopirel@321-email.com', '2019-01-28 23:00:00'),
(51, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'jexoconi@onecitymail.com', '2019-01-28 23:00:00'),
(52, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@daf.dedasfdasfdsfdasfdasf', '2019-01-30 23:00:00'),
(53, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@asdfwt3w4k35o3453k4p534k5??34534??5o', '2019-01-30 23:00:00'),
(54, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdfasdfwef@dsgdsds', '2019-01-30 23:00:00'),
(55, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdfasdff@dsdasggdsiugfdliuhgfdliuhgfdliuhjn', '2019-01-30 23:00:00'),
(56, '', 'asdf', 'asdf', '401b09eab3c013d4ca54922bb802bec8fd5318192b0a75f201d8b3727429080fb337591abd3e44453b954555b7a0812e1081c39b740293f765eae731f5a65ed1', 'asdf@asdfasdfasdfasdfasdfaasdf', '2019-02-03 23:00:00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `recovery`
--

CREATE TABLE `recovery` (
  `ID` int(10) NOT NULL,
  `RECOVERY_KEY` char(128) NOT NULL,
  `EXPIRY` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `KUNDE_ID` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ueberweisung`
--

CREATE TABLE `ueberweisung` (
  `UE_ID` int(20) NOT NULL,
  `V_IBAN` varchar(25) NOT NULL,
  `A_IBAN` varchar(25) NOT NULL,
  `BETRAG` decimal(10,2) NOT NULL,
  `TIME` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `ueberweisung`
--

INSERT INTO `ueberweisung` (`UE_ID`, `V_IBAN`, `A_IBAN`, `BETRAG`, `TIME`) VALUES
(12, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(13, '234567', '234877', '100.00', '2019-02-01 12:44:22'),
(14, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(15, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(16, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(17, '123456', '123433', '10.00', '2019-02-01 12:44:22'),
(18, '123433', '123456', '400.00', '2019-02-01 12:44:22'),
(19, '345678', '123433', '10.00', '2019-02-01 12:44:22'),
(20, '123456', '123433', '10.00', '2019-02-01 12:44:22'),
(21, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(22, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(23, '123433', '123456', '10.00', '2019-02-01 12:44:22'),
(24, '234567', '345678', '0.00', '2019-02-01 12:44:22'),
(25, '123456', '345678', '0.00', '2019-02-01 12:44:22'),
(26, '345678', '234877', '0.00', '2019-02-01 12:44:22'),
(27, '234877', '234567', '0.00', '2019-02-01 12:44:22'),
(28, '345678', '234567', '0.00', '2019-02-01 12:44:22'),
(29, '234567', '234877', '0.00', '2019-02-01 12:44:22'),
(30, '234877', '234567', '0.00', '2019-02-01 12:44:22'),
(31, '456789', '345678', '0.00', '2019-02-01 12:44:22'),
(32, '345678', '123456', '0.00', '2019-02-01 12:44:22'),
(33, '456789', '234567', '0.00', '2019-02-01 12:44:22'),
(34, '456789', '123456', '0.00', '2019-02-01 12:44:22'),
(35, '456789', '345678', '0.00', '2019-02-01 12:44:22'),
(36, '234567', '456789', '0.00', '2019-02-01 12:44:22'),
(37, '123456', '345678', '0.00', '2019-02-01 12:44:22'),
(38, '234567', '234877', '0.00', '2019-02-01 12:44:22'),
(39, '234877', '345678', '0.00', '2019-02-01 12:44:22'),
(40, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-01 12:51:27'),
(41, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-01 12:51:41'),
(42, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-01 13:12:41'),
(43, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-02 11:10:35'),
(44, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-02 13:02:27'),
(45, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-03 14:50:39'),
(46, 'DK5750510001322617', 'DE12500105170648489890', '10.00', '2019-02-03 14:51:50');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accountactivation`
--
ALTER TABLE `accountactivation`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `accountactivation_kunde_id__zu__kunde_id` (`KUNDE_ID`);

--
-- Indizes für die Tabelle `appsession`
--
ALTER TABLE `appsession`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `appsession_UNIQUE_KUNDE_ID` (`KUNDE_ID`);

--
-- Indizes für die Tabelle `konto`
--
ALTER TABLE `konto`
  ADD PRIMARY KEY (`IBAN`),
  ADD UNIQUE KEY `IBAN` (`IBAN`),
  ADD KEY `Kundenbeziehung` (`KUNDE_ID`);

--
-- Indizes für die Tabelle `kunde`
--
ALTER TABLE `kunde`
  ADD PRIMARY KEY (`ID`);

--
-- Indizes für die Tabelle `recovery`
--
ALTER TABLE `recovery`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `ID` (`ID`),
  ADD KEY `Zuordnung_kunde.ID_recovery.kunde_ID` (`KUNDE_ID`);

--
-- Indizes für die Tabelle `ueberweisung`
--
ALTER TABLE `ueberweisung`
  ADD PRIMARY KEY (`UE_ID`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `accountactivation`
--
ALTER TABLE `accountactivation`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=205;

--
-- AUTO_INCREMENT für Tabelle `appsession`
--
ALTER TABLE `appsession`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT für Tabelle `kunde`
--
ALTER TABLE `kunde`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT für Tabelle `recovery`
--
ALTER TABLE `recovery`
  MODIFY `ID` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT für Tabelle `ueberweisung`
--
ALTER TABLE `ueberweisung`
  MODIFY `UE_ID` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `accountactivation`
--
ALTER TABLE `accountactivation`
  ADD CONSTRAINT `accountactivation_kunde_id__zu__kunde_id` FOREIGN KEY (`KUNDE_ID`) REFERENCES `kunde` (`ID`);

--
-- Constraints der Tabelle `appsession`
--
ALTER TABLE `appsession`
  ADD CONSTRAINT `appsession_KUNDE_ID__zu__kunde_ID` FOREIGN KEY (`KUNDE_ID`) REFERENCES `kunde` (`ID`);

--
-- Constraints der Tabelle `konto`
--
ALTER TABLE `konto`
  ADD CONSTRAINT `Kundenbeziehung` FOREIGN KEY (`KUNDE_ID`) REFERENCES `kunde` (`ID`);

--
-- Constraints der Tabelle `recovery`
--
ALTER TABLE `recovery`
  ADD CONSTRAINT `Zuordnung_kunde.ID_recovery.kunde_ID` FOREIGN KEY (`KUNDE_ID`) REFERENCES `kunde` (`ID`);

DELIMITER $$
--
-- Ereignisse
--
CREATE DEFINER=`root`@`localhost` EVENT `DELETE_EXPIRED_RECOVERY` ON SCHEDULE EVERY 1 DAY STARTS '2019-01-27 00:00:00' ON COMPLETION NOT PRESERVE ENABLE COMMENT 'Dieser Task löscht täglich abgelaufene Password-Recoverys' DO DELETE FROM `recovery` WHERE `EXPIRY`<=CURRENT_TIMESTAMP()$$

CREATE DEFINER=`root`@`localhost` EVENT `DELETE EXPIRED ACTIVATION KEYS` ON SCHEDULE EVERY 1 DAY STARTS '2019-01-28 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM `accountactivation` WHERE `EXPIRY`<=CURRENT_DATE()$$

CREATE DEFINER=`root`@`localhost` EVENT `(DISABLED) DELETE EXPIRED USERS` ON SCHEDULE EVERY 1 DAY STARTS '2019-01-29 00:00:00' ON COMPLETION NOT PRESERVE DISABLE COMMENT 'Aus Testgründen noch disabled' DO DELETE FROM `kunde` WHERE `kunde`.`EXPIRY` <= CURRENT_DATE() AND `kunde`.`EXPIRY` <> '0000-00-00'$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
