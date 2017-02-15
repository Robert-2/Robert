<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract($_POST) ;

// Ajoute un post it à la BDD
if ( @$action == "addCalNote") {
	try {
		$date = $date / 1000;
		$postit = new Infos(TABLE_NOTES);
		$postit->addInfo('date', $date);
		$postit->addInfo('texte', strip_tags($texte, '<b><i><ul><li>'));
		$postit->addInfo('createur', $_SESSION['user']->getUserInfos('prenom'));
		$postit->addInfo('important', $important);
		$postit->save();
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible d'ajouter le post-it... \n\n ".$e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}


if ( @$action == 'delNote') {
	try {
		$postit = new Infos(TABLE_NOTES);
		$postit->delete('id', $idToDel);
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de supprimer le post-it... \n\n ".$e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}


if ( @$action == 'purgeNotes') {
	$ln = new Liste();
	$thisMorning = strtotime('midnight');
	$listeNotes = $ln->getListe(TABLE_NOTES, '*', 'date', 'ASC', 'date', '<', $thisMorning);

	try {
		if (is_array($listeNotes)) {
			foreach ($listeNotes as $note) {
				$postit = new Infos(TABLE_NOTES);
				$postit->delete('id', $note['id']);
				unset($postit);
			}
		}
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "Impossible de supprimer un post-it... \n\n ".$e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}

?>
