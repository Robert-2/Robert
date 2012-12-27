<?php
	session_start();
	require_once ('initInclude.php');
	require_once ('common.inc');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
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
