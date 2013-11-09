<?php if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1; }
include ('listing_from_bdd.php');

$l = new Liste();
$liste_struct = $l->getListe(TABLE_STRUCT);
$liste_interloc = $l->getListe(TABLE_INTERLOC);

?>

<script type="text/javascript" src="./fct/beneficiaires_Ajax.js"></script>

<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste des structures bénéficiaires</div>
	<br />
	<table class="debugTable leftText"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_STRUCT) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '<td style="text-align:center;"><b>Action</b></td>
			</tr>';
		foreach ($liste_struct as $info) {
			echo '<tr>';
			foreach ($info as $val) {
				$valOK = utf8_decode($val);
				echo "<td>$valOK</td>";
			}
			echo '<td>
					<button class="bouton mini selectStruct" id="'.$info['id'].'">SÉLECTIONNER</button>
					<button class="bouton mini deleteStruct" id="'.$info['id'].'">SUPPRIMER</button>
				</td>';
			echo '</tr>';
		}
	?></table>
</div>

<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Structure sélectionnée : </div>
	<div class="pad5 leftText afficheurSelection" id="afficheSelStruct">Choisir dans la liste dessus</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
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
		<input type="text" id="newStrucCP" size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville : <b class="red">*</b></div>
		<input type="text" id="newStrucVille" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input type="text" id="newStrucMail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="newStrucTel" size="15" />
		<br />
		<div class="ui-widget-header ui-corner-all">No SIRET : </div>
		<input type="text" id="newStrucSIRET" size="20" />
	</div>
	<div class="inline bot">
		<button class="bouton" id="addBenef">AJOUTER</button>
	</div>
</div>


<div id="debugAjax" class="debugSection ui-state-error pad10"></div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Liste des interlocuteurs</div>
	<br />
	<table class="debugTable leftText"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_INTERLOC) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '<td style="text-align:center;"><b>Action</b></td>
			</tr>';
		foreach ($liste_interloc as $info) {
			echo '<tr>';
			foreach ($info as $val) {
				$valOK = utf8_decode($val);
				echo "<td>$valOK</td>";
			}
			echo '<td>
					<button class="bouton mini selectInterloc" id="'.$info['id'].'">SÉLECTIONNER</button>
					<button class="bouton mini deleteInterloc" id="'.$info['id'].'">SUPPRIMER</button>
				</td>';
			echo '</tr>';
		}
	?></table>
</div>

<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Interlocuteur sélectionné : </div>
	<div class="pad5 leftText afficheurSelection" id="afficheSelInterloc">Choisir dans la liste dessus</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Ajouter un interlocuteur : </div>
	<br />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Nom et Prénom : <b class="red">*</b></div>
		<input type="text" id="newInterlocNom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Surnom :</div>
		<input type="text" id="newInterlocSurnom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Structure associée :</div>
		<select id="newInterlocStruct">
			<option value="0" disabled selected>Choisir une structure</option>
			<?php foreach ($liste_struct as $structure)
				echo '<option value="'.$structure['id'].'">'.$structure['label'].'</option>';
			?>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse : <b class="red">*</b></div>
		<input type="text" id="newInterlocAdr" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code postal : <b class="red">*</b></div>
		<input type="text" id="newInterlocCP" size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville : <b class="red">*</b></div>
		<input type="text" id="newInterlocVille" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input type="text" id="newInterlocMail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="newInterlocTel" size="15" />
	</div>
	<div class="inline bot">
		<button class="bouton" id="addInterloc">AJOUTER</button>
	</div>
</div>
