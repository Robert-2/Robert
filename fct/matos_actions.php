<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST);


if ( $action == 'select') {
	$matos = new Matos ('id', $id);
	$retour = $matos->getMatosInfos();
	$retour = json_encode($retour);
	echo $retour ;
}

if ( $action == 'addMatos') {
	if ($label == '' || $ref == '' || $Qtotale == '' || $tarifLoc == '' || $valRemp == '') { echo 'Pas assez de données... '; return; }
	unset($_POST['action']);
	$tmpMatos = new Matos ();
	$tmpMatos->setVals ($_POST);
	
	try { if ( $tmpMatos->save() ) echo "Matériel $ref Ajouté !"; }
	catch (Exception $e) { echo $e->getMessage(); }
	
	unset ($tmpMatos) ;
}

if ( $action == 'addMatosJson') {
	if ($label == '' || $ref == '' || $Qtotale == '' || $tarifLoc == '' || $valRemp == '') { $result['success'] = 'Pas assez de données... '; return; }
	unset($_POST['action']);
	$tmpMatos = new Matos ();
	$tmpMatos->setVals ($_POST);
	
	try { if ( $tmpMatos->save() ) $result['success'] = 'SUCCESS'; }
	catch (Exception $e) { $result['success'] = $e->getMessage(); }
	unset ($tmpMatos) ;

	$tmpMatos = new Matos ();
	$tmpMatos->loadFromBD( 'ref' , $ref ); 

	foreach ($tmpMatos as $k => $v ){
		$result['matos'][$k] = $v ;
	}
	echo json_encode($result);
}

if ( $action == 'modif') {
	unset($_POST['action']); unset($_POST['id']);
	$modMatos = new Matos ('id', $id);
	$modMatos->setVals ($_POST);
	if ($modMatos->save())
		echo 'Matériel sauvegardé !';
	else echo 'Impossible de sauvegarder.';
}

if ( $action == 'delete') {
	try {
		$delMatos = new Matos ('id', $id);
		if ($delMatos->deleteMatos() > 0) {
			$retour['error'] = 'OK';
			$retour['type'] = 'reloadPage';
		}
		else $retour['error'] = "Impossible de supprimer le matériel...";
	}
	catch (Exception $e){
		$retour['error'] = "Impossible de supprimer le matériel... <br />" . $e->getMessage() ; 
	}
	echo json_encode($retour);
}


// modification de l'ordre des sous catégories
if ( @$action == 'newSsCatOrder' ) {
	if (!is_array($ssCat)) die('La liste des sous catégories est manquante, ou ce n\'est pas un tableau...');
	$scmu = new Infos(TABLE_MATOS_CATEG);
	try {
		foreach($ssCat as $newOrder => $idSsCat) {
			$newOrder ++;
			$scmu->loadInfos('id', $idSsCat);
			$scmu->addInfo('ordre', $newOrder);
			$scmu->save();
		}
		$retour['error'] = 'OK';
		$retour['type'] = 'reloadPage';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de mettre à jour la liste des sous catégories... Message d'erreur :\n\n". $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}


// ajout d'une sous catégorie
if ( @$action == 'addSsCat') {
	try {
		$scm = new Infos(TABLE_MATOS_CATEG);
		$scm->addInfo('label', $label);
		$scm->addInfo('ordre', $ordre);
		$scm->save();
		$retour['error'] = 'OK';
		$retour['type'] = 'reloadModal';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de sauvegarder la sous catégorie... Message d'erreur :\n\n". $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}


// modification d'un nom de sous catégorie de matériel
if ( @$action == "modifSsCat") {
	try {
		$scm = new Infos(TABLE_MATOS_CATEG);
		$scm->loadInfos('id', $id);
		$scm->addInfo('label', $newLabel);
		$scm->save();
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de sauvegarder la sous catégorie... Message d'erreur :\n\n". $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}

if ( @$action == 'supprSsCat') {
	try {
		$scm = new Infos(TABLE_MATOS_CATEG);
		$scm->delete('id', $id);
		$retour['error'] = 'OK';
		$retour['type'] = 'reload';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de supprimer la sous catégorie... Message d'erreur :\n\n". $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}


?>
