<?php
define(INSTALL_PATH, dirname(__FILE__). '/../');

$pathInc   = "../inc";
$pathConf  = "../config";
$pathClass = "../classes";
$pathFCT   = "../fct";
$pathFonts = "../font";

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    $pathInc . PATH_SEPARATOR .
    $pathConf . PATH_SEPARATOR .
    $pathClass . PATH_SEPARATOR .
    $pathFonts
);
