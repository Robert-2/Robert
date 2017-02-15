<?php
session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

extract( $_POST );

// Chargement du calendrier, et formattage Json pour la frise
if ( @$action == 'loadCalendar'){
	
	$_SESSION['periodeCal']['start'] = $jS.'/'.$mS.'/'.$yS;
	$_SESSION['periodeCal']['end']   = $jE.'/'.$mE.'/'.$yE;
	
	$startCal = mktime (0,0,0, $mS, $jS, $yS);
	$EndCal = mktime(0,0,0, $mE, $jE, $yE);

	$c = new Calendar () ;
	$nb = $c->InitPlans ( $startCal, $EndCal );

	$indexPlans = $c->getIndexes();
	$cals = array();
	if (is_array($indexPlans)) {
		foreach ($indexPlans as $ind => $v ){
			$id    = $c[$ind]->getPlanID() ;
			$cals[$id]['titre']   = $c[$ind]->getPlanTitre() ;
			$cals[$id]['lieu']    = $c[$ind]->getPlanLieu() ;		
			$cals[$id]['confirm'] = $c[$ind]->getPlanConfirm();
			$cals[$id]['creator'] = $c[$ind]->getPlanCreateur();
			$cals[$id]['nbJours'] = $c[$ind]->getNBSousPlans() ;

			$cals[$id]['debut']   =  date ( 'Y,n,j', $c[$ind]->getPlanStartDate() );
			$cals[$id]['fin']     =  date ( 'Y,n,j', $c[$ind]->getPlanEndDate() );

		}
		$cals = json_encode($cals);
	}
	else {
		$cals['erreur'] = 'rien';
		$cals = json_encode($cals);
	}
	echo $cals ;
}


// Export du calendrier au format ICS
if ( @$action == 'export'){
	require('export_ICS.php');
	if (ICS_exporter())
		echo 'EXPORT ICS RÉUSSI !';
	else echo 'IMPOSSIBLE D\'EXPORTER LE FICHIER !';
}




?>
