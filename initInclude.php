<?php
/*
 *
    Robert est un logiciel libre; vous pouvez le redistribuer et/ou
    le modifier sous les termes de la Licence Publique Générale GNU Affero
    comme publiée par la Free Software Foundation;
    version 3.0.

    Cette WebApp est distribuée dans l'espoir qu'elle soit utile,
    mais SANS AUCUNE GARANTIE; sans même la garantie implicite de
	COMMERCIALISATION ou D'ADAPTATION A UN USAGE PARTICULIER.
	Voir la Licence Publique Générale GNU Affero pour plus de détails.

    Vous devriez avoir reçu une copie de la Licence Publique Générale
	GNU Affero avec les sources du logiciel; si ce n'est pas le cas,
	rendez-vous à http://www.gnu.org/licenses/agpl.txt (en Anglais)
 *
 */

define("INSTALL_PATH", dirname(__FILE__). '/');

$pathInc   = "inc";
$pathFct   = "fct";
$pathConf  = "config";
$pathClass = "classes";
$pathModals= "modals";
$pathFonts = "font";

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    $pathInc . PATH_SEPARATOR .
    $pathFct . PATH_SEPARATOR .
    $pathConf . PATH_SEPARATOR .
    $pathClass . PATH_SEPARATOR .
    $pathModals . PATH_SEPARATOR .
    $pathFonts
);
