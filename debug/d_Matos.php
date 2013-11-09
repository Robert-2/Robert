<?php if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}
include ('listing_from_bdd.php');

$l = new Liste();
$liste_matos = $l->getListe(TABLE_MATOS);
$liste_packs = $l->getListe(TABLE_PACKS);

?>

<script type="text/javascript" src="./fct/matos_Ajax.js"></script>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste du matériel</div>
	<br />
	<table class="debugTable leftText"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_MATOS) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '<td style="text-align:center;"><b>Action</b></td>
			</tr>';
		foreach ($liste_matos as $rec => $info) {
			echo '<tr>';
			foreach ($info as $type => $val) {
				$valOK = utf8_decode($val);
				echo "<td>$valOK</td>";
			}
			echo '<td><button class="bouton mini selectMatos" id="'.$info['ref'].'">SÉLECTIONNER</button></td>';
			echo '</tr>';
		}
	?></table>
</div>


<div id="debugAjax" class="debugSection ui-state-error pad10"></div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Matériel sélectionné : </div>
	<div class="pad5 leftText" id="afficheSel">Choisir un matériel dans la liste dessus</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Ajout de matériel</div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Libellé : <b class="red">*</b></div>
		<input type="text" id="newMatosLabel" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Référence : <b class="red">*</b></div>
		<input type="text" id="newMatosRef" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Catégorie :</div>
		<select id="newMatosCateg">
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="structure">STRUCTURE</option>
			<option value="transport">TRANSPORT</option>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Quantité totale : <b class="red">*</b></div>
		<input type="text" id="newMatosQtotale" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Quantité dispo :</div>
		<input type="text" id="newMatosQdispo" size="10" />
		<br />
		<div class="ui-widget-header ui-corner-all">Date d'achat :</div>
		<input type="text" id="newMatosDateAchat" class="inputCal" size="10" />
	</div>
	<div class="inline top" style="width: 220px;">
		<div class="ui-widget-header ui-corner-all">Tarif location : <b class="red">*</b></div>
		<input type="text" id="newMatosTarifLoc" size="10" /> €
		<br />
		<div class="ui-widget-header ui-corner-all">Valeur remplacement : <b class="red">*</b></div>
		<input type="text" id="newMatosValRemp" size="10" /> €
		<br />
		<div class="ui-widget-header ui-corner-all">Externe au parc :</div>
		<input type="checkbox" id="newMatosExterne" />
	</div>
	<div class="inline top">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea rows="6" cols="35" id="newMatosRemark"></textarea>
	</div>
	<div class="inline bot">
		<button class="bouton" id="addMatos">AJOUTER</button>
	</div>
</div>
