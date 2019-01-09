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

define("R_VERSION", '0.6.3');

$host       = $_SERVER['HTTP_HOST'];
$serverName = php_uname('n');

$userConfigExample = INSTALL_PATH . 'config/exemple.user_config.php';
$userConfig        = INSTALL_PATH . 'config/user_config.php';

require('global_errors.php');

if (!is_file($userConfig)) {
    echo $err_head . $err_noConfig;
    if (!copy($userConfigExample, $userConfig)) {
        echo $err_noConfigCopy;
    }
    chmod($userConfig, 0777);
    die();
}

require('user_config.php');

define("DEVS_MAILS", "polo@polosson.com, mathieu@d2mphotos.fr"); // Adresses email des développeurs

define("DSN", 'mysql:dbname='.BASE.';host='.HOST); // données de connexion à la BDD via PDO

define("TABLE_USERS", "robert_users"); // table des utilisateurs dans la BDD
define("TABLE_CAL", "robert_calendar"); // table du calendrier
define("TABLE_MATOS", "robert_matos_detail"); // table du matoss au détail
define("TABLE_MATOS_CATEG", "robert_matos_sous_cat"); // table des notes de calendrier
define("TABLE_PACKS", "robert_matos_packs"); // table des packs de matoss
define("TABLE_TEKOS", "robert_tekos"); // table des techniciens
define("TABLE_PLANS", "robert_plans"); // table des plans
define("TABLE_PLANS_DETAILS", "robert_plans_details"); // table des details pour chaque jour de plan
define("TABLE_STRUCT", "robert_benef_structure"); // table des structures bénéficiaires
define("TABLE_INTERLOC", "robert_benef_interlocuteurs"); // table des interlocuteurs de structures bénéficiaires
define("TABLE_DEVIS", "robert_devis"); // table des devis
define("TABLE_NOTES", "robert_notes"); // table des notes de calendrier

define("FOLDER_ADMIN", "admin/"); // Decription des dossiers du site
define("FOLDER_CLASSES", "classes/");
define("FOLDER_CONFIG", "config/");
define("FOLDER_CSS", "css/");
define("FOLDER_FCT", "fct/");
define("FOLDER_GFX", "gfx/");
define("FOLDER_JS", "js/");
define("FOLDER_INC", "inc/");
define("FOLDER_MAILS", "mails/");
define("FOLDER_PAGES", "pages/");

define("FOLDER_TEKOS_DATAS", "datas/TEKOS_DATAS/");
define("FOLDER_PLANS_DATAS", "datas/PLANS_DATAS/");

define("SALT_PASS", 'G:niUk5!1|WQ'); // Grain de sel (seed) pour la création / récup de password in BDD
define("COOKIE_NAME_LOG", 'auth_login'); // nom du cookie gestion login
define("COOKIE_NAME_PASS", 'auth_password'); // nom du cookie gestion password
define("COOKIE_PEREMPTION", time() + (3600 * 24 * 2)); // péremption des cookies : 2 jours.

date_default_timezone_set('Europe/Paris'); // La timezone par défaut, si introuvable dans le php.ini

// fonction de suppression de dossier récursive
if (!function_exists('rrmdir')) {
    function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") {
                        rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}

// fonction de calcul du coeficient
if (!function_exists('coef')) {
    function coef($nbJours)
    {
        return ($nbJours - 1) * 3/4 + 1;
    }
}
