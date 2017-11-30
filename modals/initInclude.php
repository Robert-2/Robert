<?php

define("INSTALL_PATH", dirname(__FILE__) . '/../');
// global INSTALL_PATH;

$pathInc  = "../inc";
$pathConf = "../config";
$pathClass = "../classes";
$pathFct = "../fct";
$pathFonts = "../font";
set_include_path(get_include_path() . PATH_SEPARATOR . $pathInc . PATH_SEPARATOR . $pathConf . PATH_SEPARATOR . $pathClass . PATH_SEPARATOR . $pathFct . PATH_SEPARATOR . $pathFonts);

?>
