-- MAJ STRUCTURE BASE DE DONNÉES --
-- DATE (AA-MM-JJ): 2017-02-14
-- FAITE PAR : Moutew
-- 6cc26d827a95499a99e293a701e77c2f

ALTER TABLE robert_benef_structure
MODIFY SIRET varchar(64) NULL,
MODIFY type varchar(64) NULL,
MODIFY interlocteurs varchar(256) NULL,
MODIFY email varchar(64) NULL,
MODIFY tel varchar(14) NULL,
MODIFY listePlans varchar(512) NULL,
MODIFY decla varchar(256) NULL,
MODIFY remarque text NULL;

ALTER TABLE robert_benef_interlocuteurs
MODIFY email VARCHAR(128) NULL,
MODIFY tel varchar(14) NULL,
MODIFY poste varchar(128) NULL,
MODIFY remarque text NULL,
MODIFY nomStruct varchar(64) NULL,
MODIFY typeRetour varchar(64) NULL;

ALTER TABLE `robert_matos_detail` MODIFY dateAchat VARCHAR(10) NULL;
UPDATE `robert_matos_detail` SET dateAchat = NULL WHERE `dateAchat` = '0000-00-00';
ALTER TABLE `robert_matos_detail` MODIFY dateAchat DATE NULL;

ALTER TABLE robert_matos_detail
MODIFY panne int(3) DEFAULT 0,
MODIFY externe tinyint(1) NULL,
MODIFY sousCateg int(4) NULL,
MODIFY dateAchat date NULL,
MODIFY ownerExt varchar(256) NULL,
MODIFY remarque text NULL;

ALTER TABLE robert_matos_packs
MODIFY tarifLoc float NULL,
MODIFY valRemp float NULL,
MODIFY remarque text NULL,
DROP KEY ref,
ADD CONSTRAINT UNIQUE KEY ref (ref) ;

ALTER TABLE robert_matos_sous_cat
DROP KEY label,
ADD CONSTRAINT UNIQUE KEY label (label);

ALTER TABLE `robert_tekos` MODIFY birthDay VARCHAR(10) NULL;
UPDATE `robert_tekos` SET birthDay = NULL WHERE `birthDay` = '0000-00-00';
ALTER TABLE `robert_tekos` MODIFY birthDay DATE NULL;
ALTER TABLE robert_tekos
MODIFY idUser smallint(3) NULL DEFAULT 0,
MODIFY email varchar(128) NULL,
MODIFY tel varchar(20) NULL,
MODIFY GUSO varchar(128) NULL,
MODIFY CS varchar(128) NULL,
MODIFY birthDay date NULL,
MODIFY birthPlace varchar(256) NULL,
MODIFY habilitations varchar(256) NULL,
MODIFY categorie varchar(128) NULL,
MODIFY SECU varchar(128) NULL,
MODIFY SIRET varchar(128) NULL,
MODIFY assedic varchar(64) NULL,
MODIFY intermittent tinyint(1) NULL,
MODIFY adresse varchar(64) NULL,
MODIFY cp varchar(64) NULL,
MODIFY ville varchar(64) NULL;

ALTER TABLE robert_users
MODIFY date_last_action int(10) NULL,
MODIFY date_last_connexion int(10) NULL,
MODIFY theme varchar(32) NULL,
MODIFY idTekos smallint(3) NULL;

ALTER TABLE robert_benef_structure DROP nbContrats;

ALTER TABLE robert_users DROP yeux;
ALTER TABLE robert_users DROP cheveux;
ALTER TABLE robert_users DROP age;
ALTER TABLE robert_users DROP taille;
