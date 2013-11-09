<?php if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}
include ('listing_from_bdd.php');

$l = new Liste();
$liste_tekos = $l->getListe(TABLE_TEKOS);

try {	$testTekos = new Tekos('3');
		$infosTestTekos = $testTekos->getTekosInfos(); }
catch (Exception $e) { echo $e->getMessage(); }

?>


<script type="text/javascript" src="./fct/tekos_Ajax.js"></script>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste des techniciens</div>
	<br />
	<table class="debugTable leftText"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_TEKOS) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '<td style="text-align:center;"><b>Action</b></td>
			</tr>';
		foreach ($liste_tekos as $info) {
			echo '<tr>';
			foreach ($info as $val) {
				$valOK = utf8_decode($val);
				echo "<td>$valOK</td>";
			}
			echo '<td><button class="bouton mini selectTekos" id="'.$info['id'].'">SÉLECTIONNER</button></td>';
			echo '</tr>';
		}
	?></table>
</div>


<div id="debugAjax" class="debugSection ui-state-error pad10"></div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Technicien sélectionné : </div>
	<div class="pad5 leftText" id="afficheSel">Choisir un technicien dans la liste dessus</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Ajouter un technicien : </div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">surnom : <b class="red">*</b></div>
		<input type="text" id="newTekosSurnom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No GUSO : </div>
		<input type="text" id="newTekosGUSO" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No Congés Spectacle : </div>
		<input type="text" id="newTekosCS" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Date de naissance :</div>
		<input type="text" id="newTekosBD" size="20" class="inputCal" />
		<br />
		<div class="ui-widget-header ui-corner-all">Lieu de naissance :</div>
		<input type="text" id="newTekosBP" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Catégorie : <b class="red">*</b></div>
		<select id="newTekosCateg">
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="polyvalent">POLYVALENT</option>
			<option value="roadie">ROADIE</option>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">No Sécu : </div>
		<input type="text" id="newTekosSECU" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No SIRET : </div>
		<input type="text" id="newTekosSIRET" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Intermittent ?</div>
		<input type="checkbox" id="newTekosIntermit" />
	</div>
	<div class="inline bot">
		<button class="bouton" id="addTekos">AJOUTER</button>
	</div>
</div>
