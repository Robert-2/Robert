<?php
	if ( !isset($_SESSION["user"])) { header('Location: index.php'); }
	if ( $_SESSION["user"]->isAdmin() !== true ) { header('Location: index.php'); }
	require_once('SQL_getDumpList.php');

?>

<script src="./fct/SQL_backup_Ajax.js"></script>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>

<div class="sousMenuPage">
	<div class="sousMenuIcon inline bouton big">
		<img src="gfx/icones/menu/sauvegarde.png" />
		<br />Sauvegarde
	</div>
	<div class="inline top leftText">
		<div class="sousMenuBtns center padV10 hide" id="saveBDD">
			<div class="ui-widget-header ui-corner-all pad5">Choix des données à sauvegarder (table)</div><br />
			<select id="tableList" multiple="multiple" size="7" class="moyen pad5">
				<option value="all" selected>TOUT</option>
				<?php foreach (getTableList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
			</select><br /><br />
			<button class="bouton" id="dumpSQL" >SAUVEGARDER LA SÉLECTION</button>
		</div>
	</div>
	
	<div class="sousMenuIcon inline bouton big marge30l">
		<img src="gfx/icones/menu/restoration.png" />
		<br />Restauration
	</div>
	<div class="inline top left">
		<div class="sousMenuBtns center padV10 hide" id="restoreBDD">
			<div class="ui-widget-header ui-corner-all pad5">Choix du fichier à restaurer dans la base :</div><br />
			<select id="dumpList" size="7" class="moyen pad5">
				<option disabled selected>----</option>
				<?php foreach (getDumpList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
			</select><br /><br /><br />
			<button class="bouton" id="restoreSQL" >RESTAURER LE FICHIER</button><br />
			<button class="bouton" id="downloadSQL" >TELECHARGER LE FICHIER</button><br />
		</div>
	</div>
</div>
