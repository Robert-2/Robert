<?php
	session_start();
	require_once ('initInclude.php');
	require_once ('common.inc');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
	require_once ('checkConnect.php' );
    
	$titrePageBar = "ROBERT";
	include('inc/head_html.php');

?>

<body>
	
	<div id="bigDiv">
		<?php
		
		?>
		<div id="Page" class="fondPage bordPage">
			
            <div class="colonne L bordSection ui-widget ui-corner-all fondSect1 center">
				<?php
				if ( isset($_SESSION["user"])) {
					echo '<div id="logo">
							<img src="gfx/Robert2.png" width="100%" />
							<br /><br />
						</div>';

					$page_admin = array ('calendrier', 'materiel', 'gens', 'beneficiaires', 'infos', 'sauvegarde' );
					$page_users = array ('calendrier', 'materiel', 'gens', 'beneficiaires' );
					$choose = array();
					
					if ( $_SESSION['user']->isAdmin() ) $choose = $page_admin ;  else $choose = $page_users ;

					foreach ( $choose as $k ){
						$nomPage = $k;
						$classUi = 'default';
						if (isset($_GET['go']) && @$_GET['go'] == $nomPage)
							$classUi = 'highlight';
						elseif ((!isset($_GET['go']) || @$_GET['go'] == 'ajout_plan' || @$_GET['go'] == 'modif_plan') && $k == 'calendrier')
							$classUi = 'highlight';
						echo "<div class='ui-state-$classUi ui-corner-all menu_icon'>
								<a href='?go=$nomPage'><img class='img_menu' src='gfx/icones/menu/$nomPage.png' />
								<br />".strtoupper($nomPage)."</a>
							  </div>";
					}
					if ( $_SESSION['user']->isLevelMod() ) {
						echo '<span class="boutonMenu petit noMarge" title="Indiquez ici les bugs que vous trouvez, mais aussi les choses que vous aimeriez voir sur la prochaine version...">
								<a href="bugHunter.php"><b>BUGs</b> Hunter</a>
							</span>';
					}
				}
				?>
				<span class="boutonMenu petit noMarge" title="Trucs qu'il nous reste à faire">
					<a href="todos.php"><b>TODOs</b></a><br />
				</span>
				

			</div>

			
			
			<div class="colonne C bordSection ui-widget ui-corner-all fondSect2 petit">
				<?	if ( !isset($_SESSION['user']) ) include ('modals/connexion.php');
					else {
						if ( isset($_GET["go"])) {
							$goto = 'pages/p_' . $_GET["go"] .'.php';
							if ( ( @include ($goto) ) == false)
								echo "<div class='ui-state-error ui-corner-all pad20 big center'>
										<p>La page <b>\"".$_GET['go']."\"</b> n'existe pas !</p>
										<p><a href='?go=calendrier'>RETOUR AU CALENDRIER</a></p>
									</div>";
						}
						else include ('pages/p_calendrier.php');
					}
				?>
			</div>
			
			
			<div class="colonne R bordSection ui-widget ui-corner-all fondSect1 petit center">
				<? 
				if ($logged == true) {
					include('menuRight.php');
				}
				?>
				
			</div>
			
		</div>

		<? // include('pages/footPage.php'); ?>
		
	</div>
</body>
</html>
