<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

if ( $_SESSION["user"]->isAdmin() !== true ) { die("Vous n'avez pas accès à cette partie du Robert."); }

extract($_POST) ;
$infosBoiteFile = $install_path . FOLDER_CONFIG . 'infos_boite.php';

if ($action == 'modifConsts') {
	unset($_POST['action']);
	$newConstFile = "<?php \n\n";
	foreach ($_POST as $key => $val) {
		$newConstFile .= "define('$key', '$val');\n";
	}
	$newConstFile .= "\n?>";
	
	if ( file_put_contents($infosBoiteFile, $newConstFile) !== false )
		echo 'Informations sauvegardées.';
	else echo 'Impossible de sauvegarder les infos...';
}










?>