<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">
<head>
	<meta charset="utf-8" />
	<meta name="keywords" content="" />
	<meta name="description" content="" />
	
	<meta name="robots" content="noindex,nofollow" />
	
	<title><?php echo $titrePageBar ; ?></title>
	
	<link rel="shortcut icon" type="image/x-icon" href="gfx/favicon.ico" />
	
	<link type="text/css" href="./<?php echo chooseThemeFolder(); ?>/jquery-ui-1.8.17.custom.css" rel="stylesheet" />
	<link type="text/css" href="./css/ossature.css" rel="stylesheet" />
	<link type="text/css" href="./css/ossature_print.css" rel="stylesheet" media="print"/>
	<link type="text/css" href="./<?php echo chooseThemeFolder(); ?>/colors.css" rel="stylesheet" />
	
	<script type="text/javascript" src="./js/jquery-1.7.min.js">// JQUERY CORE</script>
	<script type="text/javascript" src="./js/jquery-ui-1.8.17.custom.min.js">// JQUERY UI</script>
	<script type="text/javascript" src="./js/jquery.ui.datepicker-fr.js">// Patch français pour DatePicker</script>
	<script type="text/javascript" src="./js/jquery.ajaxq-0.0.1.js">// JQUERY plugin : file d'attente des requêtes ajax</script>
	<script type="text/javascript" src="./js/jquery.urlGet.js">// JQUERY plugin : récupérer les variables GET dans l'url</script>
	<script type="text/javascript" src="./js/toolTip.js">// petit script pour afficher les tooltips</script>
	<script type="text/javascript">
		// définition des variables JS accessibles partout
		var ID_USER = '<?php echo (isset($_SESSION['user'])) ? $_SESSION['user']->getUserInfos('id') : 'none'; ?>' ;
	</script>
	<script type="text/javascript" src="./js/init_all_pages.js">// scripts et fonctions à charger pour toutes les pages</script>
	<script type="text/javascript" src="./js/interface.js">// gestions des menus </script>
	
</head>
