<?php

require_once('PDOinit.php');


function getNomsChamps ($table) {
	global $bdd;
	
	$q = $bdd->prepare("SHOW COLUMNS FROM `$table`");
	$q->execute();
	if ($q->rowCount() >= 1) {
		$result = $q->fetchAll(PDO::FETCH_COLUMN) ;
		if (($K = array_search('password', $result)) !== false)
			unset($result[$K]);
		return $result;
	}
	else return false;
	
}



?>
