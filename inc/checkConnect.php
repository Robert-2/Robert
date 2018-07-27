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

require_once('autoload.php');
require_once('PDOinit.php');

$Auth = new Connecting($bdd);

// CONNEXION
if (isset($_POST['conx']) && isset($_POST['email']) && isset($_POST['password'])) {
    if (!$Auth->connect($_POST['email'], $_POST['password'])) {
        $errAuth = true;
    } else {
        $errAuth = false;
    }
} else {
    $errAuth = false;
}

// DECONNEXION
if (isset($_GET['action'])) {
    if ($_GET['action'] == 'deconx') {
        $Auth->disconnect();
        unset($_SESSION);
        $logged = false ;
    }
} else {
    // VÉRIF SI TOUJOURS CONNECTÉ
    $isStillConnect = $Auth->is_connected();
    if ($isStillConnect !== false) {
        // Crée une instance User à chaque rechargement de page si connecté
        try {
            $_SESSION["user"] = new Users($isStillConnect);
            $logged = true ;
            $userTheme = $_SESSION["user"]->getUserInfos('theme');
            $_SESSION['theme'] = $userTheme;
        } catch (Exception $e) {
            echo "Erreur lors de la connexion -> création USER en session : ".$e->getMessage() ;
            $logged = false;
            unset($_SESSION["user"]);
        }
    } else {
        $logged = false;
    }
}

function chooseThemeFolder()
{
    if (isset($_SESSION['theme']) && $_SESSION["theme"] != '') {
        $repCss = 'css/'.$_SESSION['theme'];
        if (file_exists($repCss)) {
            return $repCss;
        } else {
            return 'css/human';
        }
    } else {
        return 'css/human';
    }
}
