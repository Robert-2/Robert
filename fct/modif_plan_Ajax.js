
var mode_ajout = false;

$(function(){

	var ajaxRequest = 'action=afficheTekosMatos';
	var dateStartPick = $( "#modPlanStart" ).datepicker("getDate");
	var dateStart	  = $.datepicker.formatDate('yymmdd', dateStartPick );
	var dateEndPick   = $( "#modPlanEnd" ).datepicker("getDate");
	var dateEnd		  = $.datepicker.formatDate('yymmdd', dateEndPick );
	ajaxRequest += "&start="+dateStart+"&end="+dateEnd+'&excludePlan='+id_MODPLAN ;
	AjaxJson ( ajaxRequest, "plans_actions", displayTekosMatos );

	$('#modPlanBenef').on('autocompleteselect', function(e, ui) {
		var modBenef = ui.item.value;
		checkBenefExist(modBenef);
	});


	// Bouton 'ACTUALISER'
	$('#refreshModPlanDates').click(function() {
		var modTitre = $('#modPlanTitre').val();
		var modBenef = $('#modPlanBenef').val();
		var modLieu  = $('#modPlanLieu').val();
		var modStart = $.datepicker.formatDate('yymmdd', $('#modPlanStart').datepicker("getDate"));
		var modEnd   = $.datepicker.formatDate('yymmdd', $('#modPlanEnd').datepicker("getDate"));

		if (   (modTitre == '' || modTitre == undefined) || (modBenef == '' || modBenef == undefined) || (modLieu  == '' || modLieu  == undefined)
			|| (modStart == '' || modStart == undefined) || (modEnd   == '' || modEnd   == undefined) )
		{ alert('Il manque une information !'); return; }

		var ajaxRequest = 'action=refreshSessionModPlan';
		ajaxRequest += "&titre="+modTitre+"&benef="+modBenef+"&lieu="+modLieu+"&start="+modStart+"&end="+modEnd ;
		AjaxFct ( ajaxRequest, "plans_actions", 'reload' );
	});


	// Modif liste des tekos par sous plan
	$('.addTekosSouplan').click(function() {
		var timeStampSP = $(this).attr('id');
		var jourTxtSP   = $(this).attr('jour');
		var tekosSPList = [];

		var strAjax = 'action=getDispoTekos&date='+timeStampSP+'&exclude='+id_MODPLAN;
		AjaxJson(strAjax, 'plans_actions', displayTekosDispo);

		$("#modalTekos").find('.ui-widget-header').remove();
		$("#modalTekos").find('#tekosHolder').attr('id', 'tekosmodalHolder');
		$("#modalTekos").find('#tekosEquipe').attr('id', 'tekosmodalEquipe');

		// charge la liste des tekos presents et remplit la teamlist//
		$('#tekosmodalEquipe').html('');
		$(".tekosPik").removeClass('ui-state-highlight');
		$(this).parents('.spInfos').find('.tekosItem').each( function (){
			var nomTekos = $(this).html();
			var idTekos  = $(this).attr('id');
			$('#tekosmodalEquipe').append('<div class="inline ui-state-default ui-corner-all pad10 marge30l" id="teamTekmodal-'+idTekos+'">'+nomTekos.toUpperCase()+'</div>');
			$("#modalTekos").find('.tekosPik#tek-'+ idTekos ).addClass('ui-state-highlight');
			tekosSPList.push(idTekos);
		});
		var tekosListHTMLbackup = $(".spInfos#"+ timeStampSP).find(".tekosSPlist").html();

		// gestion des evenements en live //
		$("#tekosmodalHolder").on('click', '.tekosPik', timeStampSP ,function (){
			// click sur un tekos //
			var idTekos  = $(this).children('.tek_name').attr('id');
			var nomTekos = $(this).children('.tek_name').html();

			if ( $(this).hasClass('ui-state-highlight') ) {
				$(this).removeClass('ui-state-highlight');
				$('#teamTekmodal-'+idTekos).remove();
				$(".spInfos#"+ timeStampSP).find(".tekosItem#"+idTekos).remove();
				var i = tekosSPList.indexOf(idTekos);
				tekosSPList.splice(i, 1);
			}
			else {
				$(this).addClass('ui-state-highlight');
				$(".spInfos#"+ timeStampSP).find(".tekosSPlist").append ('<div class="ui-state-default inline ui-corner-all pad3 tekosItem" id="'+ idTekos +'">' + nomTekos + ' </div>' );
				$('#tekosmodalEquipe').append('<div class="inline ui-state-default ui-corner-all pad10 marge30l" id="teamTekmodal-'+idTekos+'">'+nomTekos.toUpperCase()+'</div>');
				tekosSPList.push(idTekos);
			}
		});

		$("#modalTekos").find('#periode').parent().html('Pour le '+jourTxtSP);
		$("#modalTekos").dialog({
								autoOpen: true, height: 550, width: 850, modal: true,
								title: 'Ajout de techniciens',
								buttons:{'Ok'    : function() { addTekosToSP(timeStampSP, tekosSPList); },
										 'Cancel': function() { resetTekosToSP(timeStampSP, tekosListHTMLbackup); } },
								close: function(e,u) {$("#tekosmodalHolder").off('click', '.tekosPik');
													  $("#modalTekos").off('click', '.filtreTekos');}
		});

	});


	$('.modifSPrem').blur(function() {
		var spTime = $(this).attr('id');
		var comment = encodeURIComponent($(this).val());
		var strAjax = 'action=addSessionSPrem&spTime='+spTime+'&comment='+comment+'&typeSess=plan_mod';
		AjaxFct ( strAjax, "plans_actions", false, "retourAjax");
	});



	$('#modPlan_modMatosListe').click(function() {
		$("#modalMatos").dialog({
								autoOpen: true, height: 600, width: 1150, modal: true,
								title: 'Modification de la liste de matériel',
								buttons:{'Ok'     : function() { saveModMatosList() ; },
										 'Annuler': function() { $(this).dialog("close") ; } },
								close: function(e,u) {$("#tekosmodalHolder").off('click', '.tekosPik');
													  $("#modalTekos").off('click', '.filtreTekos');}
		});
		initToolTip('#matosHolder', -120);
	});



	$('#saveModPlan').click(function(){
		var newDateStart = $('#modPlanStart').val();
		var newDateEnd = $('#modPlanEnd').val();
		if ( (newDateStart != old_date_start) || (newDateEnd != old_date_end) ) {
			if ( confirm('Attention, vous n\'avez pas actualisé la modification des dates !!\n\n Actualiser maintenant ?'))
				$('#refreshModPlanDates').click();
		}
		else {
			var strAjax = 'action=saveModPlan';
			AjaxFct ( strAjax, "plans_actions", false, "retourAjaxPlan", "calendrier" );
			$('#retourAjaxPlan').ajaxStop(function(){
				setTimeout("window.location = 'index.php?go=calendrier'", 2000);
			});
		}
	});


	$('#annuleModPlan').click(function(){
		if (confirm("NE PAS ENREGISTRER les modifications ? Sûr ?")) {
			var strAjax = "action=refreshSessionModPlan&restore=1" ;
			AjaxFct ( strAjax, "plans_actions", false, "retourAjaxPlan", "calendrier" );
			$('#retourAjaxPlan').ajaxStop(function(){
				window.location = 'index.php?go=calendrier';
			});
		}
	});

});
//// FIN DU DOCUMENT READY


function saveModMatosList () {
	var matosIdQteJson = JSON.stringify(matosIdQte);
	var strAjax = "action=sessModifMatos&typeSess=plan_mod&matList="+matosIdQteJson;
	AjaxFct ( strAjax, "plans_actions", 'reload', "retourAjax" );
//	$('#retourAjax').ajaxStop(function(){
//		window.location.reload();
//	});
}
