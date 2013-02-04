
-- BASE DE DONNÉES DU ROBERT : init --
-- DATE (AA-MM-JJ): 2012-12-27

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- ----------------- TABLE benef_interlocuteurs ------------------------

DROP TABLE IF EXISTS `robert_benef_interlocuteurs`;

CREATE TABLE `robert_benef_interlocuteurs` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `label` varchar(128) NOT NULL,
  `idStructure` int(4) NOT NULL,
  `nomPrenom` varchar(64) NOT NULL,
  `adresse` varchar(128) NOT NULL,
  `codePostal` varchar(10) NOT NULL,
  `ville` varchar(64) NOT NULL,
  `email` varchar(128) NOT NULL,
  `tel` varchar(14) NOT NULL,
  `poste` varchar(128) NOT NULL,
  `remarque` text NOT NULL,
  `nomStruct` varchar(64) NOT NULL,
  `typeRetour` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `label` (`label`),
  KEY `nomPrenom` (`nomPrenom`),
  KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE benef_structure ------------------------

DROP TABLE IF EXISTS `robert_benef_structure`;

CREATE TABLE `robert_benef_structure` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `label` varchar(128) NOT NULL,
  `SIRET` varchar(64) NOT NULL,
  `type` varchar(64) NOT NULL,
  `NomRS` varchar(128) NOT NULL,
  `interlocteurs` varchar(256) NOT NULL,
  `adresse` varchar(128) NOT NULL,
  `codePostal` varchar(8) NOT NULL,
  `ville` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `tel` varchar(14) NOT NULL,
  `nbContrats` int(3) NOT NULL,
  `listePlans` varchar(512) NOT NULL,
  `decla` varchar(256) NOT NULL,
  `remarque` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`),
  KEY `SIRET` (`SIRET`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- ----------------- TABLE devis ------------------------

DROP TABLE IF EXISTS `robert_devis`;

CREATE TABLE `robert_devis` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `id_plan` int(6) NOT NULL,
  `numDevis` int(3) NOT NULL,
  `fichier` varchar(128) NOT NULL,
  `matos` varchar(1024) NOT NULL,
  `tekos` varchar(256) NOT NULL,
  `total` float NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fichier` (`fichier`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE matos_detail ------------------------

DROP TABLE IF EXISTS `robert_matos_detail`;

CREATE TABLE `robert_matos_detail` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `ref` varchar(128) NOT NULL,
  `panne` int(3) NOT NULL,
  `externe` tinyint(1) NOT NULL,
  `categorie` varchar(128) NOT NULL,
  `sousCateg` int(4) NOT NULL,
  `Qtotale` int(4) NOT NULL,
  `tarifLoc` float NOT NULL,
  `valRemp` float NOT NULL,
  `dateAchat` date NOT NULL,
  `ownerExt` varchar(256) NOT NULL,
  `remarque` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ref` (`ref`),
  KEY `sousCateg` (`sousCateg`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE matos_packs ------------------------

DROP TABLE IF EXISTS `robert_matos_packs`;

CREATE TABLE `robert_matos_packs` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `ref` varchar(128) NOT NULL,
  `categorie` varchar(128) NOT NULL,
  `externe` tinyint(1) NOT NULL,
  `tarifLoc` float NOT NULL,
  `valRemp` float NOT NULL,
  `detail` varchar(256) NOT NULL,
  `remarque` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE matos_sous_cat ------------------------

DROP TABLE IF EXISTS `robert_matos_sous_cat`;

CREATE TABLE `robert_matos_sous_cat` (
  `id` int(4) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) NOT NULL,
  `ordre` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ordre` (`ordre`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

INSERT INTO `robert_matos_sous_cat` VALUES
('1','Amplificateurs','1'),
('2','Enceintes','2'),
('3','Consoles son','3'),
('4','Périphériques son','5'),
('5','Cables son','9'),
('6','Projecteurs','17'),
('7','Gradateurs','18'),
('8','Divers Lumière','20'),
('9','Microphones','6'),
('10','Divers elec','15'),
('12','Divers son','7'),
('13','Divers transport','23'),
('14','Pieds enceinte et lumiere','10'),
('15','Divers structure','13'),
('17','Structure','11'),
('18','Véhicules','22'),
('19','Divers','21'),
('20','Distribution Electrique','14'),
('21','Pieds Micro','8'),
('23','Console Lumiere','16'),
('26','Pendrillons','12'),
('27','Cables Lumière','19');


-- ----------------- TABLE notes ------------------------

DROP TABLE IF EXISTS `robert_notes`;

CREATE TABLE `robert_notes` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `date` int(10) NOT NULL,
  `texte` text NOT NULL,
  `createur` varchar(128) NOT NULL,
  `important` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;




-- ----------------- TABLE plans ------------------------

DROP TABLE IF EXISTS `robert_plans`;

CREATE TABLE `robert_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idGroup` int(11) NOT NULL,
  `titre` varchar(64) NOT NULL,
  `lieu` varchar(128) NOT NULL,
  `date_start` tinytext NOT NULL,
  `date_end` tinytext NOT NULL,
  `createur` varchar(256) NOT NULL,
  `beneficiaire` varchar(64) NOT NULL,
  `techniciens` varchar(64) NOT NULL,
  `materiel` text NOT NULL,
  `confirm` varchar(15) NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`id`),
  KEY `titre` (`titre`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE plans_details ------------------------

DROP TABLE IF EXISTS `robert_plans_details`;

CREATE TABLE `robert_plans_details` (
  `id_plandetails` int(11) NOT NULL AUTO_INCREMENT,
  `id_plan` int(11) NOT NULL,
  `jour` varchar(64) NOT NULL,
  `techniciens` varchar(100) NOT NULL,
  `materiel` text NOT NULL,
  `details_remarque` mediumtext NOT NULL,
  PRIMARY KEY (`id_plandetails`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE tekos ------------------------

DROP TABLE IF EXISTS `robert_tekos`;

CREATE TABLE `robert_tekos` (
  `id` int(3) NOT NULL AUTO_INCREMENT,
  `idUser` smallint(3) NOT NULL,
  `surnom` varchar(128) NOT NULL,
  `nom` varchar(128) NOT NULL,
  `prenom` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `tel` varchar(20) NOT NULL,
  `GUSO` varchar(128) NOT NULL,
  `CS` varchar(128) NOT NULL,
  `birthDay` date NOT NULL,
  `birthPlace` varchar(256) NOT NULL,
  `habilitations` varchar(256) NOT NULL,
  `categorie` varchar(128) NOT NULL,
  `SECU` varchar(128) NOT NULL,
  `SIRET` varchar(128) NOT NULL,
  `assedic` varchar(64) NOT NULL,
  `intermittent` tinyint(1) NOT NULL,
  `adresse` varchar(64) NOT NULL,
  `cp` varchar(64) NOT NULL,
  `ville` varchar(64) NOT NULL,
  `diplomes_folder` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surnom` (`surnom`),
  KEY `GUSO` (`GUSO`),
  KEY `CS` (`CS`),
  KEY `SECU` (`SECU`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



-- ----------------- TABLE users ------------------------

DROP TABLE IF EXISTS `robert_users`;

CREATE TABLE `robert_users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `nom` char(30) NOT NULL,
  `prenom` char(30) NOT NULL,
  `level` int(1) NOT NULL DEFAULT '1',
  `date_inscription` int(10) NOT NULL,
  `date_last_action` int(10) NOT NULL,
  `date_last_connexion` int(10) NOT NULL,
  `theme` varchar(32) NOT NULL,
  `yeux` varchar(64) NOT NULL,
  `cheveux` varchar(64) NOT NULL,
  `age` int(2) NOT NULL,
  `taille` float NOT NULL,
  `idTekos` smallint(3) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `robert_users` VALUES
('1','root@robertmanager.org','8351aaf8480d8135bc77af590c93c1e2','DEBUGGER','Root','9','1325615980','1356632988','1356620371','human','blancs','rouges','31','1.73','0');


