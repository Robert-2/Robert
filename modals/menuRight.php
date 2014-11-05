<?php
if ( !isset($_SESSION["user"])) { echo '<span>Aucune session active !!</span>'; return; }

require_once('list_themes.php');
$now = time();
$dixMinutes = $now - (60 * 10);
$l = new Liste();
$list_Users_connected = $l->getListe(TABLE_USERS, 'prenom, level, date_last_action', 'date_last_action', 'DESC', 'date_last_action', '>', $dixMinutes);

?>

<script src="./js/modifInfosUser.js"></script>

<?php
echo 'Hello <b>'.$_SESSION['user']->getUserInfos('prenom').'</b> !

	<span class="boutonMenu"><a href="index.php?action=deconx">déconnexion</a></span>

	<p><select id="themeSel">
		<option disabled selected value="">Thème...</option>';
		$themesDispo = list_themes();
		foreach ($themesDispo as $theme)
			echo "<option value='$theme'>$theme</option>";
		echo '</select>
	</p>';

if ($_SESSION['user']->getUserInfos('prenom') != "Demo") : ?>
	<button class="bouton" id="modifInfoUserActif" idUser="<?php echo $_SESSION['user']->getUserInfos('id'); ?>">Mes infos</button>
<?php endif; ?>

<br />
<br />--------------------------<br />
<br />

<div id="rightMenuSection">
	<div class="ui-state-default ui-corner-all">
		Qui était là aussi<br /><i class="mini">ces 10 dernières min. ?</i>
		<p class="padV10 leftText gros">
			<?php
			if (count($list_Users_connected) == 1)
				echo "Personne.";
			else {
				foreach ($list_Users_connected as $connUsr) {
					if ($connUsr['prenom'] != $_SESSION['user']->getUserInfos('prenom') )
					echo '<img src="./gfx/icones/users/level-'.$connUsr['level'].'.png" style="width:32px;" /> '.$connUsr['prenom'].'<br />';
				}
			}
			?>
		</p>
	</div>
	<?php if (!isset($_SESSION['plan_mod']) && !isset($_SESSION['plan_add'])) $hideRaccourcis = "style='display: none;'";  ?>
	<div id="raccourcisPlans" class="ui-state-highlight ui-corner-all petit margeTop10 marge15bot padH5" <?php echo @$hideRaccourcis; ?>  >
		<?php
		if (isset($_SESSION['plan_mod'])) {
			$planMod_RM = unserialize($_SESSION['plan_mod']);
			echo '<div id="raccourci_plan_mod">
						<div style="float:right;" class="ui-state-error ui-corner-all pad3 doigt" id="unsetPlanMod"><span class="ui-icon ui-icon-trash"></span></div>
						MODIF en cours :<br />
						<b><a href="?go=modif_plan&plan='.$planMod_RM->getPlanID().'">'.$planMod_RM->getPlanTitre() .'</a></b><br /><br />
				  </div>' ;
		}
		if (isset($_SESSION['plan_add'])) {
			$planAdd_RM = unserialize($_SESSION['plan_add']);
			echo '<div id="raccourci_plan_add">
						<div style="float:right;" class="ui-state-error ui-corner-all pad3 doigt" id="unsetPlanAdd"><span class="ui-icon ui-icon-trash"></span></div>
						AJOUT en cours :<br />
						<b><a href="?go=ajout_plan">'.$planAdd_RM->getPlanTitre() .'</a></b><br />
				  </div>' ;
		}
		?>
	</div>

	<div id="matos_sousCateg_MR" class="petit margeTop10 hide">
		<button class="bouton" id="gestionSousCatMatos"><b>GÉRER</b><br />les sous catégories</button>
	</div>


	<div id="legendeConfirmCal" class="ui-state-default ui-corner-all leftText petit margeTop10 pad10 hide">
		<div style="float:left;" class="bordFin bordSection ui-icon ui-icon-help"></div><div class="marge30l">En attente</div>
		<br />
		<div style="float:left;" class="bordFin bordSection ui-icon ui-icon-check"></div><div class="marge30l">Confirmé !</div>
	</div>

</div>

<div id="versionRobert">
	<b>Robert v<?php echo R_VERSION; ?></b><br />GNU Affero (AGLP)
</div>


<div id="dialogMyInfos" title="Modifier mes infos" class="petit hide">
	<div id="retourModUserAjax" class="ui-state-error ui-corner-all pad5 marge15bot gros hide"></div>
	<div id="infosUserDiv">
	<?php
	$info = $_SESSION['user']->getUserInfos();
	foreach ($info as $k => $v) {
		$boutonDelInfo = '';
		if ($k != 'id' && $k != 'level'  && $k != 'theme' && $k != 'idTekos' && $k != 'date_inscription' && $k != 'date_last_action' && $k != 'date_last_connexion') {
			if ($_SESSION['user']->isAdmin() === true && $k != 'email' && $k != 'nom' && $k != 'prenom')
				$boutonDelInfo = '<div class="inline nano"><button class="bouton delInfoUsers" id="del-'.$k.'"><span class="ui-icon ui-icon-minus"></span></button></div>';

			echo '<div class="inline top center marge30l blockModInfo" style="width: 175px;">
					<div class="ui-widget-header ui-corner-all pad3">'.$k.'</div>
					<input type="text" id="modUserActif-'.$k.'" size="15" value="'.$v.'" />
					'.$boutonDelInfo.'
					<br /><br />
				  </div>';
		}
	}
	?>
	</div>
	<?php
	if ($_SESSION['user']->isAdmin() === true) {
		echo '<div class="inline top marge30l" id="divAddInfoUsers" style="width: 175px;">
				 <div class="big" title="Ajouter une info">
					<button class="bouton" id="addInfoUsers"><span class="ui-icon ui-icon-plusthick"></span></button>
				 </div>
			  </div>';
	}
	?>
	<br />
	<div class="ui-state-default ui-corner-all center">
		<div class="ui-widget-header ui-corner-all">Modifier le mot de passe</div>
		<br />
		<input type="password" id="modUserActif-Pass" size="18" />
		<br />
		<span class="red petit">(laissez vide si pas de modif.)</span>
		<br />
	</div>
</div>
