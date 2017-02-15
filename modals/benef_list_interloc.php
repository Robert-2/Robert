<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();
$liste_struct = $l->getListe(TABLE_STRUCT);
if ( isset($_POST['searchingfor']) )
	 $liste_interloc = $l->getListe(TABLE_INTERLOC, '*', 'label', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
else $liste_interloc = $l->getListe(TABLE_INTERLOC);

?>
<script src="./fct/beneficiaires_Ajax.js"></script>
<script>
	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		// highlight des mini sous-menus
		$('.interlocMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#benef_list_interloc').addClass('ui-state-highlight');
		$('.interlocMiniSsMenu').next().children().show(300);
		// init du bouton de recherche
		$('.chercheBtn').attr('id', 'benef_list_interloc');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(							// Ajout des options de filtrage pour la recherche
			'<option value="label">Surnom</option>' +
			'<option value="nomPrenom">Nom ou Prenom</option>' +
			'<option value="nomStruct">Structure associée</option>' +
			'<option value="codePostal">Code Postal</option>' +
			'<option value="ville">Ville</option>' +
			'<option value="tel">No de Tel.</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche
	});
</script>


<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste des Interlocuteurs de structures bénéficiaires</div>
	<br />
	<table class="tableListe">
		<tr class="titresListe">
			<th>Surnom</th>
			<th>Nom Prénom</th>
			<th>Adresse</th>
			<th>No de Tel.</th>
			<th>email</th>
			<th>Poste</th>
			<th>Structure</th>
			<th></th>
		</tr>
		
		<?php
		if (is_array($liste_interloc)) {
			foreach ($liste_interloc as $info) {
				$infoStructName = '---';
				// récup le nom de la structure selon son id
				foreach ($liste_struct as $v ) {
					if ($info['idStructure'] == $v['id']) 
						$infoStructName = $v['label'];
				}
				if ( $_SESSION['user']->isLevelMod() ) {
					$boutonsModo = '<button class="bouton selectInterloc" id="'.$info['id'].'" nom="'.$info['label'].'" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
									<button class="bouton deleteOneInterloc" id="'.$info['id'].'" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>';
				} else $boutonsModo = '';
				// affiche le tableau des interlocuteurs
				echo '<tr class="ui-state-default interlockItem" id="'.$info['id'].'">
						<td popup="'.addslashes($info['remarque']).'">'.$info['label'].'</td>
						<td>'.$info['nomPrenom'].'</td>
						<td>'.$info['adresse'].'<br />'.$info['codePostal'].' '.$info['ville'].'</td>
						<td>'.$info['tel'].'</td>
						<td>'.$info['email'].'</td>
						<td>'.$info['poste'].'</td>
						<td>'.strtoupper($infoStructName).'</td>
						<td class="rightText printHide">
							'.$boutonsModo.'
							'//<button class="bouton printInterloc"  id="'.$info['id'].'" title="imprimer"><span class="ui-icon ui-icon-print"></span></button>
						.'</td>
					</tr>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="6">Aucun interlocuteur enregistré ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			echo '!!</td></tr>';
		}
	?></table>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier l'Interlocuteur "<span id="nomInterlocModif"></span>"</div>
	<br />
	<input type="hidden" id="modInterlocId" />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Structure associée :</div>
		<select id="modInterlocStruct" style="width:180px;">
			<option>---</option>
			<?php
			foreach ($liste_struct as $v ){
				echo '<option value="'.$v['id'].'">'.$v['label'].'</option>';
			}
			?>
		</select>
		<br />
		<div class="ui-widget-header ui-corner-all">Surnom :</div>
		<input type="text" id="modInterlocLabel" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Nom, prénom :</div>
		<input type="text" id="modInterlocNom" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse :</div>
		<input type="text" id="modInterlocAdr" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code postal :</div>
		<input type="text" id="modInterlocCP" size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville :</div>
		<input type="text" id="modInterlocVille" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input type="text" id="modInterlocMail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="modInterlocTel" size="15" />
		<br />
		<div class="ui-widget-header ui-corner-all">Poste occupé :</div>
		<input type="text" id="modInterlocPoste" size="15" />
	</div>
	<div class="inline bot">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea id="modInterlocRem" cols="20" rows="6"></textarea>
	</div>
	<div class="inline bot leftText">
		<button class="bouton closeModifieur">ANNULER</button>
		<br /><br /><br /><br /><br />
		<button class="bouton modif" id="interloc">SAUVEGARDER</button>
	</div>
</div>

<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
