<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();
$liste_tekos = $l->getListe(TABLE_TEKOS, '*', 'surnom');

if ( isset($_POST['searchingfor']) )
	 $liste_Users = $l->getListe(TABLE_USERS, '*', 'date_last_action', 'DESC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
else $liste_Users = $l->getListe(TABLE_USERS, '*', 'date_last_action', 'DESC');

if ( $_SESSION["user"]->isAdmin() !== true ) die('Vous n\'avez pas accès à cette page.');

?>
<script src="./fct/user_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		
		// highlight des mini sous-menus
		$('.usersMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#personnel_list_utilisateurs').addClass('ui-state-highlight');
		$('.usersMiniSsMenu').next().children().show(300);
		
		// init du system de recherche
		$('.chercheBtn').attr('id', 'personnel_list_utilisateurs');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(									// Ajout des options de filtrage pour la recherche
			'<option value="email">Email</option>'+
			'<option value="nom">Nom de famille</option>' +
			'<option value="prenom">Prénom</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche

	});
</script>


<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste des utilisateurs</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th>email</th>
			<th>Nom Prénom</th>
			<th>Niveau</th>
			<th>Technicien associé</th>
			<th></th>
		</tr>
		
		<?php
		if (is_array($liste_Users)) {
			foreach ($liste_Users as $info) {
				$tekos = ' --- '; $tekosInfo = array();
				if (is_array($liste_tekos)) {
					foreach ($liste_tekos as $tekosInfo)
						if ($tekosInfo['idUser'] == $info['id'])
							$tekos = $tekosInfo['surnom'].' <img src="gfx/icones/categ-'.$tekosInfo['categorie'].'.png" style="width:20px; float:right;" />' ;
				}
				$popupUserInfos = '';
				foreach ($info as $k => $v) {
					if ($k != 'id' && $k != 'email' && $k != 'prenom' && $k != 'nom' && $k != 'level' && $k != 'idTekos' && $k != 'date_inscription' && $k != 'date_last_action' && $k != 'date_last_connexion')
						$popupUserInfos .= "<li>$k : <b>$v</b></li>";
					if ($k == 'date_last_action') {
						($v == 0) ? $dateLastAction = 'Jamais' :	$dateLastAction = date('d/m/Y à H\hi', $v);
						$popupUserInfos .= "<li>Vu la dernière fois le :<br /><b>$dateLastAction</b></li>";
					}
				}
				switch ($info['level']) {
					case '1':
						$levelTxt = 'Consultant';
						break;
					case '5':
						$levelTxt = 'Utilisateur';
						break;
					case '7':
						$levelTxt = 'Administrateur';
						break;
					case '9':
						$levelTxt = 'Développeur';
						break;
				}
				echo '<tr class="ui-state-default">
						<td>'.$info['email'].'</td>
						<td popup="'.$popupUserInfos.'">'.$info['prenom'].' '.$info['nom'].'</td>
						<td><img src="gfx/icones/users/level-'.$info['level'].'.png" alt="'.$levelTxt.'" popup="'.$levelTxt.'" /></td>
						<td width="130">'.$tekos.'</td>
						<td class="rightText">
							<button class="bouton selectUser" id="'.$info['id'].'" nom="'.$info['prenom'].'" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
							<button class="bouton deleteUser" id="'.$info['id'].'" nom="'.$info['prenom'].'" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>
						</td>
					</tr>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="6">Aucun utilisateur enregistré ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			else echo '!! Comment c\'est possible ??? ';
			echo '!!</td></tr>';
		}
	?></table>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier l'utilisateur "<span id="nomUserModif"></span>"</div>
	<br />
	<input type="hidden" id="modUserId" />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse Email :</div>
		<input type="text" id="modUserEmail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Mot de passe :</div>
		<input type="text" id="modUserPass" size="20" title="Laissez vide si pas de modif." />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Prénom :</div>
		<input type="text" id="modUserPrenom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Nom :</div>
		<input type="text" id="modUserNom" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Niveau d'accès :</div>
		<select id="modUserLevel" style="width: 150px;">
			<option value="1">Consultant</option>
			<option value="5">Utilisateur</option>
			<option value="7">Administrateur</option>
		</select>
		<br />
		<div class="ui-widget-header ui-corner-all">Technicien associé :</div>
		<select id="modUserTekos" style="width: 150px;">
			<option value="0"> ---- </option>
			<?php 
			foreach ($liste_tekos as $tekos) {
				($tekos['idUser'] == 0) ? $disable = '' : $disable = 'disabled';
				echo '<option value="'.$tekos['id'].'" '.$disable.'>'.$tekos['surnom'].'</option>';
			}
			?>
		</select>
	</div>
	<div class="inline bot leftText">
		<button class="bouton closeModifieur">ANNULER</button>
		<br /><br />
		<button class="bouton modif">SAUVEGARDER</button>
	</div>
</div>

<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
