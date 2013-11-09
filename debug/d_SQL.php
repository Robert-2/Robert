<?php
if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}
include ('listing_from_bdd.php');
require_once('SQL_getDumpList.php');
?>

<script type="text/javascript" src="./fct/SQL_backup_Ajax.js"></script>

<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">SAVE / BACKUP BASE DE DONNÉES :</div>
	<br />
	<div class="inline top">
		<div class="ui-widget-header ui-corner-all pad5">Sauvegarde de(s) table(s) :</div>
		<br />
		<select id="tableList" multiple="multiple" class="moyen pad5">
			<option value="all" selected>TOUT</option>
			<?php foreach (getTableList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
		</select><br /><br />
		<button class="bouton" id="dumpSQL" >BACKUP BDD</button><br />
	</div>
	
	<div class="inline top marge30l">
		<div class="ui-widget-header ui-corner-all pad5">Récupération de fichier :</div>
		<br /><br />
		<select id="dumpList" class="pad5">
			<option disabled selected>----</option>
			<?php foreach (getDumpList() as $name) {echo '<option value="'.$name.'">'.$name.'</option>';} ?>
		</select><br /><br /><br />
		<button class="bouton" id="restoreSQL" >RESTORE BDD</button><br />
	</div>
</div>

<div id="debugAjax" class="debugSection ui-state-error"></div>