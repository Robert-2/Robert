<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();

if ( isset($_POST['searchingfor']) ) {
	 $liste_matos = $l->getListe(TABLE_MATOS, '*', 'ref', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
	 $modeRecherche = true;
}
else $liste_matos = $l->getListe(TABLE_MATOS, '*', 'ref');
unset($l);

$lm = new Liste();
$liste_ssCat = $lm->getListe(TABLE_MATOS_CATEG, '*', 'ordre', 'ASC');


?>
<script src="./fct/matos_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);

		// highlight des mini sous-menus
		$('.detailMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#matos_list_detail').addClass('ui-state-highlight');
		$('.detailMiniSsMenu').next().children().show(300);

		// init du system de recherche
		$('.chercheBtn').attr('id', 'matos_list_detail');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(							// Ajout des options de filtrage pour la recherche
			'<option value="label">Désignation</option>' +
			'<option value="ref">Référence</option>' +
			'<option value="dateAchat">Année d\'achat</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche
		$('#filtresDiv').show(300);							// affiche le module des filtres
		$('#polyvalent').hide();							// sauf le 'polyvalent' (existe que pour les packs)
		$('.filtre').removeClass('ui-state-error');

		$(".inputCal2").datepicker({dateFormat: 'yy-mm-dd', firstDay: 1, changeMonth: true, changeYear: true});
	});
</script>


<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste du matériel au détail</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th class="ui-state-disabled">Référence</th>
			<th class="ui-state-disabled">Désignation complète</th>
			<th class="ui-state-disabled">Catégorie</th>
			<th class="ui-state-disabled">Tarif loc.</th>
			<th class="ui-state-disabled">Val. Remp.</th>
			<th class="ui-state-disabled">Qté Parc</th>
			<th class="ui-state-disabled">En panne</th>
			<th class="ui-state-disabled">Actions</th>
		</tr>

		<?php
		include('matos_tri_sousCat.php');

		$matos_by_categ = creerSousCatArray($liste_matos);
		$categById		= simplifySousCatArray($liste_ssCat);

		if (is_array($matos_by_categ)) {
			foreach ($categById as $catInfo) {
				$index = $catInfo['id'];
				if (!is_array(@$matos_by_categ[$index])) continue;		// n'affiche rien si la sous catégorie est vide !
				echo '<tr class="ui-state-hover sousCategLine"><td colspan="8" class="leftText gros gras" style="padding-left:20px;">'.$catInfo['label'].'</td></tr>';
				foreach ($matos_by_categ[$index] as $info) {
					if ( $_SESSION['user']->isLevelMod() ) {
						$boutonsModo = '<button class="bouton selectMatos" id="'.$info['id'].'" nom="'.$info['ref'].'" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
										<button class="bouton deleteMatos" id="'.$info['id'].'" nom="'.$info['ref'].'" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>';
					} else $boutonsModo = '';

					$popupExterne = '';
					$hideExterne = 'matosInterne';
					if ($info['externe'] == '1') {
						$popupExterne = ' class="ui-state-error" popup="EXTERNE AU PARC !<br /><br />A louer chez : <b>'.$info['ownerExt'].'</b>"';
						$hideExterne = 'matosExterne ';
						if (@$modeRecherche != true) $hideExterne .= 'hide';
						else $hideExterne .= 'ui-state-active';
					}

					$remark = addslashes($info['remarque']);
					$remark = preg_replace('/\\n/', '<br />', $remark);

					$qteDispo = $info['panne'] ;

					$popupPanne = '';
					if ($info['panne'] >= 1) $popupPanne = 'ui-state-error';

					echo '<tr class="ui-state-default matosLine '.$hideExterne.' cat-'.$info['categorie'].'">
							<td>'.$info['ref'].'</td>
							<td popup="'.$remark.'">'.$info['label'].'</td>
							<td><img src="./gfx/icones/categ-'.$info['categorie'].'.png" alt="'.$info['categorie'].'" /></td>
							<td>'.$info['tarifLoc'].' &euro;</td>
							<td>'.$info['valRemp'].' &euro;</td>
							<td'.$popupExterne.'>'.$info['Qtotale'].'</td>
							<td class="'.$popupPanne.'">'.$qteDispo.'</td>
							<td class="rightText printHide">
								'.$boutonsModo.'
								<!--<button class="bouton printMatos"  id="'.$info['id'].'" title="imprimer"><span class="ui-icon ui-icon-print"></span></button>-->
							</td>
						</tr>';
				}
			}
		}
	?>
</table>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier le matériel "<span id="nomMatosModif"></span>"</div>
	<div class="leftText marge30l margeTop5">
		<input type="hidden" id="modMatosId" />
		<div class="inline top center pad3" style="width: 140px;">
			<div class="ui-widget-header ui-corner-all">Référence :</div>
			<input type="text" id="modMatosRef" size="15" />
		</div>
		<div class="inline top center pad3" style="width: 600px;">
			<div class="ui-widget-header ui-corner-all">Désignation complète :</div>
			<input type="text" id="modMatosLabel" size="72" />
		</div>
		<br />
		<div class="inline top center pad3" style="width: 120px;">
			<div class="ui-widget-header ui-corner-all">Catégorie :</div>
			<select id="modMatosCateg">
				<option value="son">SON</option>
				<option value="lumiere">LUMIÈRE</option>
				<option value="structure">STRUCTURE</option>
				<option value="transport">TRANSPORT</option>
			</select>
		</div>
		<div class="inline top center pad3" style="width: 190px;">
			<div class="ui-widget-header ui-corner-all">Sous Categ :</div>
			<select id="modMatosSousCateg">
				<option value="0">---</option>
				<?php
				foreach ($liste_ssCat as $ssCat) {
					echo '<option value="'.$ssCat['id'].'">'.$ssCat['label'].'</option>';
				}
				?>
			</select>
		</div>
		<div class="inline top center pad3" style="width: 105px;">
			<div class="ui-widget-header ui-corner-all">Tarif loc.</div>
			<input class="NumericInput" type="text" id="modMatosTarif" size="5" /> €
		</div>
		<div class="inline top center pad3" style="width: 105px;">
			<div class="ui-widget-header ui-corner-all">Val. Remp.</div>
			<input class="NumericInput" type="text" id="modMatosValRemp" size="6" /> €
		</div>
		<div class="inline top center pad3" style="width: 90px;">
			<div class="ui-widget-header ui-corner-all">Qté Parc</div>
			<input class="NumericInput" type="text" id="modMatosQteTot" size="6" />
		</div>
		<div class="inline top center pad3" style="width: 90px;">
			<div class="ui-widget-header ui-corner-all">En panne</div>
			<input class="NumericInput" type="text" id="modMatosPanne" size="6" />
		</div>
		<br />
		<div class="inline top center pad3" style="width: 480px;">
			<div class="ui-widget-header ui-corner-all">Remarque :</div>
			<textarea id="modMatosRem" cols="55" rows="5"></textarea>
		</div>
		<div class="inline top center pad3" style="width: 130px;">
			<div class="ui-widget-header ui-corner-all">Externe ?</div>
			<input type="checkbox" id="modMatosExterne" class="externeBox" />
		</div>
		<div class="inline top center pad3" style="width: 120px;">
			<div id="dateAchatDiv">
				<div class="ui-widget-header ui-corner-all">Acheté le :</div>
				<input type="text" id="modMatosDateAchat" class="inputCal2" size="9" />
			</div>
			<div id="chezQuiDiv" class="hide">
				<div class="ui-widget-header ui-corner-all">A louer chez :</div>
				<input type="text" id="modMatosExtOwner" size="9" />
			</div>
		</div>
		<div class="inline top leftText pad10">
			<button class="bouton closeModifieur">ANNULER</button>
			<br /><br />
			<button class="bouton modif" id="matos">SAUVEGARDER</button>
		</div>
	</div>
</div>

<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
