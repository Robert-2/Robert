<?php

$install_path = dirname(__FILE__) . '/../';
global $install_path;

$pathInc  = "../inc";
$pathConf = "../config";
$pathClass = "../classes";
$pathFCT = "../fct";
$pathFonts = "../font";
set_include_path(get_include_path() . PATH_SEPARATOR . $pathInc . PATH_SEPARATOR . $pathConf . PATH_SEPARATOR . $pathClass . PATH_SEPARATOR . $pathFonts);

?>
