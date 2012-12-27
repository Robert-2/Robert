<?php
function getDumpList () {
	$list = array();
	$dir = opendir($install_path.FOLDER_CONFIG.'dumpSQL');
	while (($fileSQL = readdir($dir)) !== false) {
		if ($fileSQL != '.' && $fileSQL != '..')
		$list[] = $fileSQL;
	}
	rsort($list, SORT_STRING);
	return $list;
}

function getTableList () {
	global $bdd;
	$q = $bdd->prepare('SHOW TABLES');
	$q->execute();
	$tablesNames = $q->fetchAll(PDO::FETCH_COLUMN);
	return $tablesNames ;
}

?>
