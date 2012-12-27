<?php
@session_start();
require_once ('initInclude.php');
require_once ('common.inc');  // OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

//$action = '';
//extract($_POST);


// Fonction de lecture des fichiers XMl
function readXML ($fileXML) {
	if ($fileXML == 'bugs.xml')		  $bigBloc = 'bugList';
	elseif ($fileXML == 'wishes.xml') $bigBloc = 'wishList';
	else return false;
	
	$xml = simplexml_load_file('bugHunter/'.$fileXML);
	
	$retour = array() ; $i=0;
	foreach ($xml->$bigBloc->children() as $item) {
		
		foreach ($item->attributes() as $attrName => $attrValue) {
			if ($attrName == 'id')    $itemId    = (int) $attrValue ;
			if ($attrName == 'by')    $itemBy    = (string) $attrValue ;
			if ($attrName == 'prio')  $itemPrio  = (string) $attrValue ;
			if ($attrName == 'fixer') $itemFixer = (string) $attrValue ;
		}
		$retour[$i]['id'] = $itemId ;
		$retour[$i]['by'] = $itemBy ;
		$retour[$i]['fixer'] = $itemFixer ;
		if (isset($itemPrio))
			$retour[$i]['prio'] = $itemPrio ;
		
		if (count($item->children()) != 0) {
			foreach($item->children() as $type => $data) {
				$retour[$i][$type] = (string) $data;
			}
		}
		else $retour[$i]['descr'] = 'Pas de description.';
		
		$i++;
	}
	
	return $retour ;
}



?>


