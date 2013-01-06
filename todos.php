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


	session_start();
	require_once ('initInclude.php');		// OBLIGATOIRE pour les sessions,
	require_once ('common.inc');			// à placer TOUJOURS EN HAUT du code
	require_once ('checkConnect.php' );

	$titrePageBar = "ROBERT - TODOs";
	include('inc/head_html.php');

?>

<body>
	<div id="bigDiv">
		<div id="Page" class="ui-widget fondPage bordPage">
			<div class="leftText pad20">
				<span class="bouton gros marge30r"><a href="index.php">RETOUR INDEX</a></span>
				<span class="enorme marge30l">TODOs</span>
			</div>
			<div class="center">
				<div class="ui-widget-content ui-corner-all shadowOut marge5 leftText">
					<div class="pad10 ui-corner-all shadowIn marge5 ">
						<p class="">
						</p>
					</div>
<!--
					_ Câler un "oeuf de pâques" quelque part :D<br />
					_ Gestion des devis multiples avec une table "devis", qui enregistre des listes de matos<br />
 -->
				</div>
			</div>
		</div>
	</div>
</body>
</html>
