<?php
	session_start();
	require_once ('initInclude.php');
	require_once ('common.inc');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
	require_once ('checkConnect.php' );

	if ( !isset($_SESSION['user']) || !$_SESSION['user']->isLevelMod())
		header('Location: index.php');

	$titrePageBar = "ROBERT - BUG hunter";
	include('inc/head_html.php');
	require_once('bugHunter/xmlBUGparser.php');

	if (!$_SESSION['user']->isAdmin())
		$showPanic = 'hide';

	$nomUser = $_SESSION['user']->getUserInfos(Users::USERS_PRENOM);
	$idUser  = $_SESSION['user']->getUserInfos(Users::USERS_ID);

?>
<style>
	.colsBug { position:absolute; top:70px; bottom:5px; z-index: 500; }
</style>

<script>
	var prenomUser = '<? echo $nomUser ?>';
	var idUser = '<? echo $idUser ?>';
</script>
<script src="./bugHunter/bugHunter.js"></script>

<body>
	<div id="bigDiv">
		<div id="Page" class="ui-widget fondPage bordPage">
			<div class="leftText pad20 gros">
				<div class="inline mid mini">
					<span class="bouton"><a href="index.php">RETOUR INDEX</a></span>
				</div>
				<div class="inline mid gros center" style="width:80%;">
					<span class="enorme marge30l">BUG HUNTER</span>
					<span class="enorme marge30l"> - </span>
					<span class="enorme marge30l"><img src="gfx/Robert2.png" height="45" alt="ROBERT" /></span>
					<span class="gros">v <? echo R_VERSION; ?></span>
				</div>
			</div>

			<div class="colonne bordSection ui-widget ui-corner-all fondSect1 center colsBug" style="left:5px; width:155px; box-shadow: inset 0 0 5px #888888;">
				<div class='ui-state-highlight ui-corner-all bouton menuBH' id="menuBug">
					<img src='gfx/icones/menu/bugs.png' />
					<br />BUGs
				</div>
				<div class='ui-state-default ui-corner-all bouton  menuBH margeTop10' id="menuWishes">
					<img src='gfx/icones/menu/wish.png' />
					<br />WE WANT MORE
				</div>
				<div class='ui-state-default ui-corner-all bouton  menuBH margeTop10 <? echo @$showPanic; ?>' id="menuPanic">
					<img src='gfx/icones/menu/panic.png' />
					<br />PANIC
				</div>
			</div>

			<div class="colonne bordSection ui-widget ui-corner-all fondSect1 colsBug" style="left:180px; right:5px; box-shadow: 0 1px 3px #888888;">

				<div class="ui-widget-content ui-corner-all leftText BHsection" id="bugDiv">
					<div class="ui-widget-header ui-corner-all gros pad3">Liste des BUGS trouvés</div>
					<?
						$bugList = readXML('bugs.xml');
						$nbBugs = count($bugList);
					?>
					<script> var nextIDbug = <? echo $nbBugs + 1; ?>;</script>

					<div class="petit margeTop5 padV10">
						<button class="bouton" id="addBugBtn">J'ai trouvé un bug !</button>
					</div>
					<div class="margeTop10 padV10" id="bugsList">
						<?
						$bugList = readXML('bugs.xml');
						if ($nbBugs != 0) {
							foreach ($bugList as $bug) {
								echo '<div class="ui-state-default ui-corner-all pad3 marge15bot" id="bug-'.$bug['id'].'">
										<div class="inline top" style="width:150px;">#'.$bug['id'].' <i>par <b>'.$bug['by'].'</b></i></div>
										<div class="inline top">'.$bug['descr'].'</div>
										<br />
										<div class="fixerDiv inline top mini" style="width:150px;">';
								if ($_SESSION['user']->isDev() && $bug['fixer'] == '')
									echo '<button class="bouton bugFixeur" bug="'.$bug['id'].'" fixer="'.$nomUser.'">Jm\'en occupe</button>';
								elseif ($bug['fixer'] != '')
									echo '<span class="ui-state-error ui-corner-all" style="padding:1px;"><b>'.$bug['fixer'].'</b> s\'en occupe</span>';
								if ($_SESSION['user']->isDev() && $bug['fixer']  == $nomUser)
									echo '<br /><button class="ui-state-error bouton bugKiller margeTop5" bug="'.$bug['id'].'"><b>Kill da Bug</b></button>';
								echo '</div>
									  <div class="inline top pad10 margeTop5 shadowIn ui-corner-all">'.$bug['repro'].'</div>
									</div>';
							}
						}
						else {
							echo '<div class="ui-state-disabled ui-corner-all pad3 gros marge15bot">Pas de bug connu pour le moment !</div>';
						}
						?>

					</div>
				</div>
				<div class="ui-widget-content ui-corner-all leftText BHsection hide" id="wishesDiv">
					<div class="ui-widget-header ui-corner-all gros pad3">Trucs que vous aimeriez pouvoir faire...</div>
					<?
						$wishList = readXML('wishes.xml');
						$nbWish  = count($wishList);
					?>
					<script> var nextIDwish = <? echo $nbWish + 1; ?>;</script>

					<div class="petit margeTop5 padV10">
						<button class="bouton" id="addWishBtn">J'aimerai bien que...</button>
					</div>
					<div class="margeTop10 padV10" id="wishesList">
						<?
						if ($nbWish != 0) {
							$tmp = Array();					// tri des wishes par priorité
							foreach($wishList as &$bugSort)
								$tmp[] = &$bugSort["prio"];
							array_multisort($tmp, SORT_DESC, $wishList);

							foreach ($wishList as $wish) {
								echo '<div class="ui-state-default ui-corner-all pad5 marge15bot" id="wish-'.$wish['id'].'">
										<div class="inline top" style="width:130px;">#'.$wish['id'].' <i>par <b>'.$wish['by'].'</b></i></div>
										<div class="inline top mini" style="width:90px;">Priorité <b>'.$wish['prio'].'</b>/10</div>
										<div class="inline top">'.$wish['descr'].'</div>
										<br />
										<div class="fixerDiv inline top mini" style="width:220px;">';
								if ($_SESSION['user']->isDev() && $wish['fixer'] == '')
									echo '<button class="bouton wishFixeur" wish="'.$wish['id'].'" fixer="'.$nomUser.'">Jm\'en occupe</button>';
								elseif ($wish['fixer'] != '')
									echo '<span class="ui-state-error ui-corner-all" style="padding:1px;"><b>'.$wish['fixer'].'</b> s\'en occupe</span>';
								if ($_SESSION['user']->isDev() && $wish['fixer'] == $nomUser)
									echo '<br /><button class="ui-state-error bouton wishKiller margeTop5" wish="'.$wish['id'].'"><b>DONE ?</b></button>';
								echo '</div>
								</div>';
							}
						}
						else {
							echo '<div class="ui-state-disabled ui-corner-all pad3 gros marge15bot">Aucun truc à ajouter, le Robert est parfait !</div>';
						}
						?>
					</div>
				</div>

				<div class="ui-widget-content ui-corner-all leftText BHsection hide" id="panicDiv">
					<div class="ui-widget-header ui-corner-all gros pad3">AU SECOURS !!!!!!</div>
					<div class="pad10">
						Envoie un mail aux développeurs, en cas d'urgence... Pas de panique, expliquez leur :<br />
					</div>
					<div class="margeTop10 padV10">
						<textarea id="panicMessage" cols="80" rows="20"></textarea>
						<button class="inline top bouton" id="sendPanic">ENVOYER</button>
					</div>
				</div>

			</div>

		</div>


		<div id="dialogBug" class="hide petit" title="Ajouter un bug">
			Soyez précis, mais concis !
			<br /><br />
			<div class="ui-widget-header ui-corner-all pad3">Description :</div>
			<textarea id="newBugDescr" cols="60" rows="5"></textarea>
			<br /><br />
			<div class="ui-widget-header ui-corner-all pad3">Comment le reproduire :</div>
			<textarea id="newBugRepro" cols="60" rows="5"></textarea>
		</div>


		<div id="dialogWish" class="hide petit" title="Ajouter un truc que vous aimeriez">
			Expliquez en détail à quoi vous vous attendez, et pour quelle partie du Robert.
			<br /><br />
			<div class="ui-widget-header ui-corner-all pad3">Description :</div>
			<textarea id="newWishDescr" cols="60" rows="5"></textarea>
			<br /><br />
			<div class="ui-widget-header ui-corner-all pad3">
				Priorité :
				<select id="newWishPrio">
					<option value="1">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
					<option value="4">4</option>
					<option value="5" selected>5</option>
					<option value="6">6</option>
					<option value="7">7</option>
					<option value="8">8</option>
					<option value="9">9</option>
					<option value="10">10</option>
				</select>
				sur 10
				<span class="mini marge30l"><i>10 = "priorité maxi, limite urgent"</span><span class="mini marge30l"> 1 = "bah, juste comme ça"</i></span>
			</div>
		</div>


	</div>
</body>
</html>