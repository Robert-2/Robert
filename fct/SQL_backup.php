<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'avez pas accès à cette partie du Robert."); }

$dumpPath = $install_path . FOLDER_CONFIG . 'dumpSQL/';
if (!is_dir($dumpPath))
	mkdir($dumpPath);
$codeAuthentik = md5('systemFlaskSQLbackup');
global $dumpPath;		// Chemin vers les backup SQL
global $codeAuthentik;	// Code "d'authenticité" pour la création / récupération



//////////////////////////////////////////////////////////////////////////////// FONCTION DE DUMP SQL DANS UN FICHIER

function backup_SQL ($toBakup='all') {											// args : 'all', ou array() des tables, ou string des tables sép. par des ','
	global $dumpPath; global $bdd; global $codeAuthentik;
	$now = date('Y-m-d');
	$fileSQL = array();

	if ($toBakup == 'all') {													// Si on dump TOUTES les tables
		$q = $bdd->prepare('SHOW TABLES');
		$q->execute();
		$tables = $q->fetchAll(PDO::FETCH_COLUMN);
		$fileSQL = 'TOUT_'.$now.'.sql';
	}
	else {																		// Si on dump QUE CERTAINES tables
		$tables  = is_array($toBakup) ? $toBakup : explode(',',$toBakup);
		if (count($tables) > 1) {
			$fileSQL = '';
			foreach($tables as $tableName) {
				$fileSQL .= $tableName.'_';
			}
			$fileSQL .= $now.'.sql';
		}
		else $fileSQL = $tables[0].'_'.$now.'.sql';								// Si on dump QU'UNE SEULE table
	}

	$output  = "\n-- BACKUP BASE DE DONNÉES -- \n";								// Création du texte du fichier SQL ( -> $output )
	$output .= "-- DATE (AA-MM-JJ): $now \n";
	$output .= "-- FAITE PAR : ".$_SESSION['user']->getUserInfos('prenom')."\n";
	$output .= "-- $codeAuthentik \n\n";
	$output .= 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";';
	$output .= "\n\n";
	foreach($tables as $table) {
		$c = $bdd->prepare('SHOW CREATE TABLE `'.$table.'`');			// Récup les types de champs de la table
		try { $c->execute(); }
		catch (Exception $e) { echo "erreur SQL : $e"; return false; }	// Si la table n'existe pas, erreur et on arrête tout !
		$resultCreate = $c->fetchAll(PDO::FETCH_ASSOC);

		$r = $bdd->prepare("SELECT * FROM $table");						// Récup les valeurs des champs de la table
		$r->execute();
		$resultTable = $r->fetchAll(PDO::FETCH_ASSOC);
		$nbRec = count($resultTable);									// Compte le nombre d'enregistrements de la table

		$output .= "-- ----------------- TABLE $table ------------------------\n\n";
		$output .= "DROP TABLE IF EXISTS `$table`;\n\n";				// $output : commande de suppression de la table si déjà existante
		$output .= $resultCreate[0]['Create Table'].";\n\n";			// $output : commande de re-création de la table

		if ($nbRec != 0) {
			$output .= "INSERT INTO `$table` VALUES ";						// $output : commande d'insertion des valeurs dans la table
			$countRec = 0;
			foreach ($resultTable as $row) {
				$countRec++ ;
				$output .= "\n(";
				$nbVal = count($row);										// Compte le nombre de colonnes de la table
				$countVal = 0;
				foreach ($row as $value) {
					$countVal++ ;


					// valeur : ajoute des slashes devant les caractères réservés
					// mieux vaut doubler les apostrophes, en mysql5.7 cela ajouter un slash n'est plus compatible.
					// commentaire à supprimer si tout le monde ok. (est qu'addslashes gère d'autres cas ?)
					/*$value = addslashes($value);*/
					$value = str_replace("'", "''", $value);


					$value = preg_replace("/\\r\\n/", "/\\\r\\\n/", $value);// valeur : évite que les retours à la ligne soient traduits
					if (isset($value)) $output .= "'$value'" ;				// $output : valeur à ajouter ('' si pas de valeur)
					else $output .= "''";
					if ($countVal == $nbVal) {
						$output .= ")";										// $output : ajout de la parenthèse fermée si à la fin des colonnes
						if ($countRec == $nbRec) $output .= ";";			// $output : ajout du point virgule si à la fin des enregistrements
						else $output .= ",";								// $output : ajout de la virgule si pas encore à la fin des enregistrements
					}
					else $output .= ",";									// $output : ajout de la virgule si pas encore à la fin des colonnes
				}
			}
		}
		$output .= "\n\n\n";
	}
																		// Sauvegarde de(s) fichier(s) SQL
	if (file_put_contents($dumpPath.$fileSQL, (string)$output) !== false)
		return $fileSQL;
	else return false;
}



//////////////////////////////////////////////////////////////////////////////// FONCTION DE RÉCUPÉRATION DE FICHIER SQL

function retore_SQL ($sqlFile) {
	global $dumpPath; global $bdd; global $codeAuthentik;
	if (file_exists($dumpPath.$sqlFile)) {								// Si le fichier existe
		$SQLcontent = file_get_contents($dumpPath.$sqlFile);
		if (preg_match("/-- $codeAuthentik/", $SQLcontent)) {			// Si le fichier contiens bien le hash MD5 créé lors d'une sauvegarde via le "Dump"
			$q = $bdd->prepare($SQLcontent);							// Execution de la requête du fichier
			try {$q->execute(); $retour = $sqlFile; }
			catch (Exception $e) { $retour = "erreur SQL : $e"; }
		}
		else {
			echo 'CODE de sécurité ERRONÉ ! ';
			$retour = false;
		}
	}
	else {
		echo 'FICHIER INTROUVABLE ! ';
		$retour = false ;
	}
	return $retour;
}

function upgrade_SQL($version){
	global $bdd; global $codeAuthentik; global $install_path;
	$sqlFile = $install_path . FOLDER_CONFIG . '/BDD_default_to_5.7.sql';
	
	if (! file_exists($sqlFile)) {
		echo 'FICHIER INTROUVABLE ! ';
		$retour = false ;
	}	
								// Si le fichier existe
	$SQLcontent = file_get_contents($sqlFile);
	if (!preg_match("/-- $codeAuthentik/", $SQLcontent)) {			// Si le fichier contiens bien le hash MD5 créé lors d'une sauvegarde via le "Dump"
		echo 'CODE de sécurité ERRONÉ ! ';
		$retour = false;
	}
	$requetes = explode(";", $SQLcontent);

	for ($i=0; $i < count($requetes)-1; $i++) { 
		$bdd->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); 
		$q = $bdd->prepare( $requetes[$i] .";" );	
		echo "Requete $i<br />" . $requetes[$i];						// Execution de la requête du fichier
		try {
			$q->execute(); 
			$retour = $SQLcontent; 
		}
		catch (Exception $e) { 
			$retour = "erreur SQL : $e"; 
		}
	}

	

	return $retour;	
}

//////////////////////////////////////////////////////////////////////////////// TRAITEMENT DES ACTIONS VIA _POST

if ( !isset($_SESSION["user"]) ) { echo "Pas de session"; return -1; }
else {
	if ($_POST) {
		if (isset($_POST['dump'])) {						// ACTION DUMP : enregistre la structure et le contenu de la base MySQL dans un fichier SQL
			if(($fileSaved = backup_SQL($_POST['dump'])) != false)
				echo 'SAUVEGARDE OK de <b>'.$fileSaved.'</b>.';
			else echo 'Impossible de sauvegarder la base de données...';
		}
		elseif (isset($_POST['restore'])) {					// ACTION RESTORE : récupère le contenu d'un fichier SQL et éxécute son contenu dans MySQL
			if (($fileLoaded = retore_SQL($_POST['fileBackup'])) != false)
				echo "</b>RÉCUPÉRATION de <b>$fileLoaded</b> OK !";
			else echo "Impossible de récupérer la sauvegarde...";
		}
/*		elseif (isset($_POST['upgradeSQL'])) {					// ACTION RESTORE : récupère le contenu d'un fichier SQL et éxécute son contenu dans MySQL
			if (($fileLoaded = retore_SQL('../BDD_default_to_5.7.sql')) != false)
				echo "</b>RÉCUPÉRATION de <b>$fileLoaded</b> OK !";
			else echo "Impossible de récupérer la sauvegarde...";
		}
*/
		elseif (isset($_POST['upgradeSQL'])) {			// ACTION upgradeSQL : compatible mode strict de MySQL 5.7 et redéfinit des configuration de colonnes de la base
			if ( $ret = upgrade_SQL($_POST['upgradeSQL']) != false)
				echo "</b>Mise à jour OK !<p>$ret";
			else echo "<p>Action annulée";			
		}
		else echo 'aucune action sélectionnée...' ;
	}
	else echo 'accès interdit...' ;
}

?>
