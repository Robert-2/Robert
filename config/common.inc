<?php
/*
 *
    Le Robert est un logiciel libre; vous pouvez le redistribuer et/ou
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

$host = $_SERVER['HTTP_HOST'];
$serverName = php_uname('n');
//echo $serverName ;


/* ***************************************************************************** */
/* ***************************************************************************** */
/*

 *	Modifiez les lignes 40 à 44 ci-dessous, en vous inspirant de l'exemple, pour renseigner :
 *	votre nom de domaine
 *	le nom de l'hôte ou se trouve la base de données
 *	le nom de l'utilisateur SQL
 *	le mot de passe de l'utilisateur SQL
 *  le nom de la base de données
 *
 *  N'oubliez pas de sauvegarder.

*/

if ($host=='www.exemple.com') {													// Nom de domaine cible
	define ("HOST", "host.db");													// nom de l'hôte ou se trouve la bdd
	define ("USER", "userDB");													// nom de l'utilisateur autorisé à se connecter
	define ("PASS", "passDB");													// son mot de passe
	define ("BASE", "robertBase");												// nom de la base de données
}

/* ***************************************************************************** */
/* ***************************************************************************** */

define ("DEVS_MAILS", "polo@polosson.com, mathieu@d2mphotos.fr");

define ("DSN", 'mysql:dbname='.BASE.';host='.HOST);								// données de connexion à la BDD via PDO

define ("R_VERSION", '0.5');

define ("TABLE_USERS", "users");												// table des utilisateurs dans la BDD
define ("TABLE_CAL"  , "calendar");												// table du calendrier
define ("TABLE_MATOS", "matos_detail");											// table du matoss au détail
define ("TABLE_MATOS_CATEG", "matos_sous_cat");								    // table des notes de calendrier
define ("TABLE_PACKS", "matos_packs");											// table des packs de matoss
define ("TABLE_TEKOS", "tekos");												// table des techniciens
define ("TABLE_PLANS", "plans");												// table des plans
define ("TABLE_PLANS_DETAILS", "plans_details");							    // table des details pour chaque jour de plan
define ("TABLE_STRUCT", "benef_structure");										// table des structures bénéficiaires
define ("TABLE_INTERLOC", "benef_interlocuteurs");								// table des interlocuteurs de structures bénéficiaires
define ("TABLE_DEVIS", "devis");												// table des devis
define ("TABLE_NOTES", "notes");												// table des notes de calendrier

define ("FOLDER_ADMIN",		"admin/");											// Decription des dossiers du site
define ("FOLDER_CLASSES",	"classes/");
define ("FOLDER_CONFIG",	"config/");
define ("FOLDER_CSS",		"css/");
define ("FOLDER_FCT",		"fct/");
define ("FOLDER_GFX",		"gfx/");
define ("FOLDER_JS",		"js/");
define ("FOLDER_INC",		"inc/");
define ("FOLDER_MAILS",		"mails/");
define ("FOLDER_PAGES",		"pages/");

define ("FOLDER_TEKOS_DATAS", "datas/TEKOS_DATAS/");
define ("FOLDER_PLANS_DATAS", "datas/PLANS_DATAS/");

define ("SALT_PASS",		'G:niUk5!1|WQ');									// Grain de sel (seed) pour la création / récup de password in BDD
define ("COOKIE_NAME_LOG",	'auth_login');										// nom du cookie gestion login
define ("COOKIE_NAME_PASS", 'auth_password');									// nom du cookie gestion password
define ("COOKIE_PEREMPTION", time() + (3600 * 24 * 2));							// péremption des cookies : 2 jours.


// fonction de suppression de dossier, vite fait, dispo pour tout le monde !
function rrmdir($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }


function coef($nbJours) {
	$c = ($nbJours - 1) * 3/4 + 1;
	return $c;
}

?>
