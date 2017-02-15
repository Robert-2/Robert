<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST);

if ( !isset($_SESSION["user"])) { die('Pas de session active...'); }

global $tmpUser ;

function createTmpUser ($idUser){
	global $tmpUser ;
	try {
		$tmpUser = new Users () ;
		$tmpUser->loadFromBD ( Users::USERS_ID , $idUser ) ;
	}
	catch (Exception $e){
		echo "Erreur new User() : " . $e->getMessage();  ;
		die() ; 
	}
}

function saveTmpUser (){
	global $tmpUser ;
	try {
		if ( $tmpUser->save() )
			echo "Sauvegarde de l'utilisateur OK !";
	}
	catch (Exception $e){
		echo "Erreur User::save() : " . $e->getMessage(); 
	}
	unset ($tmpUser) ;	
}


// Sélectionne un utilisateur (retour en JSON)
if ($action == "select" || $action == 'load'){
	global $tmpUser ;
	createTmpUser($id);
	$ar = array();
	foreach ( $tmpUser as $k => $v )
		$ar[$k] = $v ;
	echo json_encode($ar);
	unset ($selUser) ;
}

// modifier un utilisateur (pour Admin)
if ( $action == 'modif') {
	unset($_POST['action']);
	if ( !isset($id) )
		die("Il manque l'id de l'utilisateur à modifier...");
	if ( $_SESSION["user"]->isAdmin() !== true )
		die("Vous n'êtes pas habilité à modifier cet utilisateur ! $id");
	if ( ($_SESSION["user"]->getUserInfos('id') == $id) ) {
		$retourModif = json_decode(modifOwnUser(), true);
		if ( $retourModif['error'] == 'OK')
			 die( "Sauvegarde de l'utilisateur OK !" ) ;
		else die( nl2br($retourModif['error']) );
	}
	createTmpUser($id) ;
	foreach ( $_POST as $key => $val ) {
		if ( $key == 'id' || $key == 'password') continue ; 
		$tmpUser->setUserInfos ( $key, $val ) ;
	}
	if (isset($_POST['password']) && $_POST['password'] != '') {
		if ($tmpUser->setPassword($_POST['password']) != true)
			echo "Le mot de passe est trop court !";
	}
	saveTmpUser();
}

// modifier un utilisateur (pour lui-même)
if ( $action == 'modifOwnUser') {
	echo modifOwnUser();
}
function modifOwnUser () {
	global $tmpUser; global $bdd;
	unset($_POST['action']);
	$reconnect = false;
	if ( !isset($_POST['id']) ) {
		$retour['error'] = "Il manque l'id de l'utilisateur à modifier...";
	}
	if ( ($_SESSION["user"]->getUserInfos('id') != $_POST['id']) )
		$retour['error'] = "Vous devez être l'utilisateur concerné ! (No " . $_POST['id'] . ")";
	
	createTmpUser($_POST['id']) ;
	
	foreach ( $_POST as $key => $val ) {
		if ($key == 'id' || $key == 'password') continue ; 
		$tmpUser->setUserInfos ( $key, $val ) ;
	}
	if (isset($_POST['password']) && $_POST['password'] != '') {				// et/ou si le password est redéfini
		$reconnect = true;
		if ($tmpUser->setPassword($_POST['password']) != true)
			$retour['error'] = "Le mot de passe est trop court !";
	}
	if ($_POST['email'] != $_SESSION['user']->getUserInfos('email')) {					// Si le mail est redéfini
		$reconnect = true;
	}
	if (!isset($retour['error'])) {
		try {
			if ( $tmpUser->save() ) {
				if ($reconnect == true) {										// ALORS on reconnecte le user pour remettre les bons mail / MDP dans les cookies et la session
					$Auth = new Connecting($bdd);
					if (!$Auth->connect($_POST['email'], @$_POST['password'])) $errAuth = true;
					else $errAuth = false;
					if ($errAuth == true) {
						$retour['error'] = "Impossible de vous reconnecter automatiquement !\nIl doit manquer le mot de passe...\n\nMerci de vous reconnecter manuellement.";
						$retour['type'] = "reloadPage";
					}
					else {
						$_SESSION['user'] = $tmpUser;
						$retour['error'] = "OK";
						$retour['type'] = "reloadPage";
					}
				}
				else {
					$_SESSION['user'] = $tmpUser;
					$retour['error'] = "OK";
					$retour['type'] = "reloadPage";
				}
			}
		}
		catch (Exception $e){
			$retour['error'] =  "Impossible de sauvegarder l'utilisateur : " . $e->getMessage(); 
		}
		unset ($tmpUser);
	}
	return json_encode($retour);
}


// modif du thème direct
if ( $action == "modifTheme") {
	if ( !isset($_POST['id']) ) {
		$retour['error'] = "Il manque l'id de l'utilisateur à modifier...";
	}
	if ( ($_SESSION["user"]->getUserInfos('id') != $_POST['id']) )
		$retour['error'] = "Vous devez être l'utilisateur concerné ! (No " . $_POST['id'] . ")";
	else {
		try {
			createTmpUser($id) ;
			$tmpUser->setUserInfos ( 'theme', $theme ) ;
			saveTmpUser();
		}
		catch (Exception $e) { echo 'Impossible de modifier le thème...<br />'.$e->getMessage(); }
	}
}


// créer un utilisateur
if ( $action == "create" ){
	if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'êtes pas habilité à ajouter des utilisateurs !"); } 
	else {
		$tmpUser = new Users () ;
		if ( isset ($cMail) )  $tmpUser->setEmail	 ( $cMail );
		if ( isset ($cName) )  $tmpUser->setName	 ( $cName );
		if ( isset ($cPass) )  $tmpUser->setPassword ( $cPass );
		if ( isset ($cPren) )  $tmpUser->setPrenom   ( $cPren );
		if ( isset ($cLevel) ) $tmpUser->setLevel	 ( $cLevel );
		if ( isset ($cTekos) ) $tmpUser->setTekos	 ( $cTekos );
		saveTmpUser();
	}
}


// crée un utilisateur à partir d'un technicien
if ( $action == "createFromTekos" ){
	if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'êtes pas habilité à ajouter des utilisateurs !"); }
	
	$tekosToUse = new Tekos($idTekos);
	$cMail = $tekosToUse->getTekosInfos('email');
	$cName = $tekosToUse->getTekosInfos('nom');
	$cPren = $tekosToUse->getTekosInfos('prenom');
	$cLevel = '5';
	unset($tekosToUse);
	
	$tmpUser = new Users () ;
	$tmpUser->setEmail	  ( $cMail );
	$tmpUser->setName	  ( $cName );
	$tmpUser->setPassword ( $passNewUser );
	$tmpUser->setPrenom   ( $cPren );
	$tmpUser->setLevel	  ( $cLevel );
	$tmpUser->setTekos	  ( $idTekos );
	saveTmpUser();
}

// Supprimer un utilisateur
if ( $action == "delete"){
	if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'êtes pas habilité à supprimer des utilisateurs !"); } 
	try {
		if ($_SESSION["user"]->deleteUser( $toDelete ) > 0)
			echo "Utilisateur supprimé !";
		else echo "Impossible de supprimer l'utilisateur...";
	}
	catch (Exception $e){
		echo $e->getMessage(); 
	}
}

// supprimer une info de la table "Users"
if ( $action == "supprInfoFromBDD" ) {
	if ( $_SESSION["user"]->isAdmin() !== true ) {
		$retour['error'] = "Vous n'êtes pas habilité à modifier la structure de la base de données !";
		die();
	}
	if ( $info == 'id' && $info == 'email' && $info == 'password' && $info == 'nom' && $info == 'prenom' && $info == 'level' && $info == 'theme' && $info == 'date_inscription' && $info == 'date_last_action' && $info == 'date_last_connexion' && $info == 'idTekos') {
		$retour['error'] = "On va éviter de supprimer un champ utile au système !";
		die();
	}
	if (Infos::removeChamp(TABLE_USERS, $info))
		$retour['error'] = "OK";
	else $retour['error'] = "Impossible de supprimer le champs $info de la base de donnes !";
	
	echo json_encode($retour);
}

?>
