
-- BACKUP BASE DE DONNÉES --
-- DATE (AA-MM-JJ): 2013-02-04
-- FAITE PAR : Polo
-- 6cc26d827a95499a99e293a701e77c2f

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- ----------------- TABLE robert_notes ------------------------

DROP TABLE IF EXISTS `robert_notes`;

CREATE TABLE `robert_notes` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `date` int(10) NOT NULL,
  `texte` text NOT NULL,
  `createur` varchar(128) NOT NULL,
  `important` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;


INSERT INTO `robert_notes` VALUES
('1','1356620371','Une simple note, visible en bas du calendrier.','Root','0');


