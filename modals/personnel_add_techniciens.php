<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );


?>
<script src="./fct/tekos_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		
		// highlight des mini sous-menus
		$('.tekosMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#personnel_add_techniciens').addClass('ui-state-highlight');
		$('.tekosMiniSsMenu').next().children().show(300);
		
		// on cache le bouton de recherche (pas besoin ici)
		$('#chercheDiv').hide(300);
		
		// Calendrier sur focus d'input
		$(".inputCal2").datepicker({dateFormat: 'yy-mm-dd', firstDay: 1, changeMonth: true, changeYear: true});
	});
</script>


<div class="ui-widget-content ui-corner-all ajouteurPage">
	<div class="ui-widget-header ui-corner-all">Ajouter un technicien : </div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Surnom : <b class="red">*</b></div>
		<input type="text" id="newTekosSurnom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Prénom : <b class="red">*</b></div>
		<input type="text" id="newTekosPrenom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Nom : <b class="red">*</b></div>
		<input type="text" id="newTekosNom" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Date de naissance :</div>
		<input type="text" id="newTekosBD" size="20" class="inputCal2" />
		<br />
		<div class="ui-widget-header ui-corner-all">Lieu de naissance :</div>
		<input type="text" id="newTekosBP" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Catégorie : <b class="red">*</b></div>
		<select id="newTekosCateg">
			<option value="regisseur">RÉGIE</option>
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="polyvalent">POLYVALENT</option>
			<option value="roadie">ROADIE</option>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse Email : </div>
		<input class='EmailInput' type="text" id="newTekosEmail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No de Tél. : </div>
		<input type="text" class='NumericInput' id="newTekosTel" maxlength='10' size="20" />
		<br />

		<div class="ui-widget-header ui-corner-all">Intermittent ?</div>
		<input type="checkbox" id="newTekosIntermit" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse actuelle : </div>
		<input type="text" id="newTekosAdresse" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code Postal,  Ville : </div>
		<input type="text" id="newTekosCP" class="NumericInput" maxlength="5" size="4" />
		<input type="text" id="newTekosVille" size="11" />
		<br />
		<div class="ui-widget-header ui-corner-all">No Sécu : </div>
		<input type="text" id="newTekosSECU" class="NumericInput" maxlength="15"  size="15" />
	</div>
	<div class="inline top" style="width: 160px;">
		<div class="ui-widget-header ui-corner-all">No GUSO : </div>
		<input type="text" class='NumericInput' maxlength="10" id="newTekosGUSO" size="15" />
		<br />
		<div class="ui-widget-header ui-corner-all">No Congés Sp. : </div>
		<input type="text" id="newTekosCS" size="15" />
		<div class="ui-widget-header ui-corner-all">Numero Assédic : </div>
		<input type="text" id="newTekosAssedic" maxlength="8" size="12" />
		<br />
		<div class="ui-widget-header ui-corner-all">No SIRET : </div>
		<input type="text" class='NumericInput' maxlength='14' id="newTekosSIRET" size="15" />
		<br />
	</div>
	<div class="bot margeTop10">
		<button class="bouton" id="addTekos">AJOUTER</button>
	</div>
</div>
