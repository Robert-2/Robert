<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST);


if ( $action == 'select') {
	$pack = new Pack ('id', $id);
	$retour = $pack->getPackInfos();
	$retour = json_encode($retour);
	echo $retour ;
}


if ( $action == 'addPack') {
	if ($label == '' || $ref == '') { echo 'Pas assez de données... '; return; }
	unset($_POST['action']);
	$tmpPack = new Pack ();
	$tmpPack->setVals ($_POST);
	try { if ( $tmpPack->save() ) echo "Pack $ref Ajouté !"; }
	catch (Exception $e) { echo $e->getMessage(); }
	unset ($tmpPack) ;
}


if ( $action == 'modif') {
	unset($_POST['action']);
	$modPack = new Pack ('id', $_POST['id']);
	$modPack->setVals ($_POST);
	echo $modPack->updatePack();
}


if ( $action == 'addDetail') {
	extract($_POST);
	$modPack = new Pack ('id', $id);
	echo $modPack->addMatos ($ref, $qte);
}


if ( $action == 'modDetail') {
	extract($_POST);
	$modPack = new Pack ('id', $id);
	echo $modPack->modMatos ($ref, $qte);
}


if ( $action == 'delDetail') {
	extract($_POST);
	$modPack = new Pack ('id', $id);
	echo $modPack->delMatos ($ref);
}


if ( $action == 'delete') {
	try {
		$delPack = new Pack ('id', $id);
		if ($delPack->deletePack() > 0)
			echo "Pack supprimé !";
		else echo "Impossible de supprimer le pack...";
	}
	catch (Exception $e){
		echo "Impossible de supprimer le pack..." . $e->getMessage(); 
	}
}

?>
