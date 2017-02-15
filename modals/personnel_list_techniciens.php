<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();

if ( isset($_POST['searchingfor']) )
	 $liste_tekos = $l->getListe(TABLE_TEKOS, '*', 'surnom', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
else $liste_tekos = $l->getListe(TABLE_TEKOS, '*', 'surnom');


?>
<link href="css/fileuploader.css" rel="stylesheet" type="text/css">
<script src="./js/fileuploader.js"></script>
<script src="./fct/tekos_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		
		createUploader();
		
		// highlight des mini sous-menus
		$('.tekosMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#personnel_list_techniciens').addClass('ui-state-highlight');
		$('.tekosMiniSsMenu').next().children().show(300);
		
		// init du system de recherche
		$('.chercheBtn').attr('id', 'personnel_list_techniciens');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(									// Ajout des options de filtrage pour la recherche
			'<option value="surnom">Surnom</option>' +
			'<option value="nom">Nom de famille</option>' +
			'<option value="prenom">Prénom</option>' +
			'<option value="tel">No de Tel.</option>' +
			'<option value="birthDay">Date de naissance</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche
		
		$(".inputCal2").datepicker({dateFormat: 'yy-mm-dd', firstDay: 1, changeMonth: true, changeYear: true, yearRange: '-35'});

	});
</script>


<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste des techniciens</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th>Surnom</th>
			<th>Nom Prénom</th>
			<th>Catégorie</th>
			<th>No de Tel.</th>
			<th class="leftText">email</th>
			<th>Intermittent</th>
			<th></th>
		</tr>
		
		<?php
		if (is_array($liste_tekos)) {
			foreach ($liste_tekos as $info) {
				if ($info['intermittent'] == 1)
					$intermittent = '<img src="gfx/icones/intermittent.png" popup="No GUSO : <b>'.$info['GUSO'].'</b><br />No Congés Sp. : <b>'.$info['CS'].'</b>" alt="OUI" />';
				else $intermittent = '<span popup="No SIRET : <b>'.$info['SIRET'].'</b>">NON</span>';
				
				if ( $_SESSION['user']->isAdmin() && $info['idUser'] < 1 ) {
					$boutonAddUser = '<button class="bouton createUser"  id="'.$info['id'].'" surnom="'.$info['surnom'].'" title="créer un utilisateur associé">
										<span class="ui-icon ui-icon-shuffle"></span>
									</button>';
				} else $boutonAddUser = '';
				
				if ( $_SESSION['user']->isLevelMod() ) {
					$boutonsAdmin = '<button class="bouton selectTekos" id="'.$info['id'].'" nom="'.$info['surnom'].'" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
									 <button class="bouton deleteTekos" id="'.$info['id'].'" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>';
				} else $boutonsAdmin = '';
				
				echo '<tr class="ui-state-default">
						<td class="tekSurnom">'.$info['surnom'].'</td>
						<td popup="No SECU : <b>'.$info['SECU'].'</b>" class="tekNom">'.$info['prenom'].' '.$info['nom'].'</td>
						<td><img src="gfx/icones/categ-'.$info['categorie'].'.png" /></td>
						<td>'.$info['tel'].'</td>
						<td class="leftText">'.$info['email'].'</td>
						<td>'.$intermittent.'</td>
						<td class="rightText printHide">
							'.$boutonAddUser.'
							<button class="bouton showDiplomsTekos" id="'.$info['id'].'" title="voir les diplomes"><span class="ui-icon ui-icon-folder-open"></span></button>
							<button class="bouton printTekos"  id="'.$info['id'].'" title="imprimer"><span class="ui-icon ui-icon-print"></span></button>
							'.$boutonsAdmin.'
						</td>
					</tr>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="6">Aucun technicien enregistré ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			echo '!!</td></tr>';
		}
	?></table>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier le technicien "<span id="nomTekosModif"></span>"</div>
	<br />
	<input type="hidden" id="modTekosId" />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Surnom :</div>
		<input type="text" id="modTekosSurnom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Prénom :</div>
		<input type="text" id="modTekosPrenom" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Nom :</div>
		<input type="text" id="modTekosNom" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Date de naissance :</div>
		<input type="text" id="modTekosBD" size="15" class="inputCal2" />
		<br />
		<div class="ui-widget-header ui-corner-all">Lieu de naissance :</div>
		<input type="text" id="modTekosBP" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Catégorie :</div>
		<select id="modTekosCateg">
			<option value="regisseur">RÉGIE</option>
			<option value="son">SON</option>
			<option value="lumiere">LUMIÈRE</option>
			<option value="polyvalent">POLYVALENT</option>
			<option value="roadie">ROADIE</option>
		</select>
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse Email :</div>
		<input class='EmailInput' type="text" id="modTekosEmail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No de Tel. :</div>
		<input class='NumericInput' type="text" id="modTekosTel" maxlength="10" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Intermittent :</div>
		<input type="checkbox" id="modTekosIntermit" />
		<br />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">No SECU :</div>
		<input type="text" id="modTekosSECU" maxlength='15' class='NumericInput' size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No GUSO :</div>
		<input type="text" id="modTekosGUSO" maxlength="10" class='NumericInput' size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">No Congés Spectacle :</div>
		<input type="text" id="modTekosCS" size="15" />
	</div>
	<div class="inline top" style="width: 160px;">
		<div class="ui-widget-header ui-corner-all">Numero Assédic : </div>
		<input type="text" id="modTekosAssedic" maxlength="8" size="10" /><br />
		
		<div class="ui-widget-header ui-corner-all">No SIRET :</div>
		<input type="text" class='NumericInput' maxlength='14' id="modTekosSIRET" size="15" />
		<br />
	</div>
	
	<div class="inline" style="width: 95%;">
		<div class="inline top" style="width: 200px;">
			<div class="ui-widget-header ui-corner-all">Adresse actuelle : </div>
			<input type="text" id="modTekosAdresse" size="20" />
		</div>
		<div class="inline top" style="width: 410px;">
			<div class="ui-widget-header ui-corner-all">Code Postal,  Ville : </div>
			<input type="text" class='NumericInput' maxlength='5' id="modTekosCP" size="4" />
			<input type="text" id="modTekosVille" size="11" />
		</div>
		
		<div class="inline bot" style="width: 360px;">
			<button class="bouton closeModifieur">ANNULER</button>
			<button class="bouton modif">SAUVEGARDER</button>
		</div>
	</div>
	
</div>

    
<div id='modalTekosFiles' class='hide'>
	<div class='hide' id='modalTekName'></div>
	<div class='upload'>
		
		<div id="file-uploader" style='overflow:auto;'>
			<noscript><p>Merci d'activer Javascript pour utiliser l'envoi de fichier.</p></noscript>         
		</div>
		
		<div id='file-list'></div>
		
	</div>
</div>

<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
