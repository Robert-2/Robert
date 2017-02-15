<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();

if ( isset($_POST['searchingfor']) ) {
	 $liste_matos = $l->getListe(TABLE_MATOS, '*', 'label', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
	 $modeRecherche = true;
}
else $liste_matos = $l->getListe(TABLE_MATOS, '*', 'label');


?>
<script>
	$(function() {
		initToolTip('.tableListe', -120);
	});
</script>


<div class="inline top" style="width: 77%;">
	<div class="marge30l center" id="filtresDetail">
		<button class="bouton filtreD marge30l" id="son" title="voir le matos SON"><img src="./gfx/icones/categ-son.png" alt="SON" width="30" /></button>
		<button class="bouton filtreD" id="lumiere" title="voir le matos LUMIERE"><img src="./gfx/icones/categ-lumiere.png" alt="LUMIERE" width="30" /></button>
		<button class="bouton filtreD" id="structure" title="voir le matos STRUCTURE"><img src="./gfx/icones/categ-structure.png" alt="STRUCTURE" width="30" /></button>
		<button class="bouton filtreD" id="transport" title="voir le matos TRANSPORT"><img src="./gfx/icones/categ-transport.png" alt="TRANSPORT" width="30" /></button>
		
		<div class="inline top Vseparator bordSection"></div>
		
		<button class="bouton filtreD" id="int-ext" title="ne voir que le matos EXTERNE au Parc"><img src="./gfx/icones/matosExterne.png" alt="INT/EXT" width="30"></button>
	</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th>Référence</th>
			<th>Désignation complète</th>
			<th>Catégorie</th>
			<th>Tarif loc.</th>
			<th>Qté Parc</th>
			<th>J'en veux</th>
			<th>Ajouter</th>
		</tr>
		
		<?php
		if (is_array($liste_matos)) {
			foreach ($liste_matos as $info) {
				
				$externeClass = ''; $externeTxt = '';
				if ($info['externe'] == '1') {
					$externeClass = 'ui-state-active';
					$externeTxt = ' (ext)';
				}
				
				echo '<tr class="ui-state-default matosLine matosInterne '.$externeClass.' cat-'.$info['categorie'].'">
						<td>'.$info['ref'].'</td>
						<td>'.$info['label'].'</td>
						<td><img src="./gfx/icones/categ-'.$info['categorie'].'.png" alt="'.$info['categorie'].'" /></td>
						<td>'.$info['tarifLoc'].' &euro;</td>
						<td>'.$info['Qtotale'].$externeTxt.'</td>
						<td><input type="text" class="inputQteAdd" id="qteAdd-'.$info['id'].'" size="3" /></td>
						<td class="rightText" style="width:100px;">
							<button class="bouton addMatosToPack" id="'.$info['id'].'" ref="'.$info['ref'].'" title="ajouter"><span class="ui-icon ui-icon-plus"></span></button>
							<button class="bouton decMatosToPack" id="'.$info['id'].'" ref="'.$info['ref'].'" title="enlever"><span class="ui-icon ui-icon-minus"></span></button>
						</td>
					</tr>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="7">Aucun matériel enregistré ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			echo '!!</td></tr>';
		}
	?></table>
	<br />
</div>

<div class="inline top ui-widget-content ui-corner-all" style="position: absolute; right: 5px; width: 22%; height: 95%; box-shadow: inset 0 0 8px #666666;">
	<div class="ui-widget-header ui-corner-all center">CONTENU DU PACK</div>
	<div class="pad10 packContent">
		<div class="packVideHelp">
			<p class="ui-state-disabled">Pack vide !</p>
			<p class="ui-state-disabled">
				Cliquez sur le bouton <button class="bouton"><span class="ui-icon ui-icon-arrowthickstop-1-s"></span></button> pour ajouter 1, ou bien
				entrez une quantité, puis cliquez sur le bouton <button class="bouton"><span class="ui-icon ui-icon-arrowthickstop-1-s"></span></button>
			</p>
		</div>
	</div>
</div>