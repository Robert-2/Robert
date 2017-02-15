<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST);

if ( $action == 'select') {
	$tekos = new Tekos ($id);
	$sel = $tekos->getTekosInfos();
	$retour = json_encode($sel);
	echo $retour ;
}


if ( $action == 'add') {
	unset($_POST['action']);
	$addTekos = new Tekos ();
	foreach ($_POST as $key=>$val) {
		$addTekos->setTekosInfo ($key, $val);
	}
	if ($addTekos->save())
		echo 'Technicien Ajouté !';
	else echo 'Impossible d\'ajouter le technicien...';
}


if ( $action == 'modif') {
	unset($_POST['action']);
	$modTekos = new Tekos ();
	$modTekos->loadFromBD('id', $id);
	foreach ($_POST as $key=>$val) {
		$modTekos->setTekosInfo ($key, $val);
	}
	if ($modTekos->save())
		echo 'Technicien Modifié !';
	else echo 'Impossible de modifier le technicien...';
}


if ( $action == 'delete') {
	try {
		$delTekos = new Tekos ();
		$delTekos->loadFromBD('id', $id);
		if($delTekos->deleteTekos() > 0)
			echo "Technicien supprimé !";
		else echo "Impossible de supprimer le technicien...";
	}
	catch (Exception $e){
		echo $e->getMessage(); 
	}
}

if ( $action == 'delTekFile'){
	$result = array() ;
	$file = urldecode($file);
	$tekDir = strtolower($data) ;
	$dir = '../' . FOLDER_TEKOS_DATAS . $tekDir . '/' ;
	$filename = $dir . $file ;

	if ( !file_exists ( $filename ) ){ $result['Error'] = 'Fichier inexistant'; }

	if ( ! @unlink ( $filename ) )    { $result['Error'] = 'Erreur lors de la suppression de '.$file; }
	else { $result[] = $file ; }

	echo json_encode($result);
	

}


if ( $action == 'fileList'){
	$dirName = strtolower($user) ;
	$dir =  '../' . FOLDER_TEKOS_DATAS . "$dirName"  ;
	$result = array() ;

	// pas de .. ou / ds le nom de tekos 
	if ( strpos($dirName,'..') !== false || strpos($dirName,'/') !== false ) $result['Error'] = 'Action non autorisée' ;

	if ( is_dir ( $dir ) ){
		if ( ! $handle = opendir( $dir ))
			$result['Error'] = 'Impossible d\'acceder au dossier de donnée' ;

		while (false !== ($entry = readdir($handle))) {
			if( $entry != '.' && $entry != '..' )
				$result[] = $entry ;
		}
	}
	else
		$result['Error'] = 'Dossier vide' ;
			

	echo json_encode($result);
		
}

?>
