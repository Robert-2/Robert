<?php if ( !isset($_SESSION["user"])) { header('Location: index.php'); }

require_once ('date_fr.php');
require_once ('infos_boite.php');

$hideForPoppy = '';
if ( ! $_SESSION["user"]->isLevelMod() )
	$hideForPoppy = 'display:none';

?>
<link href="css/fileuploader.css" rel="stylesheet" type="text/css">
<script src="./js/fileuploader.js"></script>
<script>
	function coef(nbJours) {
		var coef = (nbJours-1) * (3/4) + 1;
		return coef;
	}
</script>
<script type="text/javascript" src="./fct/calendar_Ajax.js?12345"></script>
<script>
	$(function() {
		initToolTip('#frise_echelle', -180);
		initToolTip('#friseLegende', -250);
		$('#legendeConfirmCal').show();
		$('.C').css( 'overflow-y', 'scroll');
		tva_val = <?php echo TVA_VAL; ?>;
	});
</script>

<style>
	#frise_container { border: 2px solid black; width: 100%; min-height: 370px; height: 1000px; box-shadow: inset 0 0 8px #444444; }
	#frise_echelle   { position: relative; margin: 0px; padding: 0px; height: 100%; width:100%; }
	#frise_data		 { position: relative; margin: 0px; padding: 0px; font-size: 0.8em; }
	#blocNote		 { position: relative; margin: 0px; overflow: hidden; }
/*	#blocNote		 { position: absolute; top: 493px; bottom: 5px; left: 5px; right: 5px; overflow: hidden; }*/
	.plan			 { position: relative; text-align:center; font-size: 1.3em; margin-top: 2px; padding: 3px 0px; box-shadow: 0 3px 5px #888888; }
</style>


<div id="pageCalendrier" class="ui-widget-content ui-corner-all center pad20">
	<div class="ui-widget-header ui-corner-all gros">Calendrier</div>
	<?php /////////////////////////////// POUR DÉFINIR LES DATES DE DÉBUT ET FIN DE PÉRIODE PAR DÉFAUT DU CALENDRIER
		$now = new DateTime();
		$monthNow  = $now->format('m');
		$yearNow   = $now->format('Y');

		$PjourMois = '01/'.$monthNow.'/'.$yearNow;
		$DjourMois = date('d/m/Y', strtotime($yearNow.'-'.$monthNow." next month - 12 hour"));

		// init des variables pour les mettre dans les inputs "#calStart" et "#calEnd" ( ! qui veut du format 'd/m/Y')
		if (isset($_SESSION['periodeCal'])) {
			$calStart = $_SESSION['periodeCal']['start'];
			$calEnd   = $_SESSION['periodeCal']['end'];
		}
		else {
			$_SESSION['periodeCal']['start'] = $calStart = $PjourMois;
			$_SESSION['periodeCal']['end']   = $calEnd   = $DjourMois;
		}

	?>
	<div class="ui-widget leftText petit">
		<div class="inline top center">
			<div class="ui-state-default pad5 ui-corner-all petit margeTop5"> VOIR
				DU : <input type='text' value='<?php echo $calStart; ?>'  id='calStart' size="10" />
				AU : <input type='text' value='<?php echo $calEnd; ?>' id='calEnd' size="10" />
				<button class="bouton" id='loadFrise' title="charger le calendrier pour ces dates">Charger</button>
			</div>
		</div>
		<div class="inline bot leftText marge30l">
			<button class="bouton" id="ceMoisCi" title="Centrer la vue sur ce mois-ci">Ce mois-ci</button>
			<button class="bouton marge30l" id="showPrevMois" title="mois précédent"><span class="ui-icon ui-icon-carat-1-w"></span></button>
			<span class="bouton">
				<select id="selectMois">
					<option value="1">Janvier</option>
					<option value="2">Février</option>
					<option value="3">Mars</option>
					<option value="4">Avril</option>
					<option value="5">Mai</option>
					<option value="6">Juin</option>
					<option value="7">Juillet</option>
					<option value="8">Août</option>
					<option value="9">Septembre</option>
					<option value="10">Octobre</option>
					<option value="11">Novembre</option>
					<option value="12">Décembre</option>
				</select>
			</span>
			<button class="bouton" id="showNextMois" title="mois suivant"><span class="ui-icon ui-icon-carat-1-e"></span></button>
			<a style="font-size: 1.2em; border: 1px solid red; <?php echo $hideForPoppy; ?>" class="bouton marge30l" href="?go=ajout_plan" title="CRTL+clic sur le calendrier puis bouger la souris pour choisir les dates vite fait.">Ajouter un évènement</a>
		</div>
	</div>

	<div id='frise_container' class="ui-widget ui-corner-all gros margeTop5">
		<div id='frise_echelle'></div>
		<div id='frise_data'></div>
	</div>

</div>


<div id="friseLegende" class="leftText mini">
	<div class="center">
		<span class="ui-widget ui-state-default ui-corner-all marge10l padH5 padV10" style="color:#888888; font-weight: bold;">GRIS = évènements passés</span>
		<span class="ui-widget ui-state-default ui-corner-all marge10l padH5 padV10" style="color:#c24141; font-weight: bold;">ROUGE = évènements en cours !</span>
		<span class="ui-widget ui-state-default ui-corner-all marge10l padH5 padV10" style="font-weight: bold;">
			<font style="color:#b06341">DE</font>
			<font style="color:#c28d41">L'OR</font><font style="color:#c2be41">ANGE</font>
			<font style="color:#93c241">JUS</font><font style="color:#41c277">QU'</font><font style="color:#41c2bc">AU</font>
			<font style="color:#416cc2">BLEU =</font>
			<font style="color:#b06341">évènements</font>
			<font style="color:#c28d41">futurs (</font>
			<font style="color:#c2be41">de</font>
			<font style="color:#93c241">plus</font>
			<font style="color:#41c277">en</font>
			<font style="color:#41c2bc">plus</font>
			<font style="color:#416cc2">lointains)</font>
		</span>

		<button class="bouton marge10l" id='exportICS' popup="Attention, Google met environ 48h pour mettre à jour le calendrier..." style="<?php echo $hideForPoppy; ?>">
			Actualiser pour google Calendar
		</button>
		<br />
	</div>
</div>


<div id="blocNote" class="ui-widget-content ui-corner-all pad20">
	<div class="ui-widget-header ui-corner-all gros center">Rendez-vous, trucs importants, les post-it quoi</div>
	<br />
	<div style="float: right;" class="big">
		<?php
		if ($_SESSION['user']->isLevelMod()) {
			echo '<button id="addNote" class="bouton" title="Ajouter un post-it"><span class="ui-icon ui-icon-plusthick"></span></button><br /><br />';
			echo '<button id="purgeNotes" class="bouton" title="Supprimer TOUS les post-it passés"><span class="ui-icon ui-icon-eject"></span></button>';
		}
		?>
	</div>
	<div id="blocNote_content" style="margin-right: 70px;">
		<?php include('notes_calendrier.php'); ?>
	</div>
</div>


<div id="addNoteModal" title="Ajout de post-it" class="center hide">
	<br />
	<div class="inline top" style="width: 150px;">
		<div class="ui-widget-header ui-corner-all">Date : <b class="red">*</b></div>
		<input type="text" id="newNoteDate" class="inputCal" size="10" />
		<br /><br />
		<div class="ui-widget-header ui-corner-all">Important ?</div>
		<input type="checkbox" id="newNoteImportant" />
	</div>
	<div class="inline top" style="width: 350px;">
		<div class="ui-widget-header ui-corner-all">Texte du post-it : <b class="red">*</b></div>
		<textarea id="newNoteText" rows="4" cols="38"></textarea>
	</div>
</div>


<div id='newPlanDrag' class="ui-widget ui-state-default plan ui-corner-all" style='position:absolute; heigth:20px; background-color: red; z-index:1000; margin : 0 ; padding:0; ' ></div>
<div id="dialog" title="Détails de plan" class="hide"></div>

<div id="dialogContrat" title="Édition du contrat" class="hide">
	<p>Vérifiez les infos du contrat et modifiez-les si besoin, ci-dessous :</p>
	<textarea id="contratText" rows="20" cols="100"><?php
		$contratDefaut = file_get_contents(FOLDER_CONFIG.'default_contrat.txt');
		echo $contratDefaut;
	?></textarea>
</div>

<div id="toolTipPopup" class="ui-widget ui-state-highlight ui-corner-all pad20"></div>
