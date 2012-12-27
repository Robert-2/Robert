<?php

$install_path = dirname(__FILE__). '/';
global $install_path;

$pathInc   = "inc";
$pathFct   = "fct";
$pathConf  = "config";
$pathClass = "classes";
$pathModals= "modals";
$pathFonts = "font";
set_include_path(get_include_path() . PATH_SEPARATOR . $pathInc . PATH_SEPARATOR . $pathFct . PATH_SEPARATOR . $pathConf . PATH_SEPARATOR . $pathClass . PATH_SEPARATOR . $pathModals . PATH_SEPARATOR . $pathFonts);

?>
