<?php if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}
include ('listing_from_bdd.php');

$l = new Liste();
$liste_matos = $l->getListe(TABLE_MATOS, "id, ref");
$liste_packs = $l->getListe(TABLE_PACKS);

?>

<script type="text/javascript" src="./fct/packs_Ajax.js"></script>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste des packs</div>
	<br />
	<table class="debugTable leftText"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_PACKS) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '<td style="text-align:center;"><b>Action</b></td>
			</tr>';
		foreach ($liste_packs as $rec => $info) {
			echo '<tr>';
			foreach ($info as $type => $val) {
				$valOK = utf8_decode($val);
				echo "<td>$valOK</td>";
			}
			echo '<td><button class="bouton mini selectPack" id="'.$info['ref'].'">SÉLECTIONNER</button></td>';
			echo '</tr>';
		}
	?></table>
</div>


<div id="debugAjax" class="debugSection ui-state-error pad10"></div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Pack sélectionné : </div>
	<div class="pad5 leftText" id="afficheSel">Choisir un pack dans la liste dessus</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Création d'un pack</div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Libellé : <b class="red">*</b></div>
		<input type="text" id="newPackLabel" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Référence : <b class="red">*</b></div>
		<input type="text" id="newPackRef" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Catégorie :</div>
		<select id="newPackCateg">
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="structure">STRUCTURE</option>
			<option value="transport">TRANSPORT</option>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Quantité totale : <b class="red">*</b></div>
		<input type="text" id="newPackQtotale" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Quantité dispo :</div>
		<input type="text" id="newPackQdispo" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Externe au parc :</div>
		<input type="checkbox" id="newPackExterne" />
	</div>
	<div class="inline top" style="width: 220px;">
		<div class="ui-widget-header ui-corner-all">Tarif location : <b class="red">*</b></div>
		<input type="text" id="newPackTarifLoc" size="10" /> €
		<br />
		<div class="ui-widget-header ui-corner-all">Valeur remplacement : <b class="red">*</b></div>
		<input type="text" id="newPackValRemp" size="10" /> €
	</div>
	<div class="inline top">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea rows="6" cols="35" id="newPackRemark"></textarea>
	</div>
	<div class="inline bot">
		<button class="bouton" id="addPack">AJOUTER</button>
	</div>
</div>


<div id="dialog" title="Ajout de matériel au pack" class="hide">
	<div class="inline top" style="width: 220px;">
		<br /><br />
		<div class="ui-widget-header ui-corner-all">Référence</div>
		<select id="addRefToPack">
		<?php foreach ($liste_matos as $matos)
				echo '<option value="'.$matos['id'].'">'.$matos['ref'].'</option>'; ?>
		</select>
	</div>
	<div class="inline top" style="width: 220px;">
		<br /><br />
		<div class="ui-widget-header ui-corner-all">Quantité</div>
		<input type="text" id="addQteToPack" size="4" />
	</div>
</div>

