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
    session_start();
    require_once('initInclude.php');    // OBLIGATOIRE pour les sessions,
    require_once('global_config.php');  // à placer TOUJOURS EN HAUT du code.
    require_once('checkConnect.php');

    $titrePageBar = "ROBERT";
    include('inc/head_html.php');
?>
<body>
<div id="bigDiv">
    <div id="Page" class="fondPage">
        <div class="colonne L ui-widget fondSect1 center">
            <?php
            if (isset($_SESSION["user"])) {
                echo '<div id="logo">
                    <img src="gfx/Robert2.png" width="100%" />
                    <br /><br />
                </div>';

                $page_admin = ['calendrier', 'materiel', 'gens', 'beneficiaires', 'infos', 'sauvegarde'];
                $page_users = ['calendrier', 'materiel', 'gens', 'beneficiaires'];
                $choose     = [];

                if ($_SESSION['user']->isAdmin()) {
                    $choose = $page_admin;
                } else {
                    $choose = $page_users;
                }

                foreach ($choose as $k) {
                    $nomPage = $k;
                    $classUi = 'default';
                    if (isset($_GET['go']) && @$_GET['go'] == $nomPage) {
                        $classUi = 'highlight';
                    } elseif ((
                        !isset($_GET['go'])
                        || @$_GET['go'] == 'ajout_plan'
                        || @$_GET['go'] == 'modif_plan'
                    ) && $k == 'calendrier') {
                        $classUi = 'highlight';
                    }
                    echo "<div class='ui-state-$classUi ui-corner-all menu_icon'>
                        <a href='?go=$nomPage'><img class='img_menu' src='gfx/icones/menu/$nomPage.png' />
                        <br />".strtoupper($nomPage)."</a>
                    </div>";
                }
            }
            ?>
            <br />
            <span class="boutonMenu petit noMarge"
                  title="Site officiel de Robert">
                <a href="http://www.robert.polosson.com" target="_new"><b>Site officiel</b></a>
            </span>
            <br /><br />
            <span class="boutonMenu petit noMarge"
                  title="Indiquez ici les bugs que vous trouvez">
                <a href="http://www.robert.polosson.com/buglist.php" target="_new"><b>BugHunter</b></a>
            </span>
        </div>
        <div class="colonne C ui-widget fondSect2 petit">
            <?php
            if (!isset($_SESSION['user'])) {
                include('modals/connexion.php');
            } else {
                if (isset($_GET["go"])) {
                    $goto = 'pages/p_' . $_GET["go"] .'.php';
                    if ((@include($goto)) === false) {
                        echo "<div class='ui-state-error ui-corner-all pad20 big center'>
                            <p>La page <b>\"".$_GET['go']."\"</b> n'existe pas !</p>
                            <p><a href='?go=calendrier'>RETOUR AU CALENDRIER</a></p>
                        </div>";
                    }
                } else {
                    include('pages/p_calendrier.php');
                }
            }
            ?>
        </div>
        <div class="colonne R ui-widget fondSect1 petit center">
            <?php
            if ($logged == true) {
                include('menuRight.php');
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>
