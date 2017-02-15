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
		$('.structMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#benef_add_struct').addClass('ui-state-highlight');
		$('.structMiniSsMenu').next().children().show(300);
		// on cache le bouton de recherche (pas besoin ici)
		$('#chercheDiv').hide(300);
	});
</script>


<div class="debugSection ui-widget-content ui-corner-all ajouteurPage">
	<div class="ui-widget-header ui-corner-all">Ajouter une structure bénéficiaire : </div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">libellé : <b class="red">*</b></div>
		<input type="text" id="newStrucLabel" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Raison sociale : <b class="red">*</b></div>
		<input type="text" id="newStrucRS" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">type de structure :</div>
		<input type="text" id="newStrucType" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse : <b class="red">*</b></div>
		<input type="text" id="newStrucAdr" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code postal : <b class="red">*</b></div>
		<input type="text" id="newStrucCP" class='NumericInput' maxlength='5' size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville : <b class="red">*</b></div>
		<input type="text" id="newStrucVille" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input type="text" id="newStrucMail" class='EmailInput' size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="newStrucTel" class='NumericInput' maxlength='10' size="15" />
		<br />
		<div class="ui-widget-header ui-corner-all">No SIRET : </div>
		<input type="text" id="newStrucSIRET" class='NumericInput' maxlength="14" size="20" />
	</div>
	<div class="inline bot">
		<button class="bouton" id="addStruct">AJOUTER</button>
	</div>
</div>
