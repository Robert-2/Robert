<?php
@session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );
require_once ('date_fr.php');
require_once ('matos_tri_sousCat.php');
$action = '';
extract($_POST) ;

/* ////////////////////////////////////////////////////////////////////////////////////////////// */
/* /////////////////////////////// TRAITEMENT DES ACTIONS /////////////////////////////////////// */
/* ////////////////////////////////////////////////////////////////////////////////////////////// */

// AFFICHAGE DES 2EME et 3EME ETAPES DE L'AJOUT DE PLAN ( SELECTEURS DE TEKOS, DE MATOS ET DE PACKS )
if ( @$action == 'afficheTekosMatos') {
	unset($_POST['action']);
	unset($_POST['start']); unset($_POST['end']);
	$js = substr($start, 6); $ms = substr($start, 4, 2); $as = substr($start, 0, 4);
	$je = substr($end, 6);	 $me = substr($end, 4, 2);	 $ae = substr($end, 0, 4);

	$nouveauPlan = createTmpPlan();
	foreach ($_POST as $key => $val) $nouveauPlan->addPlanInfo($key, $val);
	try {
		$nouveauPlan->setDateStart( $js, $ms, $as );
		$nouveauPlan->setDateEnd  ( $je, $me, $ae );
	}
	catch (Exception $e) { echo "ERREUR check dispo Tekos Matos : " . $e->getMessage(); }

	$cal = new Calendar();
	$cal->initPlans( $nouveauPlan->getPlanStartDate() , $nouveauPlan->getPlanEndDate(), @$excludePlan ) ;

	$planDetails['periodeStart'] = $js.'/'.$ms.'/'.$as;
	$planDetails['periodeEnd']   = $je.'/'.$me.'/'.$ae;

	$planDetails['nbPlansPeriode'] = $cal->getNBPlans();

	$listeTek = new Liste () ;
	$lt = $listeTek->getListe(TABLE_TEKOS, 'id, surnom, categorie', 'surnom') ;
	if ( $lt != false ) {
		foreach ( $lt as $k => $v ){
			$planDetails['tekos'][$k]['surnom']	 = $v['surnom'] ;
			$planDetails['tekos'][$k]['idtek']	 = $v['id'];
			$planDetails['tekos'][$k]['categTek'] = $v['categorie'];
			$idtek = $v['id'];
			$teksBusy = $cal->checkTekosBusy( $idtek );
			if (is_array($teksBusy)) {			// Recherche de disponibilité du tekos
				if (count($teksBusy) >= $nouveauPlan->getNBSousPlans()) {
					if ($teksBusy[0]['confirm'] == '0')
						$planDetails['tekos'][$k]['iconeTekos'] = "option";
					else
						$planDetails['tekos'][$k]['iconeTekos'] = "busy";
					$planDetails['tekos'][$k]['busyTekosPlan'] = "sur le plan <b>".$teksBusy[0]['titre']."</b>";
				}
				else {
					foreach ($teksBusy as $i => $infos) {
						if ($infos['confirm'] == '0') {
							$planDetails['tekos'][$k]['iconeTekos'] = "optionPartiel";
							$option = '(option)';
						}
						else {
							$planDetails['tekos'][$k]['iconeTekos'] = "partiel";
							$option = '';
						}
						$planDetails['tekos'][$k]['busyTekosDay'][$i] = 'le ' . $infos['jour'].", sur <i>\"".$infos['titre']."\"</i> $option";
					}
				}
			}
			else $planDetails['tekos'][$k]['iconeTekos'] = "dispo";
		}
	}

	$planDetails['matos'] = Array();
	$planDetails['packs'] = Array();

	$liste = new Liste () ;
	$lm = $liste->getListe(TABLE_MATOS, 'id, panne, Qtotale', 'ref', 'ASC') ;
	$lp = $liste->getListe(TABLE_PACKS, 'id, ref, detail', 'ref', 'ASC') ;
	if ($lm != false) {
		if ($lp != false) $cal->initPacks($lp);
		foreach ( $lm as $k => $v ) {
			$idMatos = $v['id'];
			$planDetails['matos'][$k]['panne']		= $v['panne'] ;
			$planDetails['matos'][$k]['idMatos']	= $idMatos;
			$planDetails['matos'][$k]['Qtotale']	= $v['Qtotale'];
			$planDetails['matos'][$k]['Qdispo']		= $v['Qtotale'];
			$qteDispoPourPack = $v['Qtotale'];
			$planDetails['matos'][$k]['Qattente']	= 0;
			$matosBusy = $cal->checkMatosBusy($idMatos, @$excludePlan);
			if (is_array($matosBusy)) {
				$planDetails['matos'][$k]['fullParc']	 = 'false';						// pseudo bool pour savoir vite fait s'il en manque
				$planDetails['matos'][$k]['Qdispo']		-= $matosBusy['QteConfirm'];	// -> qté en confirmé
				$planDetails['matos'][$k]['Qattente']	+= $matosBusy['QteAttente'];	// -> qté en option
				foreach ($matosBusy["planInfo"] as $index => $info) {
					$planDetails['matos'][$k]['infoPlans'][$index]['titre'] = $info['titrePlan'];		// titre    du plan ou c'est pris
					$planDetails['matos'][$k]['infoPlans'][$index]['owner'] = $info['ownerPlan'];		// créateur du plan ou c'est pris
					$planDetails['matos'][$k]['infoPlans'][$index]['qteC']  = $info['qteC'];			// nbre de matos confirmé pour ce plan
					$planDetails['matos'][$k]['infoPlans'][$index]['qteA']  = $info['qteA'];			// nbre de matos en attente pour ce plan
				}
				$qteDispoPourPack -= $matosBusy['QteConfirm'] + $matosBusy['QteAttente'] + $v['panne'];
			}
			if ($lp != false) $cal->createPack($v['id'], $qteDispoPourPack );
		}
		if ($lp != false) $planDetails['packs'] = $cal->countPacks();
	}

	$planDetails = json_encode($planDetails);
	echo $planDetails ;
}


// CHECK de la dispo des tekos à UNE date donnée
if ( @$action == 'getDispoTekos') {
	if (!isset($date)) die("Il manque la date pour vérifier les dispos des techniciens !");
	$dateS = (int)$date - 43200;	// -12 heures
	$dateE = (int)$date + 43200;	// +12 heures
	$calDay = new Calendar();
	$calDay->InitPlans($dateS, $dateE, @$exclude);
	$listeTek = new Liste () ;
	$lt = $listeTek->getListe(TABLE_TEKOS, 'id') ;
	if ( $lt != false ) {
		foreach ( $lt as $idtek ){
			$teksBusy = $calDay->checkTekosBusy( $idtek );
			$tekArray[$idtek] = "dispo";
			if ($teksBusy) $tekArray[$idtek] = "busy";
		}
	}
	$tekArray = json_encode($tekArray);
	echo $tekArray ;
}


// TRAITEMENT DE L'AJOUT DE PLAN
if ( @$action == 'saveSessionPlan') {
	if (!isset($_SESSION['plan_add'])) die("Pas de plan enregistré en session... Désolé !");
	$newPlan = unserialize($_SESSION['plan_add']);
	$newPlan->addPlanInfo( Plan::PLAN_cCREATEUR , $_SESSION['user']->getUserInfos('id') );
	if ($type == 'devis') $newPlan->addPlanInfo( Plan::PLAN_cCONFIRM , 0 );
	elseif ($type == 'reservation') $newPlan->addPlanInfo( Plan::PLAN_cCONFIRM , 1 );
	try {
		if ( $newPlan->save() ) {
			echo "Plan AJOUTÉ !";
			unset($_SESSION['plan_add']);
		}
		else echo "Impossible de sauvegarder le plan...";
	}
	catch (Exception $e) {
		echo "Sauvegarde du plan impossible : " . $e->getMessage();
	}
}


// CHARGEMENT D'UN PLAN ET SES SOUS PLANS
if ( @$action == 'loadPlan'){
	try {
		$p = new Plan () ;
		$p->load ( Plan::PLAN_cID , $ID ) ;
		jsonPlan($p);
	}
	catch ( Exception $e ) { echo "Impossible de charger le plan : " . $e->getMessage(); }
}


// TRAITEMENT DE LA MODIFICATION DES SOUS PLANS D'UN PLAN lors d'un AJOUT
if ( @$action == 'modPlan') {
	unset($_POST['action']);
	if (!isset($_SESSION['plan_add'])) die("Pas de plan enregistré en session... Désolé !");
	if (!isset($id)) die("Il me manque l'id du plan à modifier !");

	// on charge le plan modifié qui est en session
	$modPlan = unserialize($_SESSION['plan_add']);

	// on charge les infos du plan original
	$tmpPlan = createTmpPlan () ;
	$tmpPlan->load( Plan::PLAN_cID , $id ) ;

	$backupSsPlans = array();					// récupère dans un tableau les sous plans déjà définis en BDD
	while ( $tmpPlan->whileTestSousPlan() ){
		$ts = $tmpPlan->getSousPlanDate();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_ID]       = $tmpPlan->getSousPlanId();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_TECKOS]   = implode(" ", $tmpPlan->getSousPlanTekos());
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_MATOS]    = $tmpPlan->getSousPlanMatos();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_COMMENT]  = $tmpPlan->getSousPlanComment();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_PGROUPID] = $tmpPlan->getPlanGroupID();
	}

	$tmpPlan->purgeSousPlans();					// supprime les sous plans existants en BDD

	// on remplace les infos du plan chargé par les infos du plan modifié
	$modTitre = $modPlan->getPlanTitre();
	$tmpPlan->addPlanInfo ('titre', $modTitre);
	$modLieu = $modPlan->getPlanLieu();
	$tmpPlan->addPlanInfo ('lieu', $modLieu);
	$modBenef = $modPlan->getPlanBenef();
	$tmpPlan->addPlanInfo ('beneficiaire', $modBenef);
	$modTekos = $modPlan->getPlanTekosBrut();
	$tmpPlan->addPlanInfo ('techniciens', $modTekos);
	$modMatos = $modPlan->getPlanMatosBrut();
	$tmpPlan->addPlanInfo ('materiel', $modMatos);
	// on lui ajoute le créateur, et la confirm
	$tmpPlan->addPlanInfo( Plan::PLAN_cCREATEUR , $_SESSION['user']->getUserInfos('id') );
	if ($type == 'devis') $tmpPlan->addPlanInfo( Plan::PLAN_cCONFIRM , 0 );
	elseif ($type == 'reservation') $tmpPlan->addPlanInfo( Plan::PLAN_cCONFIRM , 1 );

	// on redéfini les dates avec celles du modPlan
	$js = date('d', $modPlan->getPlanStartDate());
	$ms = date('m', $modPlan->getPlanStartDate());
	$as = date('Y', $modPlan->getPlanStartDate());
	$je = date('d', $modPlan->getPlanEndDate());
	$me = date('m', $modPlan->getPlanEndDate());
	$ae = date('Y', $modPlan->getPlanEndDate());

	$tmpPlan->setDateStart( $js, $ms, $as );
	$tmpPlan->setDateEnd  ( $je, $me, $ae );

	// après être passé dans le setDateEnd, donc le createSubPlan, on remet les infos des sous plans
	foreach( $backupSsPlans as $k => $v ){
		if ( $tmpPlan->setSousplanOffsetByTimeStamp($k) ){
			$tmpPlan->setSousPlanId     ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_ID]);
			$tmpPlan->setSousPlanTekos  ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_TECKOS] ) ;
			$tmpPlan->setSousPlanMatos  ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_MATOS]) ;
			$tmpPlan->setSousPlanComment( $backupSsPlans[$k][Plan::PLAN_cDETAILS_COMMENT] ) ;
		}
	}
	// on sauvegarde
	try {
		if ( $tmpPlan->save() )
			echo "OK, plan modifié !";
	}
	catch ( Exception $e ){
		echo "Sauvegarde Impossible : " . $e->getMessage();
	}
}

if ( $action == 'getManqueMatos' ){

	echo Matos_getManque( $PlanId );

}


// Ajoute une dimension a la liste de materiel ['MANQUE']
function Matos_getManque( $idPlan, $listMatos ){

	$tmpPlan = createTmpPlan () ;
	$tmpPlan->load( Plan::PLAN_cID , $idPlan ) ;
	$tmpPlanConfirmTime = $tmpPlan->getPlanConfirm();

	$start = date( 'Ymd' , $tmpPlan->getPlanStartDate() );
	$end =   date( 'Ymd' , $tmpPlan->getPlanEndDate() );

	$js = substr($start, 6); $ms = substr($start, 4, 2); $as = substr($start, 0, 4);
	$je = substr($end, 6);	 $me = substr($end, 4, 2);	 $ae = substr($end, 0, 4);

	$startCal = mktime (0,0,0, $ms, $js, $as);
	$EndCal = mktime(0,0,0, $me, $je, $ae);

	$c = new Calendar();
	$c->InitPlans(  $startCal , $EndCal , $idPlan  );

	$liste=new Liste();
	$lm = $liste->getListe(TABLE_MATOS, 'id, ref, panne, Qtotale') ;
	$lm = $liste->simplifyList('id');

	$lmatos = $tmpPlan->getPlanMatos();

//	$retour = $listMatos;
	foreach( $lmatos as $id => $qte ){
		if (!isset($lm[$id])) continue;
		$dispo = $lm[$id]['Qtotale'] - $lm[$id]['panne'];
		$ar = $c->checkMatosBusy($id);
		if ( $ar == false ) {
			$listMatos[$id]['manque'] = 0; continue ;
		}
		foreach ( $ar["planInfo"] as $indPlan => $Infos) {
			$tmpStamp = $Infos['confirmDate'];
			// le plan a ete confirmé avant celui ci, donc le matos est pris et on soustrait
			if ( $tmpStamp <= $tmpPlanConfirmTime )
				$dispo -= $Infos['qteC'] ;
		}
		$dispo = $dispo - $qte;
		if ( $dispo <= 0 ) 	$listMatos[$id]['manque'] = $dispo ;
		if ( $dispo <= 0 && $dispo <= - ($qte) ) $listMatos[$id]['manque'] = $qte ;

	}
	return ($listMatos);
}



// MODIFICATION D'UN SOUS PLAN
// modif de la remarque
if ( @$action == 'addSessionSPrem') {
	if (!isset($_SESSION[$typeSess])) die("Pas de plan enregistré en session... Désolé !");
	$p = unserialize($_SESSION[$typeSess]);
	while($p->whileTestSousPlan()) {
		if ($p->getSousPlanDate() == $spTime)
			$p->setSousPlanComment($comment);
	}
	$_SESSION[$typeSess] = serialize($p);
}
// modif des tekos
if ( @$action == 'SousPlanModifTek'){
	if (!isset($_SESSION[$typeSess])) die("Pas de plan enregistré en session... Désolé !");
	$p = unserialize($_SESSION[$typeSess]);
	if ( ! $p->setSousplanOffsetByTimeStamp( $spDate ) ) die("Sous plan introuvable !");
	$p->setSousPlanTekos($tekList);
	$_SESSION[$typeSess] = serialize($p);
}


// MODIFICATION DU MATOS pour le plan en session
if ( @$action == 'sessModifMatos'){
	if (!isset($_SESSION[$typeSess])) die("Pas de plan enregistré en session... Désolé !");
	$p = unserialize($_SESSION[$typeSess]);
	$p->addPlanInfo(Plan::PLAN_cMATOS, $matList);
	$_SESSION[$typeSess] = serialize($p);
	// on sauvegarde
	try {
		$p->save();
		unset($_SESSION[$typeSess]);
	}
	catch ( Exception $e ){
		echo "Refresh : Sauvegarde Impossible : " . $e->getMessage();
	}
}

// INITIALISATION DE L'AJOUT DE PLAN (pour le mettre en $_SESSION)
if ( @$action == 'initSessionPlanAdd') {
	unset($_POST['action']);
	unset($_POST['start']); unset($_POST['end']);
	$js = substr($start, 6); $ms = substr($start, 4, 2); $as = substr($start, 0, 4);
	$je = substr($end, 6);	 $me = substr($end, 4, 2);	 $ae = substr($end, 0, 4);
	$theNewPlan = createTmpPlan();
	foreach ($_POST as $key => $val) $theNewPlan->addPlanInfo($key, $val);
	try {
		$theNewPlan->setDateStart( $js, $ms, $as );
		$theNewPlan->setDateEnd  ( $je, $me, $ae );
		$_SESSION['plan_add'] = serialize($theNewPlan);
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "ERREUR lors de l\'initialisation du plan_add en session...\nMessage d\'erreur :\n\n" . $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour ;
}


// Rafraîchi les données d'un plan qu'on est en train d'ajouter (aux étapes 2 et 3) (enregistré en $_SESSION)
if ( @$action == 'refreshSessionAddPlan') {
	unset($_POST['action']);
	if (!isset($_SESSION['plan_add'])) {
		$retour['error'] = "Pas de plan enregistré en session... Désolé !";
		$retour = json_encode($retour);
		echo $retour;
		die();
	}
	$addPlan = unserialize($_SESSION['plan_add']);
	try {
		if (isset($tekosArr)) {
			$tekosArrD = json_decode($tekosArr);
			$tekosList = implode(' ', $tekosArrD);
			$addPlan->addPlanInfo('techniciens', $tekosList);
			$_SESSION['plan_add'] = serialize($addPlan);
			$retour['error'] = 'OK';
		}
		elseif (isset($matosList)) {
			$addPlan->addPlanInfo('materiel', $matosList);
			$_SESSION['plan_add'] = serialize($addPlan);
			$retour['error'] = 'OK';
		}
		else $retour['error'] = 'Il manque la liste à actualiser en session !';
	}
	catch (Exception $e) {
		$retour['error'] = 'Une erreur est survenue lors de l\'actualisation du plan en session...\nMessage d\'erreur :\n\n'.$e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}



// Rafraîchi les données d'un plan qu'on est en train de modifier (quand clic sur bouton "actualiser") (enregistré en $_SESSION)
if ( @$action == 'refreshSessionModPlan') {
	unset($_POST['action']);
	if (!isset($_SESSION['plan_mod'])) die("Pas de plan enregistré en session... Désolé !");
	$modPlan = unserialize($_SESSION['plan_mod']);

	$backupSsPlans = array();					// récupère dans un tableau les sous plans déjà définis en session
	while ( $modPlan->whileTestSousPlan() ){
		$ts = $modPlan->getSousPlanDate();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_ID]       = $modPlan->getSousPlanId();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_TECKOS]   = implode(" ", $modPlan->getSousPlanTekos());
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_MATOS]    = $modPlan->getSousPlanMatos();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_COMMENT]  = $modPlan->getSousPlanComment();
		$backupSsPlans["$ts"][Plan::PLAN_cDETAILS_PGROUPID] = $modPlan->getPlanGroupID();
	}

	$modPlan->purgeSousPlans();					// supprime les sous plans existants en BDD

	if ( @$restore == 1 && isset($_SESSION['plan_mod_backup'])) {
		// on remplace les infos du plan en session par les anciennes var
		$modPlan->addPlanInfo ('titre', $_SESSION['plan_mod_backup'][0]);
		$modPlan->addPlanInfo ('beneficiaire', $_SESSION['plan_mod_backup'][1]);
		$modPlan->addPlanInfo ('lieu', $_SESSION['plan_mod_backup'][2]);
		$start = $_SESSION['plan_mod_backup'][3];
		$end   = $_SESSION['plan_mod_backup'][4];
		$js = substr($start, 6); $ms = substr($start, 4, 2); $as = substr($start, 0, 4);
		$je = substr($end, 6);	 $me = substr($end, 4, 2);	 $ae = substr($end, 0, 4);
		$modPlan->addPlanInfo ('materiel', json_encode($_SESSION['plan_mod_backup'][5]));
	}
	else {
		// on remplace les infos du plan en session par les infos POST
		$modPlan->addPlanInfo ('titre', $titre);
		$modPlan->addPlanInfo ('lieu', $lieu);
		$modPlan->addPlanInfo ('beneficiaire', $benef);
		$js = substr($start, 6); $ms = substr($start, 4, 2); $as = substr($start, 0, 4);
		$je = substr($end, 6);	 $me = substr($end, 4, 2);	 $ae = substr($end, 0, 4);
	}
	$modPlan->setDateStart( $js, $ms, $as );
	$modPlan->setDateEnd  ( $je, $me, $ae );	// il reconstruit les sous plans
	// on re-rempli les sous plans avec les infos de backup
	foreach( $backupSsPlans as $k => $v ){
		if ( $modPlan->setSousplanOffsetByTimeStamp($k) ){
			$modPlan->setSousPlanId     ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_ID]);
			$modPlan->setSousPlanTekos  ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_TECKOS] ) ;
			$modPlan->setSousPlanMatos  ( $backupSsPlans[$k][Plan::PLAN_cDETAILS_MATOS]) ;
			$modPlan->setSousPlanComment( $backupSsPlans[$k][Plan::PLAN_cDETAILS_COMMENT] ) ;
		}
	}
	// on le remet dans la session
	$_SESSION['plan_mod'] = serialize($modPlan);
	// on sauvegarde
	try {
		$modPlan->save();
		unset($_SESSION['plan_mod']);
		if ( @$restore == 1 ) {
			unset($_SESSION['plan_mod_backup']);
		}
	}
	catch ( Exception $e ){
		echo "Refresh : Sauvegarde Impossible : " . $e->getMessage();
	}
}


// SAUVEGARDE DU PLAN EN SESSION
if ( @$action == 'saveModPlan') {
	if (!isset($_SESSION['plan_mod'])) die("Pas de plan enregistré en session... Désolé !");
	$savePlan = unserialize($_SESSION['plan_mod']);
	try {
		$savePlan->save();
		unset($_SESSION['plan_mod']);
		unset($_SESSION['plan_mod_backup']);
		echo 'plan sauvegardé !';
	}
	catch ( Exception $e ){
		echo "Sauvegarde Impossible : " . $e->getMessage();
	}
}


// SUPPRESSION DU PLAN EN SESSION (ajout ou modif)
if ( @$action == 'unsetSessionPlan') {
	unset($_SESSION[$type]);
	$retour['error'] = 'OK';
	$retour['type']  = 'removeDiv';
	$retour['divId']  = $type;
	$retour = json_encode($retour);
	echo $retour ;
}


// CONFIRMATION D'UN PLAN
if ( @$action == 'confirmPlan') {
	$tmpPlan = createTmpPlan();
	$tmpPlan->load( Plan::PLAN_cID , $ID ) ;
	$tmpPlan->addPlanInfo( Plan::PLAN_cCONFIRM , 1 );
	saveTmpPlan ( $tmpPlan );
}


// SUPPRESSION D'UN PLAN
if ( @$action == 'delPlan') {
	try {
		if (isset($_SESSION['plan_mod'])) {		// Si le plan qu'on veut supprimer se trouve en session, on suppr aussi la var de session
			$modPlan = unserialize($_SESSION['plan_mod']);
			$idSessPlan = $modPlan->getPlanID();
			if ($ID == $idSessPlan)
				unset($_SESSION['plan_mod']);
		}
		$p = new Plan () ;
		$p->load( Plan::PLAN_cID , $ID ) ;
		$r = $p->delete() ;						// Supprime tout ce qui a un rapport avec le plan : plan, sous plans, devis...

		if ( $r > 0 ) {
			$retour['error'] = "OK";
			$retour['type']  = 'reloadPage';
		}
		else {
			$retour['error'] = "Évènement introuvable !" ;
			$retour['type']  = 'reloadPage';
		}
	}
	catch ( Exception $e ) {
		$retour['error'] = "Impossible de supprimer l'évènement : \n\n" . $e->getMessage();
		$retour['type']  = 'reloadPage';
	}
	echo json_encode($retour);
}


																	// DEVIS
// Création d'un DEVIS
if ( @$action == 'createDevis' ) {
	$tmpPDF = new SortiePDF($id);
	if (!test_benef($id)) {
		$retour['error'] = "ERREUR !\n\n Vous devez renseigner le bénéficiaire avant de créer un devis !";
		$retour['type']  = 'reloadPage';
		die(json_encode($retour));
	}
	try {
		$tmpPDF->createDevis($salaires, $remise, stripslashes(urldecode($contratTxt)));
		$retour['error'] = 'OK';
		$retour['message'] = stripslashes($contratTxt);
	}
	catch (Exception $e) {
		$retour['error'] = "ERREUR !\n\n". $e->getMessage();
		$retour['type']  = 'reloadPage';
	}
	$retour = json_encode($retour);
	echo $retour;
}

// Suppression d'un DEVIS
if ( @$action == 'supprDevis') {
	$tmpDevis = new Devis($idPlan);
	try {
		$tmpDevis->deleteDevis($file);
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "ERREUR !\n\n". $e->getMessage();
	}
	$retour = json_encode($retour);
	echo $retour;
}

// GETLISTE DES FICHIERS DE DEVIS POUR UN PLAN
if ( @$action == 'showDevisFiles') {
	$listeDevis = Devis::getDevisFiles($idPlan, true);
	$retour = array();
	if ($listeDevis == false)
		$retour[] = 'NO_FILE';
	else $retour = $listeDevis;
	$retour = json_encode($retour);
	echo $retour;
}

// SAUVEGARDE DU CONTRAT PAR DÉFAUT
if ( @$action == 'saveContrat') {
	if (file_put_contents('../'.FOLDER_CONFIG.'default_contrat.txt', stripslashes($contratTxt))) {
		$retour['error'] = 'OK'	;
		$retour['message'] = 'Contrat par défaut sauvegardé';
	}
	else $retour['error'] = 'Impossible de sauvegarder le contrat par défaut !'	;
	echo json_encode($retour);
}


																	// FACTURE
// Création de la FACTURE
if ( @$action == 'createFacture' ) {
	$tmpPDF = new SortiePDF($id);
	if (!test_benef($id)) {
		$retour['error'] = "ERREUR !\n\n Vous devez renseigner le bénéficiaire avant de créer une facture !";
		$retour['type']  = 'reloadPage';
		die(json_encode($retour));
	}
	try {
		$tmpPDF->createFacture($remise);
		$retour['error'] = 'OK';
	}
	catch (Exception $e) {
		$retour['error'] = "ERREUR !\n\n". $e->getMessage();
		$retour['type']  = 'reloadPage';
	}
	$retour = json_encode($retour);
	echo $retour;
}

// Suppression de la FACTURE
if ( @$action == 'supprFacture') {
	$factureFilePath = '../'.FOLDER_PLANS_DATAS.$idPlan.'/facture';
	rrmdir($factureFilePath);
	$retour['error'] = 'OK';
	$retour = json_encode($retour);
	echo $retour;
}

// RÉCUP DU NOM DE FICHIER DE LA FACTURE
if ( @$action == 'showFactureFile') {
	$factureFilePath = '../'.FOLDER_PLANS_DATAS.$idPlan.'/facture';
	$f = @scandir($factureFilePath);
	if ($f != false) {
		$factureFile = array();
		foreach ($f as $file) {
			if ($file != "." && $file != "..") {
				if (filetype($factureFilePath."/".$file) != "dir")
						$factureFile['file'] = $file;
			}
		}
		$retour[] = $factureFile;
	}
	else $retour[] = 'NO_FILE';

	$retour = json_encode($retour);
	echo $retour;
}


																	// FICHIERS
// Suppression d'un fichier divers du plan
if ( @$action == 'supprFichier' ) {
	$file = urldecode($file);
	$fichierPath = '../'.FOLDER_PLANS_DATAS.$idPlan.'/'.$file;
	if (@unlink($fichierPath) == true)
		$retour['error'] = 'OK';
	else $retour['error'] = "Impossible de supprimer le fichier $file !";
	$retour = json_encode($retour);
	echo $retour;
}

// Récup les fichiers divers du plan
if (@$action == 'showPlanFiles') {
	$filePath = '../'.FOLDER_PLANS_DATAS.$idPlan;
	$f = @scandir($filePath);
	if ($f != false) {
		$listeFile = array();
		foreach ($f as $file) {
			if ($file != "." && $file != "..") {
				if (filetype($filePath."/".$file) != "dir")
						$retour[]['file'] = $file;
			}
		}
		if (!isset($retour[0]['file'])) $retour[] = 'NO_FILE';
	}
	else $retour[] = 'NO_FILE';

	$retour = json_encode($retour);
	echo $retour;
}


/* ////////////////////////////////////////////////////////////////////////////////////////////// */
/* //////////////////////////////// FONCTIONS DES PLANS ///////////////////////////////////////// */
/* ////////////////////////////////////////////////////////////////////////////////////////////// */

// CRÉE UN OBJET PLAN VITE FAIT
function createTmpPlan (){
	try {
		$tmpPlan = new Plan () ;
	}
	catch (Exception $e){
		echo "Erreur Plan : " . $e->getMessage();  ;
		die() ;
	}
	return $tmpPlan ;
}


// SAUVEGARDE D'UN PLAN ET SOUS PLAN
function saveTmpPlan ( $tmpPlan ){
	try {
		if ( $tmpPlan->save() ) {
			$retour['error'] = "OK";
			$retour['idPlan']= $tmpPlan->getPlanID();
		}
	}
	catch (Exception $e){
		$retour['error'] = "Sauvegarde Impossible : " . $e->getMessage();
		$retour['type']  = 'reloadPage';
	}
	unset ($tmpPlan) ;
	echo json_encode($retour);
}


// RÉCUPÉRATION DES PLANS ET SOUS PLANS puis ENCODAGE JSON
function jsonPlan($p) {
	$l = new Liste();
	$list_Matos = $l->getListe(TABLE_MATOS, 'id, ref, tarifLoc, externe, categorie, sousCateg, ownerExt', 'categorie', 'ASC');

	if ( get_class($p) != "Plan" ) return -1 ;
	$retour['id']			= $p->getPlanID();
	$retour['titre']		= $p->getPlanTitre();
	$retour['dateDebut']	= datefr( date('j F Y', $p->getPlanStartDate()) );
	$retour['dateFin']		= datefr( date('j F Y', $p->getPlanEndDate()) );
	$retour['timeDebut']	= date('Y-m-d', $p->getPlanStartDate());
	$retour['timeFin']		= date('Y-m-d', $p->getPlanEndDate());
	$matos_plan				= $p->getPlanMatos();
	$retour['lieu']			= $p->getPlanLieu();
	$retour['createur']		= $p->getPlanCreateur();
	$retour['benef']		= $p->getPlanBenef();
	$retour['nbSousPlans']	= $p->getNBSousPlans();
	if ($p->getPlanConfirm() != 0) {
		$retour['resa']		= 'reservation';
		$retour['resaTxt']	= 'confirmé, devis accepté';
	}
	else {
		$retour['resa']		= 'devis';
		$retour['resaTxt']	= 'en attente de confirmation';
	}

	foreach($list_Matos as $matos) {
		if ( isset($matos_plan[$matos['id']]) ) {
			$matosPlanList[] = $matos;
		}
	}

	$list_sousCat = $l->getListe ( TABLE_MATOS_CATEG, '*', 'ordre', 'ASC' );
	$list_sousCat = simplifySousCatArray($list_sousCat);
	$matosPlanBySsCat = creerSousCatArray_showExterieur(@$matosPlanList);

	//echo '<pre>'; var_dump($matosPlanBySsCat);'</pre>';
	if ($matosPlanBySsCat != false) {
		foreach($matosPlanBySsCat as $ssCatId => $matosLine) {						// récupération de la liste du matos, des qtés, et recalcul du sous-total
			$sousCat = $list_sousCat[$ssCatId]['label'];
			foreach($matosLine as $matosInfo) {
				$idMatos = $matosInfo['id'];
				$qteMatos = $matos_plan[$idMatos];
				$retour['matos'][$sousCat][$idMatos]['cat']	  = $matosInfo['categorie'];
				$retour['matos'][$sousCat][$idMatos]['ref']	  = $matosInfo['ref'];
				$retour['matos'][$sousCat][$idMatos]['qte']	  = $qteMatos;
				$retour['matos'][$sousCat][$idMatos]['prix']  = $matosInfo['tarifLoc'] * $qteMatos;
				$retour['matos'][$sousCat][$idMatos]['ext']	  = $matosInfo['externe'];
				$retour['matos'][$sousCat][$idMatos]['extOwn']= $matosInfo['ownerExt'];
			}
		}
	}

	while ( $sp = $p->whileTestSousPlan() ) {									// récupération des sous plans
		$spID  = $p->getSousPlanId() ;
		$retour['sousPlans'][$spID]['id']		= $spID ;
		$retour['sousPlans'][$spID]['jour']		= datefr( date("l j F Y", $p->getSousPlanDate()) ) ;
		$retour['sousPlans'][$spID]['timestamp']= $p->getSousPlanDate() ;
		$retour['sousPlans'][$spID]['rem']		= $p->getSousPlanComment() ;
		$tekosIds = $p->getSousPlanTekos() ;
		$stringTekosList = '';
		foreach ($tekosIds as $id) {
			if ($id != '' && $id != ' ') {
				try {
					$tmpTekos = new Tekos($id);
					$stringTekosList .= $tmpTekos->getTekosInfos('surnom') . ', ';
				}
				catch (Exception $e) { continue; }
			}
		}
		$retour['sousPlans'][$spID]['tekos'] = substr($stringTekosList, 0, -2);
	}

	$tmp = Array();								// Tri du tableau des sous plans par leur timestamp
	if (is_array(@$retour['sousPlans'])) {
		foreach($retour['sousPlans'] as &$spTs)
			$tmp[] = &$spTs["timestamp"];
		array_multisort($tmp, $retour['sousPlans']);
	}
	else {
		$retour['sousPlans'] = array(array('id'=>0,'jour'=>'ERREUR','rem'=>'<b class="red">ERREUR : aucun jour sauvé... Recréez le plan pour corriger le problème.</b>','tekos'=>'???'));
	}

	if ($_SESSION['user']->isLevelMod()) $retour['levelAuth'] = true;
	else $retour['levelAuth'] = false;

	$retour = json_encode($retour);
	echo $retour ;
}


function test_benef ($idPlan) {
	$p = new Plan();
	$p->load('id', $idPlan);
	$benefPlan = $p->getPlanBenef();
	$l = new Liste();
	$benefList = $l->getListe(TABLE_STRUCT, 'label');
	if (in_array($benefPlan, $benefList)) return true;
	else return false;
}

?>
