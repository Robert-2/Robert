<?php
	if ( !isset($_SESSION["user"])) { header('Location: index.php'); }
	
	$hideForPoppy = '';
	if ( ! $_SESSION["user"]->isLevelMod() )
		$hideForPoppy = 'style="display:none;"'
?>
<script>
	var filtreRunning = false;
	var modifyingSousCat = false;
	var oldNameCat = '---';
	$(function() {
		$('#matos_sousCateg_MR').show();
		
		$('#gestionSousCatMatos').click(function(){
			$('#modalGestionSousCatMatos').dialog({
					autoOpen: true, height: 550, width: 860, modal: true,
					buttons:{'Terminé' : function() { sendNewOrderSsCat(); },
							 'Fermer'  : function() { $(this).dialog('close'); }}
			});
		});
		
		$('#modalGestionSousCatMatos').on('click', '#ajouteSousCatMatos', function() {
			var nameNewSsCat = prompt('Nom de la nouvelle sous catégorie :');
			if (nameNewSsCat == null || nameNewSsCat == undefined) return;
			var ordreMax = $('#max_ordre_ssCat').val();
			var strAjax = 'action=addSsCat&label='+nameNewSsCat+'&ordre='+ordreMax;
			AjaxJson(strAjax, 'matos_actions', alerteErr);
		});
		
		$('#modalGestionSousCatMatos').on('click', '.supprCat', function() {
			var idCat = $(this).parents('.matosSousCatItem').attr('id');
			idCat = idCat.substr(6);
			var strAjax = 'action=supprSsCat&id='+idCat;
			AjaxJson(strAjax, 'matos_actions', alerteErr);
		});
		
		$('#modalGestionSousCatMatos').on('click', '.modifCatLabel', function() { if (modifyingSousCat == false) gestionMatosSousCat($(this)); });
		
		$('#modalGestionSousCatMatos').on('blur', '.ssCatInput', function() { saveModifSsCat($(this)); });
		$('#modalGestionSousCatMatos').on('keydown', '.ssCatInput', function(event) { if (event.which == 13) saveModifSsCat($(this)); });
	});
	
	
	function sendNewOrderSsCat () {
		var newOrder = $('#sousCategList').sortable("serialize");
		var strAjax = 'action=newSsCatOrder&'+newOrder;
		AjaxJson(strAjax, 'matos_actions', alerteErr);
	}
	
	
	function gestionMatosSousCat (item) {
		modifyingSousCat = true;
		var idCat = item.parents('.matosSousCatItem').attr('id');
		idCat = idCat.substr(6);
		var nameCat = $('#nameSsCat-'+idCat).html();
		oldNameCat = nameCat;
		var longueurInput = nameCat.length;
		$('#nameSsCat-'+idCat).html('<input type="text" value="'+nameCat+'" id="inputCat-'+idCat+'" class="ssCatInput" size="'+longueurInput+'" />');
		$('#inputCat-'+idCat).focus();
	}
	
	function saveModifSsCat (input) {
		var idCat = input.parents('.matosSousCatItem').attr('id');
		idCat = idCat.substr(6);
		var newNameSsCat = input.val();
		if (newNameSsCat != oldNameCat) {
			var strAjax = 'action=modifSsCat&id='+idCat+'&newLabel='+newNameSsCat;
			AjaxJson(strAjax, 'matos_actions', alerteErr);
		}
		
		$('#nameSsCat-'+idCat).html(newNameSsCat);
		modifyingSousCat = false;
	}
	
</script>

<div class="ui-state-error ui-corner-all center top gros" id="retourAjax"></div>


<div class="miniSousMenu padV10 printHide">
	<div class="sousMenuIcon inline bouton big detailMiniSsMenu" title="MATÉRIEL AU DÉTAIL">
		<img src="gfx/icones/menu/mini-matosDetail.png" />
	</div>
	<div class="inline top leftText">
		<div class="sousMenuBtns hide" id="detailMenuBtns">
			<a class="bouton miniSmenuBtn" id="matos_list_detail" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="matos_add_detail" <?php echo $hideForPoppy; ?> href="#">Ajout</a>
		</div>
	</div>
	
	<div class="sousMenuIcon inline bouton big marge30l packsMiniSsMenu" title="PACKS DE MATÉRIEL">
		<img src="gfx/icones/menu/mini-materiel.png" />
	</div>
	<div class="inline top rightText">
		<div class="sousMenuBtns hide" id="packsMenuBtns">
			<a class="bouton miniSmenuBtn" id="matos_list_packs" href="#">Liste</a>
			<a class="bouton miniSmenuBtn" id="matos_add_packs" <?php echo $hideForPoppy; ?> href="#">Ajout</a>
		</div>
	</div>
	
	
	<div class="inline top center" style="display: none;" id="chercheDiv">
		
		<div class="inline top Vseparator bordSection"></div>
		
		<div class="inline top">
			<input type="text" id="chercheInput" size="15" />
			<br />
			<select id="filtreCherche"></select>
		</div>
		<div class="inline top nano">
			<button class="bouton chercheBtn">
				<span class="ui-icon ui-icon-search"></span>
			</button>
		</div>
		
		<div class="inline top Vseparator bordSection"></div>
		
	</div>
	
	
	<div class="inline top center" id="filtresDiv">
		<button class="bouton filtre" id="son" title="voir le matos SON"><img src="./gfx/icones/categ-son.png" alt="SON" width="30" /></button>
		<button class="bouton filtre" id="lumiere" title="voir le matos LUMIERE"><img src="./gfx/icones/categ-lumiere.png" alt="LUMIERE" width="30" /></button>
		<button class="bouton filtre" id="structure" title="voir le matos STRUCTURE"><img src="./gfx/icones/categ-structure.png" alt="STRUCTURE" width="30" /></button>
		<button class="bouton filtre" id="transport" title="voir le matos TRANSPORT"><img src="./gfx/icones/categ-transport.png" alt="TRANSPORT" width="30" /></button>
		<button class="bouton filtre" id="polyvalent" title="voir le matos POLYVALENT"><img src="./gfx/icones/categ-polyvalent.png" alt="POLYVALENT" width="30" /></button>
		
		<div class="inline top Vseparator bordSection"></div>
		
		<button class="bouton filtre" id="int-ext" title="matos INTERNE / EXTERNE au Parc"><img src="./gfx/icones/matosExterne.png" alt="INT/EXT" width="30"></button>
	</div>
	
</div>


<div id="modalGestionSousCatMatos" title="Modification de la liste des sous catégories de matériel" class="mini hide">
	<?php include('matos_gere_sous_cat.php'); ?>
</div>


<div id="matosPage" class="pageContent">
	<?php
	if ( isset($_GET['sousPage']) ) {
		$goto = $_GET['sousPage'].'.php';
	}
	else $goto = 'matos_list_detail.php';
	
	if ((@include($goto)) == false)
		echo "<div class='ui-state-error ui-corner-all enorme center pad10'>Cette page n'existe pas !</div>";
	
	?>
</div>
