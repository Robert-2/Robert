
var infosNewPlan	 = {} ;
var etape			 = 1 ;
var etapesValid		 = [0, false, false, false];
var etapeNotValidMsg = '';
var mode_ajout = true;

$(function() {
	refreshEtapesBtns ();

/// Rafraîchir l'état des boutons suivants sur l'étape 1
	$('input.newPlan_data').keyup(function(){
		var suivantValid = true;
		$('input.newPlan_data').each(function(){
			if ($(this).val() == '') {
				suivantValid = false;
				return false;
			}
		});
		if (suivantValid) $('.nextEtape').removeClass('ui-state-disabled');
		else $('.nextEtape').addClass('ui-state-disabled');
	});


/// Click sur les boutons d'étapes dans le menu de droite
	$('#rightMenuSection').on('click', '.etapes', function() {
		var n = $(this).attr('id');
		n = parseInt(n.substr(12, 1), 10);

		if ($(this).hasClass('ui-state-disabled'))  return;
		if ($(this).hasClass('ui-state-highlight')) return;

		validationEtape(etape);
		if (  n > etape && etapesValid[etape] == false ) {
			alert(etapeNotValidMsg);
			return;
		}
		etape = n;
		$('.addSection').hide(transition);
		if (etape == 4) {
			var infosRequest = JSON.encode(infosNewPlan);
			var matosRequest = JSON.encode(matosIdQte);
			var tekosRequest = JSON.encode(tekosIds);
			$('#recapPlan').load('modals/planAdd_recap.php', {'infos':infosRequest, 'matos':matosRequest, 'tekos':tekosRequest});
		}
		$('#etape-'+etape).show(transition);
		refreshEtapesBtns ();
	});



/// Click sur un TEKOS pour l'ajouter à la liste
	$('.tekosPik').click(function() {
		var idTekos  = $(this).children('.tek_name').attr('id');
		var nomTekos = $(this).children('.tek_name').html();
		$('.tekosVideHelp').remove();
		if ( $(this).hasClass('ui-state-highlight') ) {
			$(this).removeClass('ui-state-highlight');
			tekosIds.splice(tekosIds.indexOf(idTekos), 1 );
			$('#teamTek-'+idTekos).remove();
		}
		else {
			$(this).addClass('ui-state-highlight');
			tekosIds.push( idTekos );
			$('#tekosEquipe').append('<div class="inline ui-state-default ui-corner-all pad10 marge30l" id="teamTek-'+idTekos+'">'+nomTekos.toUpperCase()+'</div>');
		}
		refreshEtapesBtns(1);
	});


/// Filtrage des tekos par catégorie
	$('.filtreTekos').click(function() {
		var categ = $(this).attr('id');
		$('.tekosPik').hide();
		if ($(this).hasClass('ui-state-error')) {
			$('.filtreTekos').removeClass('ui-state-error');
			$('.tekosPik').show();
		}
		else {
			$('.filtreTekos').removeClass('ui-state-error');
			$('.tekosPik').each(function(){
				var categTek = $(this).children('.tek_categ').children('img').attr('alt');
				if (categTek == categ){
					$(this).show();
				}
			});
			$(this).addClass('ui-state-error');
		}
	});


/// Ajoute un tekos ds pour un sous plan
	$('#etape-4').on('click', '.addTekosSouplan', function() {
		var timeStampSP = $(this).attr('id');
		var jourTxtSP   = $(this).attr('jour');
		var tekosSPList = [];

		var strAjax = 'action=getDispoTekos&date='+timeStampSP;
		AjaxJson(strAjax, 'plans_actions', displayTekosDispo);

		$("#tekosmodalHolder").off('click', '.tekosPik');
		$("#modalTekos").children("#etape-2").show();
		$("#modalTekos").find('#periode').parent().html('Pour le <span id="periode">'+jourTxtSP+'</span>');
		$("#modalTekos").find('.ui-widget-header').remove();
		$("#modalTekos").find('#tekosHolder').attr('id', 'tekosmodalHolder');
		$("#modalTekos").find('#tekosEquipe').attr('id', 'tekosmodalEquipe');

		// charge la liste des tekos presents et remplit la teamlist//
		$("#modalTekos").find('#tekosmodalEquipe').html('');
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

		$("#modalTekos").dialog({
								autoOpen: true, height: 550, width: 850, modal: true,
								title: 'Ajout de techniciens',
								buttons:{'Ok'    : function() {addTekosToSP(timeStampSP, tekosSPList);},
										 'Cancel': function() {resetTekosToSP(timeStampSP, tekosListHTMLbackup);}},
								close: function(e,u) {$("#tekosmodalHolder").off('click', '.tekosPik');
													  $("#modalTekos").off('click', '.filtreTekos');}
		});

	});



/// modif d'un textarea de sousPlan (étape 4)
	$('#etape-4').on('blur', '.modifSPrem', function() {
		var spTime = $(this).attr('id');
		var comment = encodeURIComponent($(this).val());
		var strAjax = 'action=addSessionSPrem&spTime='+spTime+'&comment='+comment+'&typeSess=plan_add';
		AjaxFct ( strAjax, "plans_actions", false, "retourAjaxPlan");
	});


	$('.plan_save').click(function() {
		var type = $(this).attr('id');
		var ajaxRequest = "action=saveSessionPlan&type=" + type ;
		AjaxFct ( ajaxRequest, "plans_actions", false, "retourAjaxPlan", "calendrier" );
	});

});
// FIN DU DOCUMENT.READY


///////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////// FONCTIONS DE L'INTERFACE ///////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////



// click sur étape précédente
function prevEtape () {
	if (etape > 1) {
		etape -= 1;
		$('.addSection').hide(transition);
		$('#etape-'+etape).show(transition);
	}
	refreshEtapesBtns ();
}

// click sur étape suivante
function nextEtape () {
	if (etape < 4) {

		validationEtape(etape);
		if (etape == 1) checkBenefExist($('#beneficiaire').val());

		if (etapesValid[etape] == true) {
			etape += 1;
			$('.addSection').hide(transition);
			if (etape == 4) {
				var infosRequest = JSON.encode(infosNewPlan);
				var matosRequest = JSON.encode(matosIdQte);
				var tekosRequest = JSON.encode(tekosIds);
				$('#recapPlan').load('modals/planAdd_recap.php', {'infos':infosRequest, 'matos':matosRequest, 'tekos':tekosRequest});
			}
			$('#etape-'+etape).show(transition);
		}
		else alert(etapeNotValidMsg);
	}
	refreshEtapesBtns ();
}

// pour activer / désactiver le bouton précédent
function refreshEtapesBtns (offset) {
	if (offset == null || offset == undefined) offset = 0;
	if (etape > 1) {
		$('.prevEtape').removeClass('ui-state-disabled');
		$('#rappelPlanInfos').show();
		$('#bigTotalDiv').hide();
		if (etape >= 3) {
			$('.totaux').show();
		}
	}
	else {
		$('.prevEtape').addClass('ui-state-disabled');
		$('#rappelPlanInfos').hide();
		$('#bigTotalDiv').hide();
	}

	validationEtape(etape);

	$('.nextEtape').addClass('ui-state-disabled');
	if (etapesValid[etape])
		$('.nextEtape').removeClass('ui-state-disabled');

	$('.etapes').removeClass('ui-state-highlight');
	$('#indic-etape-'+etape).addClass('ui-state-highlight');
	$('#indic-etape-'+etape).removeClass('ui-state-disabled');
	$('#indic-etape-'+etape).addClass('ui-state-default');
	if (etape > 0 && offset != 0) {
		var etapeTofind = etape + offset;
		if (etapesValid[etape]) {
			$('#indic-etape-'+etapeTofind).removeClass('ui-state-disabled');
			$('#indic-etape-'+etapeTofind).addClass('ui-state-default');
		}
		else {
			$('#indic-etape-'+etapeTofind).removeClass('ui-state-default');
			$('#indic-etape-'+etapeTofind).addClass('ui-state-disabled');
		}
	}

	if (etape >= 4) {
		$('.nextEtape').addClass('ui-state-disabled');
	}
}


// Valide une étape donnée
function validationEtape (nEtape) {
	if (nEtape == 1) {
		var incomplet	= false;
		var ajaxRequest = 'action=afficheTekosMatos';
		var dateStartPick = $( "#picker_start" ).datepicker("getDate");
		var dateStart	  = $.datepicker.formatDate('yymmdd', dateStartPick );
		var dateEndPick   = $( "#picker_end" ).datepicker("getDate");
		var dateEnd		  = $.datepicker.formatDate('yymmdd', dateEndPick );

		$(".newPlan_data").each (function() {
			var data = $(this).attr("id") ;
			var val  = $(this).val() ;
			if (data == 'titre' || data == 'beneficiaire' || data == 'lieu' || data == 'picker_start') {
				if (val == '' || val == undefined) {incomplet = true;return false;} // false -> arrête le each
			}
			ajaxRequest += "&" + data + '=' + val ;
			if (data == 'titre')		$('#rappelTitrePlan').html(val.toUpperCase());
			if (data == 'lieu')			$('#rappelLieuPlan').html(val);
			if (data == 'beneficiaire') $('#rappelBenefPlan').html(val);
			infosNewPlan[data] = val;
		});

		if (incomplet) {etapeNotValidMsg = 'Vous devez remplir TOUS les champs avec une étoile !!';etapesValid[1] = false;return;}

		if (dateEnd < dateStart) {etapeNotValidMsg = 'Date de fin antérieure à date de début !';etapesValid[1] = false;return;}

		ajaxRequest += "&start="+dateStart+"&end="+dateEnd ;
		AjaxJson ( ajaxRequest, "plans_actions", displayTekosMatos );

		var ajaxReq = "action=initSessionPlanAdd&start="+dateStart+"&end="+dateEnd+'&titre='+infosNewPlan['titre']+'&beneficiaire='+infosNewPlan['beneficiaire']+'&lieu='+infosNewPlan['lieu'] ;
		AjaxJson ( ajaxReq, "plans_actions", alerteErr );
		etapesValid[1] = true;
	}


	else if (nEtape == 2) {
		if (tekosIds.length == 0) {
			etapeNotValidMsg = 'Vous devez choisir au moins un technicien !';
			etapesValid[2] = false;
			return;
		}
		else {
			var tekosList = JSON.encode(tekosIds);
			var strAjax = 'action=refreshSessionAddPlan&tekosArr='+tekosList;
			AjaxJson ( strAjax, "plans_actions", alerteErr );
			etapesValid[2] = true;
		}
	}


	else if (nEtape == 3) {
		if (Object.keys(matosIdQte).length == 0) {
			etapeNotValidMsg = 'Vous devez choisir au moins un matériel !';
			etapesValid[3] = false;
			return;
		}
		else {
			var matosList = JSON.encode(matosIdQte);
			var ajaxStr = 'action=refreshSessionAddPlan&matosList='+matosList;
			AjaxJson ( ajaxStr, "plans_actions", alerteErr );
			etapesValid[3] = true;
		}
	}


	else {
		return;
	}
}

