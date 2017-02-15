<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();
$liste_struct = $l->getListe(TABLE_STRUCT);


?>
<script src="./fct/beneficiaires_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		// highlight des mini sous-menus
		$('.interlocMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#benef_add_interloc').addClass('ui-state-highlight');
		$('.interlocMiniSsMenu').next().children().show(300);
		// on cache le bouton de recherche (pas besoin ici)
		$('#chercheDiv').hide(300);
	});
</script>


<div class="debugSection ui-widget-content ui-corner-all ajouteurPage">
	<div class="ui-widget-header ui-corner-all">Ajouter un interlocuteur : </div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Nom et Prénom : <b class="red">*</b></div>
		<input type="text" id="newInterlocNom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Surnom :</div>
		<input type="text" id="newInterlocSurnom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Structure associée : <b class="red">*</b></div>
		<select id="newInterlocStruct">
			<option value="0" disabled selected>Choisir une structure</option>
			<?php foreach ($liste_struct as $structure)
				echo '<option value="'.$structure['id'].'">'.$structure['label'].'</option>';
			?>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse : <b class="red">*</b></div>
		<input type="text" id="newInterlocAdr" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code postal : <b class="red">*</b></div>
		<input class="NumericInput" maxlength="5" type="text" id="newInterlocCP" size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville : <b class="red">*</b></div>
		<input type="text" id="newInterlocVille" size="20" />

	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input class="EmailInput" type="text" id="newInterlocMail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="newInterlocTel" size="15" />
		<div class="ui-widget-header ui-corner-all">Poste occupé: </b></div>
		<input type="text" id="newInterlocPoste" size="20" />
	</div>
	<div class="inline bot">
		<button class="bouton" id="addInterloc">AJOUTER</button>
	</div>
</div>
