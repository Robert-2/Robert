<?php

global $install_path;

function list_themes () {
	global $install_path;
	$list = array();
	$dir = opendir($install_path.FOLDER_CSS);
	while (($fileCSS = readdir($dir)) !== false) {
		if ($fileCSS != '.' && $fileCSS != '..' && $fileCSS != 'ossature.css' && $fileCSS != 'fileuploader.css' && $fileCSS != 'ossature_print.css')
		$list[] = $fileCSS;
	}
	sort($list, SORT_STRING);
	return $list;
}

?>
