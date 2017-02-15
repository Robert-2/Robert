<?php

if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$lCateg = new Liste();
$lMatos = new Liste();

$categ = $lCateg->getListe(TABLE_MATOS_CATEG, '*' );
$matos = $lMatos->getListe(TABLE_MATOS, '*') ;

$matos = simplifieTableauListe ($matos);
$categ = simplifieTableauListe ($categ);

$sousCategList = array();
foreach ( $matos as $matosData ){

	$sousCatMatos = $matosData['sousCateg'];
	if ( isset ($sousCategList[$sousCatMatos] ) )
		$sousCategList[$sousCatMatos][] = $matosData["id"]  ;
	else
		$sousCategList[$sousCatMatos] = array($matosData["id"])  ;
}




foreach ( $sousCategList as $loop => $table ){

	if ( isset ( $categ[$loop] ) ) $name = $categ[$loop]['label']; else $name='Tas de boue';

	echo "<h1>$name $loop</h1>";

	foreach ( $table as $key => $data ){
	
		$matosName = $matos[$data]['ref'];
		echo "$matosName , ";
	}
	

}


// supprimme une dimension du tableau retourné par l'objet liste
// pour faire correspondre l'ID tableau avec l'ID Matos'
function simplifieTableauListe ( $listArray ){

	$newTableau = array();

	foreach( $listArray as $ind => $Matos){
		$ind = $Matos['id'];
		$newTableau[$ind] = $Matos ;
	}

	return $newTableau ;

}


?>



