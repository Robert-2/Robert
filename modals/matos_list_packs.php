<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();

$liste_matos = $l->getListe(TABLE_MATOS, 'id, ref, externe, Qtotale, tarifLoc', 'id');

if ( isset($_POST['searchingfor']) ) {
	 $liste_packs = $l->getListe(TABLE_PACKS, '*', 'ref', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
	 $modeRecherche = true;
}
else $liste_packs = $l->getListe(TABLE_PACKS, '*', 'ref');


?>
<script>
	var listDetailPack = {};
	var refMatos = {};
	<?php foreach($liste_matos as $matos) echo 'refMatos["'.$matos['id'].'"] = "'.$matos['ref'].'" ;'."\n" ; ?>
</script>
<script src="./fct/packs_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		
		// highlight des mini sous-menus
		$('.packsMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#matos_list_packs').addClass('ui-state-highlight');
		$('.packsMiniSsMenu').next().children().show(300);
		
		// init du system de recherche
		$('.chercheBtn').attr('id', 'matos_list_packs');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(							// Ajout des options de filtrage pour la recherche
			'<option value="label">Désignation</option>' +
			'<option value="ref">Référence</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche
		$('#filtresDiv').show(300);							// affiche le module des filtres
		$('#polyvalent').show();							// montre le filtre 'polyvalent' (si on viens du matos détail qui le cache)
		$('.filtre').removeClass('ui-state-error');
		
		$(".inputCal2").datepicker({dateFormat: 'yy-mm-dd', firstDay: 1, changeMonth: true, changeYear: true});
	});
</script>


<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste des packs de matériel</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th width="150">Référence</th>
			<th>Désignation complète</th>
			<th>Catégorie</th>
			<th>Tarif loc.</th>
			<th>Qté possible en Parc</th>
		</tr>
		
		<?php
		if (is_array($liste_packs)) {
			foreach ($liste_packs as $info) {
				if ( $_SESSION['user']->isLevelMod() ) {
					$boutonsModo = '<button class="bouton selectPack" id="'.$info['id'].'" nom="'.$info['ref'].'" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
									<button class="bouton deletePack" id="'.$info['id'].'" nom="'.$info['ref'].'" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>';
				} else $boutonsModo = '';
				
				$popupExterne = '';
				$hideExterne = 'matosInterne';
				if ($info['externe'] == '1') {
					$popupExterne = ' class="ui-state-error" popup="EXTERNE AU PARC"';
					$hideExterne = 'matosExterne ';
					if (@$modeRecherche != true) $hideExterne .= 'hide';
					else $hideExterne .= 'ui-state-active';
				}
				
				$pack = new Pack ('id', $info['id']);
				$qteTotale = $pack->getPackInfos('Qtotale');
				($qteTotale < 1) ? $classQteOK = 'ui-state-error' : $classQteOK = '';
				($qteTotale > 1) ? $pluriel = 's' : $pluriel = '';
				$tarifLoc = number_format($pack->getTarifPack(), 2);
				
				echo '<tr class="ui-state-default matosLine '.$classQteOK.' '.$hideExterne.' cat-'.$info['categorie'].'">
						<td>'.$info['ref'].'</td>
						<td popup="'.addslashes($info['remarque']).'">'.$info['label'].'</td>
						<td><img src="./gfx/icones/categ-'.$info['categorie'].'.png" alt="'.$info['categorie'].'" /></td>
						<td>'.$tarifLoc.' &euro;</td>
						<td id="qtePack-'.$info['id'].'" '.$popupExterne.'>'.$qteTotale.' pack'.$pluriel.'</td>
						<td class="rightText">
							<button class="bouton showPDtr" id="'.$info['id'].'" title="Afficher le détail du pack"><span class="ui-icon ui-icon-search"></span></button>
							'.$boutonsModo.'
							<button class="bouton printMatos"  id="'.$info['id'].'" title="imprimer"><span class="ui-icon ui-icon-print"></span></button>
						</td>
					</tr>
					<tr class="shadowIn center pDetail hide" id="packDetailTR-'.$info['id'].'">
						<td valign="top" class="pad20"><br />Detail de <b>"'.$info['ref'].'"</b></td>
						<td colspan="6" class="leftText pad20"><br />';
					$details = json_decode($info['detail'], true);
					foreach ($details as $idM => $qteNeed) {
						foreach ($liste_matos as $matos) {
							if ($matos['id'] == $idM) {
								$refM = $matos['ref'];
								$qteParc = $matos['Qtotale'];
								$ssTarifLoc = $qteNeed * $matos['tarifLoc'];
								$ou = ($matos['externe'] == '1') ? 'externe au parc': 'en parc';
							}
						}
						($qteNeed > $qteParc) ? $ok = 'insufisant !' : $ok = 'OK.';
						($qteNeed > $qteParc) ? $classOk = 'ui-state-error' : $classOk = '';
						echo "<div class='inline padV10 $classOk' style='width:20%;'>$qteNeed x $refM <i class='mini'>($ssTarifLoc €)</i></div><div class='inline padV10 $classOk'>$qteParc $ou, $ok</div><br />";
					}
					echo '<br /></td>
					</tr>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="7">Aucun pack enregistré ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			echo '!!</td></tr>';
		}
	?></table>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier le pack "<span id="nomPackModif"></span>"</div>
	<div class="leftText marge30l margeTop5">
		<input type="hidden" id="modPackId" />
		<div class="inline top center pad3">
			<div class="ui-widget-header ui-corner-all">Référence :</div>
			<input type="text" id="modPackRef" size="15" />
		</div>
		<div class="inline top center pad3">
			<div class="ui-widget-header ui-corner-all">Désignation complète :</div>
			<input type="text" id="modPackLabel" size="65" />
		</div>
		<div class="inline top center pad3">
			<button class="bouton" id="showPackContent">CONTENU DU PACK</button>
		</div>
		<br />
		<div class="inline top center pad3" style="width: 140px;">
			<div class="ui-widget-header ui-corner-all">Catégorie :</div>
			<select id="modPackCateg">
				<option value="son">SON</option>
				<option value="lumiere">LUMIÈRE</option>
				<option value="structure">STRUCTURE</option>
				<option value="transport">TRANSPORT</option>
				<option value="polyvalent">POLYVALENT</option>
			</select>
			<br />
			<div class="ui-widget-header ui-corner-all">Qté Parc :</div>
			<div id="modPackQteTot" class="pad5 NumericInput" title="dépend du matériel au détail disponible dans le parc">0</div>
		</div>
		<div class="inline top center pad3" style="width: 120px;">
			<div class="ui-widget-header ui-corner-all">Externe ?</div>
			<input type="checkbox" id="modPackExterne" />
			<br />
			<div class="ui-widget-header ui-corner-all margeTop5">Tarif loc. :</div>
			<div id="modPackTarif" class="pad5 NumericInput" title="Calculé automatiquement"></div>
		</div>
		<div class="inline top center pad3">
			<div class="ui-widget-header ui-corner-all">Remarque :</div>
			<textarea id="modPackRem" cols="50" rows="4"></textarea>
		</div>
		<div class="inline top leftText pad10">
			<button class="bouton closeModifieur">ANNULER</button>
			<br /><br />
			<button class="bouton modif" id="Pack">SAUVEGARDER</button>
		</div>
	</div>
</div>

<div class="petit hide" id="DialogDetailPack">
	<?php include('pack_add_detail.php') ?>
</div>


<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
