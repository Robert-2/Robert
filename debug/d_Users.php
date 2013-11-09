<?php
if ( !isset($_SESSION["user"])) { echo '<span>Aucune session Auth active !!</span>'; return -1;}
include ('listing_from_bdd.php');

$u = new Liste();
$liste_users = $u->getListe(TABLE_USERS);

?>

<script type="text/javascript" src="./fct/user_Ajax.js"></script>


<div id="createUser" class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Créer un utilisateur</div>
	<br />
	<div class="inline top">
		<div class="ui-widget-header ui-corner-all">Email :</div>
		<input type="text" id='cMail' value="" size="30" />
		<div class="ui-widget-header ui-corner-all">Nom :</div>
		<input type="text" id='cName' value="" size="30" />
	</div>
	<div class="inline top">
		<div class="ui-widget-header ui-corner-all">Prénom :</div>
		<input type="text" id='cPren' value="" size="30" />
		<div class="ui-widget-header ui-corner-all">Mot de passe :</div>
		<input type="text" id='cPass' value="" size="30" /><br /><br />
	</div>
	<div class="inline top">
		<button class="bouton petit" id="btncreateUser">Créer l'utilisateur</button>
	</div>
	<br />
</div>


<div id="debugAjax" class="debugSection ui-state-error"></div>


<div id="viewUser" class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all">Modif des Utilisateurs :</div>
	<br />
	<select id='nameCombo'>
		<option disabled='disabled' selected >LISTE DES USERS ENREGISTRÉS</option>
		<?php
			foreach ($liste_users as $rec => $info) {
				echo '<option value="'.$info['id'].'">'.$info['prenom'].' '.$info['nom'].'</option>';
			}
		?>
	</select>
	<button class="bouton petit" id='btnDelUser'>SUPPRIMER</button>
	
	<div class="leftText pad20" id='modifUser'>
	<?php
/*
		foreach (getNomsChamps('users') as $k => $titreChamp) {
			if ($titreChamp != 'date_inscription' && $titreChamp != 'date_last_action' && $titreChamp != 'date_last_connexion' ) {
				$lastInfo = $_SESSION["user"]->getUserInfos($titreChamp);
				echo '
				<div class="userModifier pad5">
					Modif '.$titreChamp.' : 
					<input type="text" id="'.$titreChamp.'" value="'.$lastInfo.'" />
				</div>';
			}
		}
*/
	?>
	</div>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<p class="userModifier">
		Ajout de champ / valeur : 
		<input type="text" id="newChampName" value="" /> <input type="text" id="newChampVal" value="" />
		<button class="bouton mini addChampBTN" id="updateInconnu">Ajouter</button>
</div>


<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all gros">Liste des users dans la BDD :</div>
	<br />
	<table class="debugTable"> <?php
		echo '<tr>';
		foreach (getNomsChamps(TABLE_USERS) as $k => $titreChamp) {
			echo '<td style="text-align:center;"><b>'.$titreChamp.'</b></td>';
		}
		echo '</tr>';
		foreach ($liste_users as $rec => $info) {
			echo '<tr>';
			foreach ($info as $type => $val) {
				if ($type != 'password')
					echo "<td>$val</td>";
			}
			echo '</tr>';
		}
	?></table>
</div>

<div class="debugSection ui-widget-content ui-corner-all center pad20">
	<?php echo '<pre>';
		print_r($_SESSION['user']);
		echo '</pre>';
	?>
</div>