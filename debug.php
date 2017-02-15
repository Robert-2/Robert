<?php
	session_start();
	require_once ('initInclude.php');
	require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
	require_once ('checkConnect.php' );

	$titrePageBar = "MPM - DEBUG";
	$titrePage = "DEBUG du Modèle Polo Mout";
	include('head_html.php');
?>

<body>

    <style>
        .debugHeader   { width:99%; padding:4px; }
		.debugSection  { margin-bottom: 10px; }
		.PageDebug	   { position: absolute; top:40px; bottom:30px; width:100%; overflow: scroll; }
		.panDroite	   { position: absolute; top:40px; bottom: 5px; right: 2px; width: 15%; overflow:auto; background-color:#333; padding: 5px; color:#fff; font-size: 0.7em; }
		.debugTable	   { border-spacing: 0px; width: 99%; font-size: 0.8em; }
		.debugTable td { border: 1px solid; padding:3px; }
    </style>

	<div id="bigDiv">
		<div class='debugHeader ui-widget'>
			<span class="boutonMenu marge30l"><a href="index.php">RETOUR INDEX</a></span>
			<select id="themeSel">
				<option disabled selected>CHOIX DU THEME</option>
				<?php include ('fct/list_themes.php');
				$themesDispo = list_themes();
				foreach ($themesDispo as $theme)
					echo "<option value='$theme'>$theme</option>";
				?>
			</select>
		<?php
			if ($handle = opendir('debug/')) {
				while (($entry = readdir($handle)) !== false) {
					if (strpos($entry, ".php") && substr($entry,0,2) == 'd_'  )
						echo "<span class='boutonMenu '><a href='debug.php?file=$entry'>$entry</a></span>";
				}
			}
		?>
		</div>

		<div class="PageDebug ui-widget">

			<?php if ( isset($_GET["file"])) include ('debug/' . $_GET["file"]); ?>
		</div>


<!--		<div id="logo"><img src="nothing" alt=""/></div>-->

	</div>
</body>
</html>
