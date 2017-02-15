<?php
	session_start();
	require_once ('initInclude.php');
	require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
	require_once ('checkConnect.php' );

	require_once ( '../' . FOLDER_CONFIG . 'infos_boite.php');

	if (!isset($_GET['tek'])) { die('pas de technicien sélectionné !') ; }
	$idTekos = $_GET['tek'] ;
	$t = new Tekos($idTekos);
	$tekos = $t->getTekosInfos();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<meta name="robots" content="noindex,nofollow" />
	
	<title>coordonnées de technicien</title>
	
	<link rel="shortcut icon" type="image/x-icon" href="../gfx/favicon.ico" />
	
	<link type="text/css" href="../<?php echo chooseThemeFolder(); ?>/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
	<link type="text/css" href="../css/ossature.css" rel="stylesheet" />
	<link type="text/css" href="../css/ossature_print.css" rel="stylesheet" media="print"/>
	<link type="text/css" href="../<?php echo chooseThemeFolder(); ?>/colors.css" rel="stylesheet" />
	
	<script type="text/javascript" src="../js/jquery-1.7.min.js">// JQUERY CORE</script>
	<script type="text/javascript" src="../js/jquery-ui-1.8.17.custom.min.js">// JQUERY UI</script>

<style>
	.titreSection { margin : 5% 2% 0% 2%; }
	.container    { margin : 1% 0% 0% 2%;}
</style>
	
</head>

<body>
	<div style="float:right; margin-right:20px;"><button class="gros printHide" onClick="window.print()">IMPRIMER</button></div>
	
	<div class='ui-widget titreSection bordFin ui-corner-all'>
		<div class='ui-widget-header ui-corner-all gros pad5' style='color:white; background-color:orange;'>
			Coordonnées de <?php  echo $tekos['prenom'].' '.$tekos['nom'];  ?>
		</div>

		<div class='container'>
			
			<p>Surnom : <b><?php  echo $tekos['surnom']; ?></b></p>
			
			<p>Date de naissance : <b><?php  echo date_format(new DateTime($tekos['birthDay']), 'd/m/Y') ; ?></b></p>

			<p>Lieu de naissance : <b><?php  echo $tekos['birthPlace']; ?></b></p>

			<p>Technicien <b><?php  echo $tekos['categorie']; ?></b></p>
			<p>Adresse Email : <b><?php  echo $tekos['email']; ?></b></p>

			<p>No de Tel. : <b><?php  echo $tekos['tel']; ?></b></p>

			<p>Intermittent : <b><?php  echo ($tekos['intermittent']==1) ? 'oui' : 'non'; ?></b></p>

			<p>No SECU : <b><?php  echo $tekos['SECU']; ?></b></p>

			<p>No GUSO : <b><?php  echo $tekos['GUSO']; ?></b></p>

			<p>No Congés Spectacle : <b><?php  echo $tekos['CS']; ?></b></p>
			<p>Numero Assédic : <b><?php  echo $tekos['assedic']; ?></b></p>

			<p>No SIRET : <b><?php  echo $tekos['SIRET']; ?></b></p>

			<p>Adresse actuelle : <br />
				<b>
					<?php echo $tekos['adresse']; ?><br />
					<?php echo $tekos['cp']; ?> 
					<?php echo $tekos['ville']; ?>
				</b>
				
			</p>
		</div>
	</div>


</body>
</html>
