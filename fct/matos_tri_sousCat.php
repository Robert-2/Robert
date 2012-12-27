<?php

function creerSousCatArray ($liste_Matos = null) {
	if ($liste_Matos == null) return false ;
	
	$sousCategList = array();
	foreach ( $liste_Matos as $matos ){

		if ( ! isset ( $matos['sousCateg']) ) continue ;
		$sousCategList[$matos['sousCateg']][] = $matos ;
	}
	
	if ( empty ($sousCategList)  ) return false ; 
	return $sousCategList;
}


// cree une sous categorie supplementaire nommée //
	// contenant le materiel a louer //
function creerSousCatArray_showExterieur ($liste_Matos = null ) {
	if ($liste_Matos == null) return false ;

	$sousCategList = array();
	$ext = array(); 
	foreach ( $liste_Matos as $matos ){

		if ( ! isset ( $matos['sousCateg']) ) continue ;

		if ( $matos['externe'] == '1') {
			$ext[] = $matos;
		}
		else
			$sousCategList[$matos['sousCateg']][] = $matos ;
	}
	
	if ( empty ($sousCategList) && empty($ext) ) return false ;
	$sousCategList[999] = $ext;
	return $sousCategList;
}



function simplifySousCatArray($liste_sousCat = null) {
	
	if ($liste_sousCat == null) {
		$ls = new Liste();
		$liste_sousCat = $ls->getListe(TABLE_MATOS_CATEG, '*', 'ordre', 'ASC');
		unset($ls);
	}
	array_push($liste_sousCat, array ( 'id'=> 0, 'label'=> 'sans sous catégorie' ));
	array_push($liste_sousCat, array ( 'id' => 999, 'label' => 'A louer' ));
	
	$newTableau = array();
	foreach( $liste_sousCat as $ssCat){
		$ind = $ssCat['id'];
		$newTableau[$ind] = $ssCat ;
	}
	return $newTableau ;
}


// trie le tableau de materiel exterieur par le champ 'ownerExt' !
function MatosExt_by_Location ( $listeMatosExterieur ){

	//echo "<pre>"; print_r ($listeMatosExterieur) ;echo "</pre>";

	$newTableau = array();

	foreach ( $listeMatosExterieur as $matos ){
		$newTableau[$matos['ownerExt']][] = $matos ;
	}
	//echo "<pre>"; print_r ($newTableau) ;echo "</pre>";
	return $newTableau ; 

}


?>
