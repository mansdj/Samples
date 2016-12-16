-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.5.32 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             8.1.0.4545
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for achieve3000
CREATE DATABASE IF NOT EXISTS `achieve3000` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `achieve3000`;


-- Dumping structure for table achieve3000.colors
CREATE TABLE IF NOT EXISTS `colors` (
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`color`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table achieve3000.colors: ~7 rows (approximately)
/*!40000 ALTER TABLE `colors` DISABLE KEYS */;
INSERT INTO `colors` (`color`) VALUES
	('Blue'),
	('Green'),
	('Indigo'),
	('Orange'),
	('Red'),
	('Violet'),
	('Yellow');
/*!40000 ALTER TABLE `colors` ENABLE KEYS */;


-- Dumping structure for table achieve3000.votes
CREATE TABLE IF NOT EXISTS `votes` (
  `city` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `votecount` int(11) NOT NULL DEFAULT '0',
  KEY `Index` (`city`,`color`),
  KEY `FK_votes_colors` (`color`),
  CONSTRAINT `FK_votes_colors` FOREIGN KEY (`color`) REFERENCES `colors` (`color`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table achieve3000.votes: ~7 rows (approximately)
/*!40000 ALTER TABLE `votes` DISABLE KEYS */;
INSERT INTO `votes` (`city`, `color`, `votecount`) VALUES
	('Anchorage', 'Blue', 10000),
	('Anchorage', 'Yellow', 15000),
	('Brooklyn', 'Red', 100000),
	('Brooklyn', 'Blue', 250000),
	('Detroit', 'Red', 160000),
	('Selma', 'Yellow', 15000),
	('Selma', 'Violet', 5000);
/*!40000 ALTER TABLE `votes` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
