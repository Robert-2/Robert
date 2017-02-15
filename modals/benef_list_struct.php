<?php
if (session_id() == '') session_start();
require_once ('initInclude.php');
require_once ('common.inc.php');		// OBLIGATOIRE pour les sessions, à placer TOUJOURS EN HAUT du code !!
require_once ('checkConnect.php' );

$l = new Liste();

if ( isset($_POST['searchingfor']) )
	 $liste_struct = $l->getListe(TABLE_STRUCT, '*', 'label', 'ASC', $_POST['searchingwhat'], 'LIKE', '%'.$_POST['searchingfor'].'%');
else $liste_struct = $l->getListe(TABLE_STRUCT, '*', 'label');

if (isset($_GET['affiche']))
	$liste_struct = $l->getListe(TABLE_STRUCT, '*', 'label', 'ASC', 'label', 'LIKE', $_GET['affiche']);

$l = null ; $l = new Liste();
$listeInterlocks = $l->getListe ( TABLE_INTERLOC, '*', 'label' );
?>
<script src="./fct/beneficiaires_Ajax.js"></script>
<script>

	cssFile = '<?php echo chooseThemeFolder(); ?>';

	$(function() {
		$('.bouton').button();
		initToolTip('.tableListe', -120);
		
		// highlight des mini sous-menus
		$('.structMiniSsMenu').addClass('ui-state-highlight');
		$('.miniSmenuBtn').removeClass('ui-state-highlight');
		$('#benef_list_struct').addClass('ui-state-highlight');
		$('.structMiniSsMenu').next().children().show(300);
		
		// init du system de recherche
		$('.chercheBtn').attr('id', 'benef_list_struct');	// ajoute le nom du fichier actuel (en id du bouton) pour la recherche
		$('#filtreCherche').html(							// Ajout des options de filtrage pour la recherche
			'<option value="label">Nom Court</option>' +
			'<option value="codePostal">Code Postal</option>' +
			'<option value="ville">Ville</option>' +
			'<option value="tel">No de Tel.</option>' +
			'<option value="SIRET">No de SIRET</option>'
		);
		$('#chercheInput').val('');							// vide l'input de recherche
		$('#chercheDiv').show(300);							// affiche le module de recherche
	});
</script>

<div class="ui-widget-content ui-corner-all" id="listingPage">
	<div class="ui-widget-header ui-corner-all gros center pad3">Liste des structures bénéficiaires</div>
	<br />
	<div id='Strucs'>
	<?php
		if (is_array($liste_struct)) {
			foreach ($liste_struct as $info) {
				$added = false ;
				if ( $_SESSION['user']->isLevelMod() ) {
					$boutonsModo = '<button class="bouton selectStruct hidePreview printHide" title="modifier"><span class="ui-icon ui-icon-pencil"></span></button>
									<button class="bouton deleteStruct hidePreview printHide" title="supprimer"><span class="ui-icon ui-icon-trash"></span></button>';

					$boutonsModoInterlock ="<div class='inline ui-state-default pad3 ui-corner-all marge10l doigt' style='box-shadow: 1px 1px 4px #888888;'>
												<span class='ui-icon ui-icon-pencil btnAddInterlock modifInterlock hidePreview printHide' title='modifier'></span>
											</div>
											<div class='inline ui-state-default pad3 ui-corner-all marge10l doigt' style='box-shadow: 1px 1px 4px #888888;'>
												<span class='ui-icon ui-icon-trash deleteInterlock hidePreview printHide' title='supprimer'></span>
											</div>";
				} else {
					$boutonsModo = '';
					$boutonsModoInterlock = '';
				}

				echo '<div class="structItem doigt" id="' .$info['id']. '" title="clic pour voir les interlocuteurs">
							<div class="structInfos ui-state-default ui-corner-top pad10">
								<div class="info" style="width:25%;">
									<span class="hide structID">' .$info['id']. '</span>
									<span>' .$info['type']. '</span>
									<span class="enorme structLabel" >' .$info['label'].   '</span><br />
									<span class="structAdress">'		.$info['adresse']. '</span><br />
									<span class="structcPostal">' .$info['codePostal']. '</span>
									<span class="structVille">'   .$info['ville'].		'</span>  
								</div>
								<div class="info" style="width:25%;">
									' .$info['tel']. '<br />
									' .$info['email'].'
								</div>
								<div class="info" style="width:20%;">
									no de SIRET <br />
									' .$info['SIRET']. '
								</div>
								<div class="info rightText" style="width:20%;">
									<button class="bouton printStruct hidePreview" title="Imprimer"><span class="ui-icon ui-icon-print"></span></button>
									' . $boutonsModo . '
								</div>
							</div>
							
							<div class="structInterlock pad10 shadowIn ui-corner-bottom" id="struct-' .$info['id']. '">';
						if ( $_SESSION['user']->isLevelMod() ) 
							echo '<div class="inline tiers mid">
									<button class="btnAddInterlock bouton hidePreview" title="Ajouter un interlocuteur">
										<span class="gros">AJOUTER interlocuteur</span>
									</button>
									<span class="marge30l bouton hidePreview" title="Envoyer un email à cette structure"><a href="mailto:'.$info['email'].'">Écrire un mail</a></span>
							</div>';
					

				if ( $info["remarque"] != '')
					echo '<div class="inline deuxTiers marge30l remarqueStruct mid"><span class="ui-widget-header ui-corner-all padV10">REMARQUE</span> <span class="remarque">'. $info['remarque'] .'</span></div>';
				echo '<br />';

				foreach ( @$listeInterlocks as $ind => $data ){
					if ( $data['nomStruct'] ==  $info['label'] ){
						$added = true ;

						
						if ( $data['poste'] != '' ) $poste = ' ( <span class="poste">' . $data['poste'] . '</span> )' ; else $poste = "" ; 
						
						echo "<div class='interlockItem cinquieme pad10 marge30l margeTop10 inline ui-corner-all ui-state-default ui-state-focus' id='". $data['id'] ."'>
								  <div class='center'>
									<span class='gros label'>". $data['label']."</span>
									". $boutonsModoInterlock ."
								  </div>
								  <br />
								  <div>
									<span class='nomPrenom'>" . $data['nomPrenom'] ."</span>
									". $poste ."
								  </div>
								  <div class='adresse'>"      . $data['adresse']   ."</div>
								  <div>
									<span class='codePostal'>". $data['codePostal']."</span>
									<span class='ville'>"	  . $data['ville']	   ."</span>
								  </div>
								  <br />
								  <div>
									<div class='ui-icon ui-icon-mail-closed inline'></div>
									<a class='email' href='mailto:". $data['email'] ."' title='écrire un mail'>" . $data['email'] ."</a>
								  </div>
								  <div>
									<span class='ui-icon ui-icon-contact inline'></span>
									<span class='tel'>". $data['tel'] ."</span>
								  </div>
								  <p></p>
								  <div>
									<div class='ui-icon ui-icon-comment inline'></div>
									<div class='remarque inline'>". $data['remarque'] ."</div>
								  </div>
							</div>";
					}
				}
				if (! $added )
					echo '<p class="structVide-' .$info['id']. ' pad10">Aucun interlocuteur enregistré pour cette structure.</p>';

				echo '</div>
				</div>';
			}
		}
		else {
			echo '<tr class="ui-state-error big pad20">
				<td colspan="6">Aucune structure enregistrée ';
			if (isset($_POST['searchingfor']))
				echo 'pour la recherche <b>"'.$_POST['searchingfor'].'"</b> ';
			echo '!!</td></tr>';
		}
	?></div>
	<br />
</div>

<div class="ui-widget-content ui-corner-all center gros hide" id="modifieurPage">
	<div class="closeModifieur ui-state-active ui-corner-all" id="btnClose"><span class="ui-icon ui-icon-circle-close"></span></div>
	<div class="ui-widget-header ui-corner-all pad3">Modifier la structure "<span id="nomStructModif"></span>"</div>
	<br />
	<input type="hidden" id="modStrucId" />
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Nom court :</div>
		<input type="text" id="modStrucLabel" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Raison sociale :</div>
		<input type="text" id="modStrucRS" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Type de structure :</div>
		<input type="text" id="modStrucType" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">Adresse :</div>
		<input type="text" id="modStrucAdr" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">Code postal :</div>
		<input type="text" id="modStrucCP" size="7" />
		<br />
		<div class="ui-widget-header ui-corner-all">Ville :</div>
		<input type="text" id="modStrucVille" size="20" />
	</div>
	<div class="inline top" style="width: 200px;">
		<div class="ui-widget-header ui-corner-all">email :</div>
		<input type="text" id="modStrucMail" size="20" />
		<br />
		<div class="ui-widget-header ui-corner-all">téléphone :</div>
		<input type="text" id="modStrucTel" size="15" />
		<br />
		<div class="ui-widget-header ui-corner-all">No SIRET :</div>
		<input type="text" id="modStrucSIRET" size="20" />
	</div>
	<div class="inline bot">
		<div class="ui-widget-header ui-corner-all">Remarque :</div>
		<textarea id="modStrucRem" cols="20" rows="6"></textarea>
	</div>
	<div class="inline bot leftText">
		<button class="bouton closeModifieur">ANNULER</button>
		<br /><br /><br /><br /><br />
		<button class="bouton modif" id="structure">SAUVEGARDER</button>
	</div>
</div>

<div id='addInterlok' class='hide'>
	<div class="ui-widget-content ui-corner-all ajouteurPage"> 
			<div class=" ui-corner-all inline rightText quart">Nom et Prénom&nbsp;</div><input type="text" id="newInterlocNom" size="20" /><b class="red">*</b><br />
			
			<div class=" ui-corner-all inline rightText quart">Surnom&nbsp;</div><input type="text" id="newInterlocSurnom" size="20" />&nbsp;&nbsp;<br />
			<div class=" ui-corner-all inline rightText quart">Structure associée&nbsp;</div><input id="newInterlocStructName" type="text" disabled="" size="20"><b class="red">*</b><p>

			<div class=" ui-corner-all inline rightText">Adresse&nbsp;</div><input type="text" id="newInterlocAdr" size="31" /><b class="red">*</b><br />
			<div class=" ui-corner-all inline rightText">Code postal&nbsp;</div><input type="text" id="newInterlocCP" class="NumericInput" maxlength="5" size="5" />
			<div class=" ui-corner-all inline rightText">Ville&nbsp;</div><input type="text" id="newInterlocVille" size="15" /><p>
			
			<div class=" ui-corner-all inline rightText quart">email&nbsp;</div><input type="text" id="newInterlocMail" class='EmailInput' size="20" /><br />
			<div class=" ui-corner-all inline rightText quart">téléphone&nbsp;</div>
				<input type="text" class='phoneInput NumericInput' id="phone-1" maxlength="2" size="2" />.
				<input type="text" class='phoneInput NumericInput' id="phone-2" maxlength="2" size="2" />.
				<input type="text" class='phoneInput NumericInput' id="phone-3" maxlength="2" size="2" />.
				<input type="text" class='phoneInput NumericInput' id="phone-4" maxlength="2" size="2" />.
				<input type="text" class='phoneInput NumericInput' id="phone-5" maxlength="2" size="2" />
				<br />
			<div class=" ui-corner-all inline rightText quart">Poste occupé&nbsp;</b></div><input type="text" id="newInterlocPoste" size="20" /><br />
			<div class=" ui-corner-all inline center quart">Remarque</b></div><br /><input type="text" size="40" id="newInterlocRem" /> 
			<input type="text"  class='hide' id="newInterlocStructID" />
			<input type="text"  class='hide' id="modInterID" />
	</div> 


</div>

<div id="print"></div>
<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
