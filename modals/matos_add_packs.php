<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

?>
<script>
	var listDetailPack = {};
</script>
<script src="./fct/packs_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		
		// highlight des mini sous-menus
		$('.packsMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#matos_add_packs').addClass('ui-state-highlight');
		$('.packsMiniSsMenu').next().children().show(300);
		
		// on cache le bouton de recherche et les filtres (pas besoin ici)
		$('#chercheDiv').hide(300);
		$('#filtresDiv').hide(300);
		
	});
</script>


<div class="ui-widget-content ui-corner-all leftText ajouteurPage">
	<div class="ui-widget-header ui-corner-all center big">Ajout de pack de matériel</div>
	<br />
	<div class="inline top center pad3" style="width: 180px;">
		<div class="ui-widget-header ui-corner-all">Référence : <b class="red">*</b></div>
		<input type="text" id="newPackRef" size="18" />
	</div>
	<div class="inline top center pad3" style="width: 800px;">
		<div class="ui-widget-header ui-corner-all">Désignation complète : <b class="red">*</b></div>
		<input type="text" id="newPackLabel" size="95" />
	</div>
	<br /><br />
	<div class="inline top ui-corner-all" style="width: 810px; box-shadow: inset 0 0 8px #666666;">
		<div class="ui-widget-header ui-corner-all center">Aperçu du contenu du pack :</div>
		<div class="pad10 packContent petit">
			<div class="packVideHelp">
				<p class="ui-state-disabled">Pack vide !</p>
				<p class="ui-state-disabled">
					Cliquez sur le bouton "CONTENU DU PACK" pour ajouter ou modifier la liste du matériel que contiendra ce pack.
				</p>
			</div>
		</div>
	</div>
	<div class="inline bot rightText" style="width: 180px;">
		<button class="bouton" id="addDetail">CONTENU DU PACK</button>
	</div>
	<br /><br />
	<div class="inline top center pad3" style="width: 180px;">
		<div class="ui-widget-header ui-corner-all">Catégorie : <b class="red">*</b></div>
		<select id="newPackCateg">
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="structure">STRUCTURE</option>
			<option value="transport">TRANSPORT</option>
			<option value="polyvalent">POLYVALENT</option>
		</select>
		<br />
		<div class="ui-widget-header ui-corner-all margeTop5">Externe ?</div>
		<input type="checkbox" id="newPackExterne" />
	</div>
<!--	<div class="inline top center pad3" style="width: 180px;">
		<div class="ui-widget-header ui-corner-all">Tarif loc. : <b class="red">*</b></div>
		<input class="NumericInput" type="text" id="newPackTarifLoc" size="6" /> €
		<br />
		<div class="ui-widget-header ui-corner-all margeTop5">Val. Remp. : <b class="red">*</b></div>
		<input class="NumericInput" type="text" id="newPackValRemp" size="8" /> €
	</div>-->
	<div class="inline top center pad3" style="width: 600px;">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea id="newPackRemark" cols="60" rows="4"></textarea>
	</div>
</div>

<div class=" margeTop10 center big">
	<button class="bouton" id="addPack">AJOUTER LE PACK</button>
</div>

<div id="debugPack"></div>


<div class="petit hide" id="Dialog">
	<?php include('pack_add_detail.php'); ?>
</div>

