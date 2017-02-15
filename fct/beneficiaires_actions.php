<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST) ;

if ($action == 'selectStruct') {
	$struct = new Structure ($id);
	$retour = $struct->getInfoStruct();
	$retour = json_encode($retour);
	echo $retour ;
}

if ($action == 'selectInterloc') {
	$interloc = new Interlocuteur ($id);
	$retour = $interloc->getInfoInterloc();
	$retour = json_encode($retour);
	echo $retour ;
}


if ( $action == 'addStruct') {
	unset($_POST['action']);
	if ($label == '' || $NomRS == '' || $adresse == '' || $codePostal == '' || $ville == '') { echo 'Pas assez de données... '; return; }
	$tmpStruct = new Structure ();
	$tmpStruct->setVals ($_POST);
	try { if ( $tmpStruct->save() ) echo "Structure \"$label\" ajoutée !"; }
	catch (Exception $e) { echo $e->getMessage(); }
	unset ($tmpStruct) ;
}

if ( $action == 'addInterloc') {
	unset($_POST['action']);
	$result = array ();

	if ($nomPrenom == '' || $adresse == '' || $codePostal == '' || $ville == '') { echo 'Pas assez de données... '; return; }
	$tmpInterloc = new Interlocuteur ();
	$tmpInterloc->setVals ($_POST);
	try {
		if ($tmpInterloc->save()) {
			$result["success"] = 'SUCCESS';
			$idNewInterloc = $tmpInterloc->getNewInterlocID();
		}
	}
	catch (Exception $e) { $result["success"] = $e->getMessage(); die(json_encode($result)); }

	if ($idStructure) {											// met à jour la structure correspondante avec l'id de l'interlocuteur
		$tmpStruct = new Structure($idStructure);
		$tmpStruct->updateInterloc($idNewInterloc);
		try { $tmpStruct->save(); }
		catch (Exception $e) { $result["success"] = $e->getMessage(); die(json_encode($result)); }
	}

	foreach ( $tmpInterloc as $k => $v ){
		$result['info'][$k] = $v ;
	}
	echo json_encode( $result );

	unset ($tmpInterloc) ;
	unset ($tmpStruct) ;
}



if ( $action == 'modifStruct') {
	unset($_POST['action']);
	unset($_POST['id']);
	$modStruct = new Structure ($id);
	$modStruct->setVals($_POST);
	try { if ( $modStruct->save() ) echo "Structure \"$label\" modifiée !"; }
	catch (Exception $e) { echo $e->getMessage(); }
}


if ( $action == 'modifInterloc') {
	$result = array();
	unset($_POST['action']);
	unset($_POST['id']);
	if (@$remarque == '') unset($_POST['remarque']) ;
	$modInterloc = new Interlocuteur ($id);
	$modInterloc->setVals($_POST);
	try { if ( $modInterloc->save() )
		$result['success'] = 'SUCCESS';
		$result['id'] = $id ;
    }
	catch (Exception $e) {
		$result['success'] = $e->getMessage(); }

	foreach ( $modInterloc as $k => $v ){
		$result['info'][$k] = $v ;
	}
	if (@$typeRetour == 'noJson') {
		if ($result['success'] == 'SUCCESS')
			echo 'Interlocuteur modifié !';
		else echo $result['success'];
	}
	else echo json_encode( $result );
}



if ( $action == 'supprStruct') {
	try {
		$delStruct = new Structure ($id);
		if($delStruct->deleteStruct() > 0) {
			$result['success'] = 'SUCCESS';
			$result['id'] = $id ;
		}
		else $result['success'] = "impossible de supprimer la structure !";
	}
	catch (Exception $e) { $result['success'] = $e->getMessage(); }

	echo json_encode( $result );
}



if ( $action == 'supprInterloc') {
	$result = array('id' => $id);
	try {
		$delInterloc = new Interlocuteur ($id);
		if($delInterloc->deleteInterloc() > 0) {
			$result['success'] = 'SUCCESS';
			$result['msg'] = "Interlocuteur supprimé";
		}
		else $result['success'] = "impossible de supprimer l'interlocuteur... ";
	}
	catch (Exception $e) { $result['success'] = $e->getMessage(); }

	echo json_encode( $result );
}

?>
