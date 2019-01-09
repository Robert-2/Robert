
var currentYear;
var detailsContent;

$(function() {

	$( "#calStart" ).datepicker({dateFormat: "dd/mm/yy", firstDay: 1, changeMonth: true, changeYear: true});
	$( "#calEnd" ).datepicker({dateFormat: "dd/mm/yy", firstDay: 1, changeMonth: true, changeYear: true});

	nbJoursSelection = 0 ;
	dateStartPick    = -1 ;
	dateEndPick      = -1 ;
	$("#loadFrise").click( function () {loadFrise();} );
	loadFrise();

	currentYear  = $.datepicker.formatDate('yy', $("#calStart").datepicker("getDate") ) ;
	var currentMonth = $("#calStart").datepicker("getDate") ;
	$('#selectMois').val(currentMonth.getMonth()+1);

/// AFFICHAGE D'UN MOIS COMPLET
	$('#selectMois').change(function () {loadMois($('#selectMois').val(), currentYear);});

/// AFFICHAGE DU MOIS ACTUEL
	var moisActuel    = $.datepicker.formatDate('mm', new Date());
	var anneeActuelle = $.datepicker.formatDate('yy', new Date());
	$('#ceMoisCi').click(function () {loadMois(moisActuel, anneeActuelle);$('#selectMois').val(parseInt(moisActuel, 10));});

/// AFFICHAGE DU MOIS SUIVANT
	$('#showNextMois').click(function () {moisAfter ($('#selectMois').val(), currentYear);} );

/// AFFICHAGE DU MOIS PRÉCÉDANT
	$('#showPrevMois').click(function () {moisBefore ($('#selectMois').val(), currentYear);} );

	$('#frise_container').disableSelection();


/// CLIC-SLIDE sur des jours pour ajouter un Plan
	var beginPlan = false ;var endPlan = false ;
	var drawPlan = false ;var debugX = 0 ;
	$("#frise_container").mousedown( function (e){
		debugX = beginPlan = e.pageX;
		if  ( e.ctrlKey ) drawPlan = true;

	}).mousemove( function (e) {
		var width = e.pageX - beginPlan ;
		var left = beginPlan - ( $('.L').position().left + $('.L').innerWidth() ) ;
		if ( drawPlan ) {
			$('#newPlanDrag').css( {'color'	  : '#F00',
									'padding' : '5px',
									'left'    : left -12 +'px' ,
									'width'   : width -12 +'px',
									'top'     : e.pageY +'px'} )
							 .html('Nouveau plan ');
		}
	}).mouseup( function (e) {
		endPlan = e.pageX;
		$('.dayInCalendar').each( function (ind, obj ){
			var x = $(obj).offset() ;var w = $(obj).width();
			if ( beginPlan >= x.left && beginPlan <= x.left + w ) beginPlan = $(obj).attr('realDate') ;
			if ( endPlan >= x.left && endPlan <= x.left + w )	  endPlan = $(obj).attr('realDate') ;
		});
		if ( beginPlan != false && endPlan != false && e.ctrlKey ) {
			drawPlan = false ;
			window.location = "?go=ajout_plan&start="+beginPlan+"&end="+endPlan;
		}
	});


/// DOUBLE-CLIC sur un jour pour ajouter un plan (date de début seulement)
	$("#frise_container").dblclick(function(e) {
		var clicDay = e.pageX;var beginPlan = false;
		$('.dayInCalendar').each( function (ind, obj ){
			var x = $(obj).offset() ;var w = $(obj).width();
			if ( clicDay >= x.left && clicDay <= x.left + w ) beginPlan = $(obj).attr('realDate') ;
		});
		if (beginPlan != false) {
			window.location = "?go=ajout_plan&start="+beginPlan+"&end="+beginPlan;
		}
	});



/// EXPORTATION DU CALENDRIER
	$('#exportICS').click(function() {
		if (confirm('Exporter le calendrier ?')) {
			AjaxFct('action=export', 'calendar_actions', false, 'dialog');
			$( "#dialog" ).dialog({
				autoOpen: true, height: 250, width: 350, modal: true,
				buttons: {"Fermer"	: function() {$(this).dialog("close");}},
				title: "Exportation ICS"
			});
		}
	});


/// APPLICATION DE LA REMISE
	$('#dialog').on('blur', '#remisePercent', function () {
		var remisePercent		= parseFloat($(this).val());
		var totalRemisableTTC	= parseFloat($('#totalRemisablePlanTTC').html());
		var totalNonRemisable	= parseFloat($('#totalNonRemisablePlan').html());
		var totalBeforeTTC		= parseFloat($('#totalTTCplan').html());

		var remise			= totalRemisableTTC * (remisePercent / 100);
		var totalRemisedTTC = totalRemisableTTC - remise + totalNonRemisable;

		console.log('TotTTC : '+totalBeforeTTC+' ; TotRem : '+totalRemisableTTC.toFixed(2)+' ; NonComp : '+totalNonRemisable);
		console.log('remise % : '+ remisePercent +' ; Remise : '+remise.toFixed(2)+' ; TotFinal : '+totalRemisedTTC.toFixed(2));
		if (totalRemisedTTC < (totalBeforeTTC - totalRemisableTTC) || remisePercent > 100) {
			alert('ATTENTION ! La remise dépasse le total remisable !\n\nPourquoi pas leur donner le matos aussi ?');
			$('#totalAPremise').html(totalBeforeTTC.toFixed(2));
			$('#remiseMontant').val(totalBeforeTTC.toFixed(2));
			$('#remisePercent').val('0');
			return;
		}
		if (remisePercent <= 0) {
			$('#totalAPremise').html(totalBeforeTTC.toFixed(2));
			$('#remiseMontant').val(totalBeforeTTC.toFixed(2));
			$('#remisePercent').val('0');
			return;
		}
		$('#remisePercent').val(remisePercent.toFixed(3));
		$('#totalAPremise').html(totalRemisedTTC.toFixed(2));
		$('#remiseMontant').val((totalRemisedTTC).toFixed(2));
	});


	$('#dialog').on('blur', '#remiseMontant', function () {
		var totalVoulu		= parseFloat($(this).val());
		var totalRemisableTTC	= parseFloat($('#totalRemisablePlanTTC').html());
		var totalBeforeTTC		= parseFloat($('#totalTTCplan').html());
		var remise		  = totalBeforeTTC - totalVoulu;
		var remisePercent = (remise / totalRemisableTTC) * 100;
		if (totalVoulu < (totalBeforeTTC - totalRemisableTTC) || remisePercent > 100) {
			alert('ATTENTION ! La remise dépasse le total remisable !\n\nPourquoi pas leur livrer le matos à l\'oeil aussi ?');
			$('#totalAPremise').html(totalBeforeTTC.toFixed(2));
			$('#remiseMontant').val(totalBeforeTTC.toFixed(2));
			$('#remisePercent').val('0');
			return;
		}
		$('#remisePercent').val(remisePercent.toFixed(3));
		$('#totalAPremise').html(totalVoulu.toFixed(2));
	});

	// Touche entrée
	$('#dialog').on('keypress', '#remisePercent', function (e) {
		if (e.keyCode == 13) { $(this).blur(); }
	});
	$('#dialog').on('keypress', '#remiseMontant', function (e) {
		if (e.keyCode == 13) { $(this).blur(); }
	});


//	$('#dialog').on('blur', '#remiseMontant', function () {
//		var remiseMontant = parseFloat($(this).val());
//		var totalRemisable = parseFloat($('#totalRemisablePlan').html());
//		var totalBeforeHT  = parseFloat($('#totalHTplan').html());
//		var totalBeforeTTC = parseFloat($('#totalTTCplan').html());
//		if (remiseMontant >= totalRemisable) {
//			alert('ATTENTION ! remise ≥ à 100% !\n\nPourquoi pas leur donner le matos aussi ?');
//			$('#totalAPremise').html(totalBeforeTTC.toFixed(2));
//			$('#remiseMontant').val('0');
//			$('#remisePercent').val('0');
//			return;
//		}
//		if (remiseMontant <= 0) {
//			$('#totalAPremise').html(totalBeforeTTC.toFixed(2));
//			$('#remiseMontant').val('0');
//			$('#remisePercent').val('0');
//			return;
//		}
//		var remisePercent = (remiseMontant / totalRemisable) * 100  ;
//		var totalRemisedHT  = totalBeforeHT - remiseMontant ;
//		$('#remisePercent').val(remisePercent.toFixed(3));
//		$('#totalAPremise').html((totalRemisedHT + (totalRemisedHT * tva_val)).toFixed(2));
//	});
//


/// AJOUT DE POST-IT
	$('#addNote').click(function() {
		$('#addNoteModal').dialog({
								autoOpen: true, height: 300, width: 600, modal: true,
								buttons:{'Ajouter': function() {addPostIt() ;},
										 'Annuler': function() {$(this).dialog("close") ;}}
		});
	});


/// Suppression de TOUS les post-it passés
	$('#purgeNotes').click(function() {
		var strAjax = 'action=purgeNotes';
		AjaxJson(strAjax, 'notes_actions', refreshPostit);
	});


/// Suppression de Post-it
	$("#blocNote_content").on("click", ".supprNote", function() {
		if (!confirm('Sûr de vouloir supprimer ce post-it ?')) return;
		var idNote = $(this).attr('id');
		var strAjax = 'action=delNote&idToDel='+idNote;
		AjaxJson(strAjax, 'notes_actions', refreshPostit);
	});


	$('#dialog').on('click', '.boutonsPlan', function() {
		$('.boutonsPlan').removeClass('ui-state-active');
		$(this).addClass('ui-state-active');
	});


});
/// FIN DU $(DOCUMENT).READY !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


// Ajoute un post it
function addPostIt () {
	var datePick  = $('#newNoteDate').datepicker("getDate");
	var date	  = $.datepicker.formatDate("@", datePick);
	var important = $('#newNoteImportant').attr('checked');
	var texte	  = encodeURIComponent($('#newNoteText').val());
	if (date == null) {alert('Vous devez entrer une date pour le post-it !');return;}
	if (texte == '') {alert('Vous devez entrer un texte pour le post-it !');return;}
	var imp = '0';
	if (important == 'checked') imp = '1';

	var strAjax = 'action=addCalNote&date='+date+'&texte='+texte+'&important='+imp;
	AjaxJson(strAjax, 'notes_actions', refreshPostit);
}

// Rafraichi les post-it
function refreshPostit (data) {
	if (data.error == 'OK') {
		$('#blocNote_content').load('modals/notes_calendrier.php');
		$('#addNoteModal').dialog("close");
		$('#newNoteText').val('');
		$('#newNoteDate').val('');
	}
	else {
		alert(data.error);
	}
}


// Charge les données du calendrier, pour une période donnée
function loadFrise () {
	dateStartPick = $( "#calStart" ).datepicker("getDate");
	var yStart = $.datepicker.formatDate('yy', dateStartPick ) ;
	var mStart = $.datepicker.formatDate('mm', dateStartPick ) ;
	var jStart = $.datepicker.formatDate('dd', dateStartPick ) ;

	dateEndPick = $( "#calEnd" ).datepicker("getDate");
	var yEnd = $.datepicker.formatDate('yy', dateEndPick ) ;
	var mEnd = $.datepicker.formatDate('mm', dateEndPick ) ;
	var jEnd = $.datepicker.formatDate('dd', dateEndPick ) ;

	if (dateEndPick < dateStartPick) {
		alert('ATTENTION ! La date de fin est antérieure à celle de début...');
		return;
	}
	nbJoursSelection = Math.round( (dateEndPick - dateStartPick) / (1000 * 3600 * 24 ) + 1 ) ;

	var ajaxReq = 'action=loadCalendar&yS=' + yStart + "&mS="+mStart+"&jS="+jStart+"&yE="+yEnd+"&mE="+mEnd+"&jE="+jEnd ;

	// Execute la fonction displayFrise, pour la div #frise_data, après récup des données en JSON
	AjaxJson (ajaxReq, 'calendar_actions', displayFrise, '#frise_data') ;
}


// Affiche la frise, avec l'échelle de temps et les plans
function displayFrise ( data, displayDiv ) {
	// data est un objet contenant le retour json (décodé par la fonction AjaxJson)
	$(displayDiv).html('');
	$("#frise_echelle").html('');

	// Ajustage de la hauteur du container de la frise en fonction du nbre de plans
	var NBplans = Object.keys(data).length;
//	console.log(NBplans, 'plans');
	$("#frise_container").height(NBplans * 50);

	var maxX = $(displayDiv).width();
	var maxY = $("#frise_container").height() - 20;
	var widthUnit   = maxX / nbJoursSelection ;

	var baseMs   = dateStartPick.getTime();
	var baseDate = new Date( baseMs );
	var lastMois = $.datepicker.formatDate('MM', baseDate );
	var echelleDays = '';
	var echelleMois = [];

	var todayD  = new Date();
	var todayJ = $.datepicker.formatDate('dd', todayD ) ;
	var todayM = $.datepicker.formatDate('MM', todayD ) ;
	var todayA = $.datepicker.formatDate('yy', todayD ) ;
	var today  = todayJ + todayM + todayA ;
	var hierD = new Date();hierD.setDate(todayD.getDate()-1);
	var demainD = new Date();demainD.setDate(todayD.getDate()+1);

	/////////////////////////////// Dessin de l'échelle de la frise, les mois et les jours
	var dm = 0 ;var nbMois = 1;
	for ( j = 0 ; j < nbJoursSelection ; j ++  ) {
		var newJour = new Date(baseDate.getFullYear(), baseDate.getMonth(), baseDate.getDate() +j );
		var Jour   = $.datepicker.formatDate('dd', newJour ) ;
		var JourTxt= $.datepicker.formatDate('DD', newJour ) ;
		var Mois   = $.datepicker.formatDate('MM', newJour ) ;
		var MoisNb = $.datepicker.formatDate('mm', newJour ) ;
		var Annee  = $.datepicker.formatDate('yy', newJour ) ;
		var ActDay = Jour + Mois + Annee ;

		mois_Xunit = widthUnit * dm ;
		if (Mois != lastMois) {
			echelleMois.push(mois_Xunit, lastMois) ;
			lastMois = Mois;
			dm = 0;nbMois ++;
		}
		dm++ ;
		var jour_Xunit = widthUnit ;
		if ( j == nbJoursSelection -1 ) jour_Xunit = widthUnit - 1 ;
		var background = '';var border = '';
		if ( j % 2 ) background = "background-color : rgba(127,127,127,0.2); ";
		if (ActDay == today) background = "background-color : rgba(250,127,127,0.2); font-weight: bold;";
		if (JourTxt == "Lundi") {border = 'border-left:1px solid #f90;';jour_Xunit -= 1;}
		echelleDays += "<div class='dayInCalendar' "
							+"style='position:relative; width:"+jour_Xunit+"px;  height:"+maxY+"px; float:left; "+background+" "+border+"' "
							+"popup='"+JourTxt+"' "
							+"realDate='"+Annee+MoisNb+Jour+"' >"
								+Jour
						+"</div>";
	}
	if (dm == 1) echelleMois.push((widthUnit - (nbMois * 2) - 2), Mois) ;
	else		 echelleMois.push((mois_Xunit + widthUnit - (nbMois * 2) - 2), Mois) ;


	var echelleMoisHtml = '';
	$.each(echelleMois, function (k, val) {
		if (k % 2 == 0)
			echelleMoisHtml += "<div style='width:"+val +"px; position:relative; float:left;' class='ui-widget ui-state-default'>" ;
		else
			echelleMoisHtml += val + "</div>"
	});

	$("#frise_echelle").append(echelleMoisHtml + '<br />' + echelleDays);


	if (data.erreur == 'rien') return ;
	/////////////////////////////// Dessin des plans dans la frise, un par ligne
	var htmlFrise = '';
	var colorInd = 0 ;
	var topEvent = 0 ;
	var colors = Array( '#b06341', '#c28d41', '#c2be41', '#93c241', '#41c277', '#41c277', '#41c2bc', '#416cc2' ); // dégradé du rouge au bleu
	jQuery.each( data, function (i, val) {
		var color     = colors[colorInd] ;

		var dateDebut = new Date (val.debut) ;
		var dateFin   = new Date (val.fin) ;
		var dateFinJour = new Date(val.fin);dateFinJour.setHours(23);dateFinJour.setMinutes(59);
		var debut     = (dateDebut - dateStartPick) / ( 1000 * 3600 * 24 ) * widthUnit;
		var longueur  = val.nbJours * widthUnit - 2 ;
		var beforeDiv = '' ;var afterDiv = '' ;
		var createur = val.creator;

		if ( dateFinJour < todayD )								color = '#888888';
		else if ( dateDebut < todayD && dateFinJour > todayD )	color = '#c24141; font-weight: bold';
		else {
			if (colorInd == 8) colorInd = 0;
			else colorInd ++ ;
		}

		if ( debut < 0 ) {
			rognage = debut ;
			debut    = 0 ;
			longueur = longueur + rognage ;
			beforeDiv  = "<div style='position:relative; width:10px; padding-left:10px; float:left;' title='étendre la vue plus loin, vers le passé'>";
			beforeDiv += "<a href='#' class='ui-icon ui-icon-arrowthickstop-1-w' onClick='jourBefore()'></a></div>" ;
		}
		if ( ( debut + longueur ) > maxX ) {
			longueur  = maxX - debut - 2 ;
			afterDiv  = "<div style='position:relative; width:10px; padding-right:10px; float:right;' title='étendre la vue plus loin, vers le futur'>";
			afterDiv += "<a href='#' class='ui-icon ui-icon-arrowthickstop-1-e' onClick='jourAfter()'></a></div>" ;
		}

		(val.confirm != 0) ? iconeConfirm = 'ui-icon-check' : iconeConfirm = 'ui-icon-help';
		(val.confirm != 0) ? txtConfirm = 'créé par '+createur+', confirmé' : txtConfirm = 'créé par '+createur+', en attente';

		$(displayDiv).append("<div id='plan_"+i+"' style='position:absolute; width:"+longueur+"px; left:"+debut+"px; top:"+topEvent+"px' class='ui-widget ui-state-default plan ui-corner-all'>"
							+ beforeDiv
							+ afterDiv
							+ "<div onclick='loadDetailsPlan("+i+")' class='doigt' title='"+txtConfirm+"' style='color:"+ color +";'>"
								+ val.titre
								+ "<span class='inline bot bordFin bordSection marge10l ui-icon "+iconeConfirm+"'></span>"
							+ "</div>"
					+"</div>");
		var eventHeight = $("#plan_"+i+"").height();
		topEvent += eventHeight + 9;
		if (topEvent >= maxY - eventHeight - 10) topEvent = 0;
	});

	var topDiv = maxY - 20 ;
	$(displayDiv).css('top', "-"+topDiv+'px');
}


// Décale la période d'affichage de la frise d'un jour après la fin
function jourAfter () {
	$( "#calEnd" ).datepicker("setDate", "c+1");
	loadFrise();
}

// Décale la période d'affichage de la frise d'un jour avant le début
function jourBefore () {
	$( "#calStart" ).datepicker("setDate", "c-1");
	loadFrise();
}

// Décale la période d'affichage au mois suivant
function moisBefore (moisCurr, anneeCurr) {
	var wantMois  = parseInt(moisCurr,10) - 1 ;
	var wantAnnee = anneeCurr;
	if (wantMois == 0 ) {
		wantAnnee = parseInt(anneeCurr,10) - 1 ;
		wantMois = '12';
	}
	$('#selectMois').val(wantMois);
	loadMois(wantMois, wantAnnee);
}

// Décale la période d'affichage au mois précédant
function moisAfter (moisCurr, anneeCurr) {
	var wantMois  = parseInt(moisCurr,10) + 1 ;
	var wantAnnee = anneeCurr;
	if (wantMois == 13 ) {
		wantAnnee = parseInt(anneeCurr,10) + 1 ;
		wantMois = '01';
	}
	$('#selectMois').val(wantMois);
	loadMois(wantMois, wantAnnee);
}

// défini la période d'affichage sur un mois complet
function loadMois (mois, annee) {
	var lastDay = new Date(annee, mois, 0).getDate();
	$("#calStart").datepicker("setDate", '01/'+mois+'/'+annee);
	$("#calEnd").datepicker("setDate", lastDay+'/'+mois+'/'+annee);
	currentYear = annee;
	loadFrise();
}


// Charge les détails d'un plan
function loadDetailsPlan (idPlan) {
	var ajaxReq = "action=loadPlan&ID=" + idPlan ;
	AjaxJson (ajaxReq, 'plans_actions', montreDetailsPlan) ;
}

var iconeMeteo = '';
// Affiche les détails d'un plan dans une fenêtre 'dialog'
function montreDetailsPlan (datas) {
	if (datas == undefined) return;
	var dialogTitre	  = 'Détails de l\'évènement "'+datas.titre+'" <i class="micro">(No '+datas.id+')</i> - Géré par : '+datas.createur;

	// DIV d'en haut, dates, titre bénef et icones
	var pluriel = '';
	if (datas.nbSousPlans > 1) pluriel ='s';
	var dialogContent ='<div style="position:absolute; top:45%; left:20%; right:20%;" class="ui-state-error ui-corner-all center pad10 shadowOut hide" id="retourAjaxModalCentre"></div>'
					 + '<div class="ui-state-default ui-corner-all center gros pad10">'
							+'du <b>'+datas.dateDebut+'</b> au <b>'+datas.dateFin+'</b> '
							+'('+datas.nbSousPlans+' jour'+pluriel+')<br />'
							+'à <span class="enorme">'+datas.lieu + '</span>, pour <a href="?go=beneficiaires&affiche='+datas.benef+'" class="gros">'+datas.benef+'</a>'
							+'<div style="position: absolute; top: 10px; left: 18px;" title="'+datas.resaTxt+'"><img src="gfx/icones/icon-'+datas.resa+'.png" alt="'+datas.resa+'" id="planStatusIcon" /></div>'
							+'<div style="position: absolute; top: 10px; right:18px;" class="petit" id="meteoDay"></div>'
					 + '</div>';

	// DIV des sous plans (tekos et remarques)
	dialogContent += '<div class="inline top" style="width:375px;">';
	if (datas.levelAuth == true) {
		dialogContent += '<div class="ui-state-highlight ui-corner-all pad5 mini margeTop10 center">'
							+'<button class="ui-state-default ui-corner-all pad5 center boutonsPlan doigt ui-state-active" onClick="detailsPlan()">Détail jours</button> '
							+'<button class="ui-state-default ui-corner-all pad5 center boutonsPlan doigt" onClick="devisPlan('+datas.id+')">DEVIS</button> '
							+'<button class="ui-state-default ui-corner-all pad5 center boutonsPlan doigt" onClick="facturePlan('+datas.id+')">FACTURE</button> '
							+'<button class="ui-state-default ui-corner-all pad5 center boutonsPlan doigt" onClick="fichiersPlan('+datas.id+')">Fichiers divers</button> '
						+'</div>'
	}
		dialogContent += '<div id="detailPlanLeftDiv">';
			detailsContent = '';
			for (sp in datas.sousPlans) {
				detailsContent +='<div class="mini margeTop10 ui-state-default ui-corner-all pad5 shadowOut">'
								+'<div class="gros"><b>'+datas.sousPlans[sp]['jour']+'</b></div>'
								+'<div class="inline top ui-state-default ui-corner-all margeTop5 pad3" style="width:100px" title="techniciens"> avec '+datas.sousPlans[sp]['tekos']+'</div>';
				if (datas.sousPlans[sp]['rem'] != '')
					detailsContent	 += '<div class="inline top shadowIn ui-corner-all margeTop5 marge10l pad10" style="width:225px" title="remarque">'+datas.sousPlans[sp]['rem']+'</div>' ;
				detailsContent += '</div>';
			}
		dialogContent += detailsContent+'</div>'
				+'</div>';

	// DIV des totaux, remise et détail matériel
	dialogContent += '<div class="inline top ui-state-default ui-corner-all pad10 marge10l margeTop10 shadowOut" style="width:310px;" id="detailPlanRightDiv">';

	var detailMatosStr = '<br /><b>DÉTAIL DU MATÉRIEL :</b><br /><br />';
	var ssTotalSon = 0;var ssTotalLight = 0;var ssTotalStruct = 0;var ssTotalTransp = 0;var ssTotalMatosExt = 0;var totalJour = 0;
	if (datas.matos != undefined) {
		$.each(datas.matos, function (titreSsCat, matosSsCat) {
			detailMatosStr += '<div class="marge15bot">'
							+	'<u>'+titreSsCat+'</u>';
			$.each(matosSsCat, function(id, info) {
				detailMatosStr += '<div class="marge30l">'
									+'<span style="float:right">'+info.prix.toFixed(2)+' €</span>'
									+ info.qte+' x <b>'+info.ref+'</b>';
				if (info.ext == 1) detailMatosStr += '<br /><i class="mini">(chez '+info.extOwn+')</i>';
				detailMatosStr += '</div>';

				totalJour += info.prix;
				if (info.cat == "son")
					ssTotalSon	  += info.prix;
				else if (info.cat == "lumiere")
					ssTotalLight  += info.prix;
				else if (info.cat == "structure")
					ssTotalStruct += info.prix;
				else if (info.cat == "transport")
					ssTotalTransp += info.prix;
				if (info.ext == 1)
					ssTotalMatosExt += info.prix;
			});
			detailMatosStr += '</div>';
		});
		var totalHT				= (totalJour - ssTotalTransp) * coef(datas.nbSousPlans) + ssTotalTransp;
		var totalRemisableHT	= totalHT - ssTotalTransp - ssTotalMatosExt * coef(datas.nbSousPlans);
		var totalTTC			= totalHT * (1+tva_val);
		var totalRemisableTTC	= totalRemisableHT * (1+tva_val) ;
		var totalNonRemisable	= (ssTotalTransp + ssTotalMatosExt * coef(datas.nbSousPlans)) * (1+tva_val);


		dialogContent += '<b>TOTAL : </b>'
						+'<span class="mini ui-state-disabled">'+datas.nbSousPlans+' jour(s), coef <b>'+coef(datas.nbSousPlans)+'</b></span><br />'
						+'<div class="rightText gros">H.T. : <span class="gras" id="totalHTplan">'+totalHT.toFixed(2)+'</span><b> €</b></div>'
						+'<div class="hide">RemisableHT : <span id="totalRemisablePlan">'+totalRemisableHT.toFixed(2)+'</span> €</div>'
						+'<div class="rightText gros">T.T.C. : <span class="gras" id="totalTTCplan">'+totalTTC.toFixed(2) +'</span><b> €</b></div>'
						+'<div class="rightText petit">Remisable : <span id="totalRemisablePlanTTC">'+totalRemisableTTC.toFixed(2)+'</span> €</div>'
						+'<div class="hide"><span id="totalNonRemisablePlan">'+totalNonRemisable+'</span></div>'
						+'<div class="leftText micro">'
							+'<b>Remise</b> sur total sans transport ni matos ext,<br />pour le devis ou la facture PDF :'
						+'</div><div class="rightText mini">'
							+'<input type="text" id="remisePercent" size="4" value="0" /> <b class="gros">%</b>, '
							+'donc <input type="text" id="remiseMontant" size="5" value="'+totalTTC.toFixed(2)+'" /> <b class="gros">€</b>'
						+'</div>'
						+'<div class="rightText enorme">'
							+'<span class="mini"><b>TOTAL T.T.C.</b> : </span><span class="gros gras" id="totalAPremise">'+totalTTC.toFixed(2)+'</span><b> €</b>'
						+'</div>'
						+'<div class="rightText petit marge15bot">'
							+'<span class="red" id="salairesTxt">Montant des salaires : </span><input type="text" class="NumericInput" size="5" onKeyUp="checkSalaires()" id="totalSalaires" /> €'
						+'</div>';

		dialogContent += '<b>SOUS TOTAUX :</b>';
		if (ssTotalSon != 0) dialogContent += '<div class="rightText">SON : '+ssTotalSon.toFixed(2)+' €</div>';
		if (ssTotalLight != 0) dialogContent += '<div class="rightText">LUMIÈRE : '+ssTotalLight.toFixed(2)+' €</div>';
		if (ssTotalStruct != 0) dialogContent += '<div class="rightText">STRUCTURE : '+ssTotalStruct.toFixed(2)+' €</div>';
		if (ssTotalTransp != 0) dialogContent += '<div class="rightText">TRANSPORT : '+ssTotalTransp.toFixed(2)+' €</div>'

		dialogContent += detailMatosStr;
	}
	else {
		dialogContent += "Il n'y a pas de matériel enregistré pour ce plan...<br /><br />"
					  +"Une erreur a dû survenir pendant la création de la liste du matériel. Merci de modifier le plan pour refaire la liste.<br /><br />Désolé !";
	}
	dialogContent += '</div>';

	$("#dialog").html(dialogContent);
	$('.bouton').button();
	// check des salaires pour autoriser 0
	checkSalaires();

	if (datas.levelAuth == true) {
		if (datas.resa == 'devis') {
			$( "#dialog" ).dialog({
				autoOpen: true, height: 550, width: 770, modal: true,
				buttons: {"Modifier"    : function() {window.location = '?go=modif_plan&plan='+datas.id ;},
						  "Confirmer"	: function() {confirmPlan(datas.id) ;},
						  "Supprimer"	: function() {supprPlan(datas.id) ;},
						  "Imprimer RECAP"	: function() {window.open('modals/plan_printRecap.php?&plan='+datas.id, 'RobertPrint', 'scrollbars=yes,menubar=yes,width=960,height=720,resizable=no,location=no,directories=no,status=no') ;},
						  "Fermer"	    : function() {$('#dialog').off('click', '#createNewDevis');$(this).dialog("close") ;}
				},
				title: dialogTitre
			});
		}
		else {
			$( "#dialog" ).dialog({
				autoOpen: true, height: 550, width: 770, modal: true,
				buttons: {"Modifier"    : function() {window.location = '?go=modif_plan&plan='+datas.id ;},
						  "Supprimer"	: function() {supprPlan(datas.id) ;},
						  "Imprimer RECAP"	: function() {window.open('modals/plan_printRecap.php?&plan='+datas.id, 'RobertPrint', 'scrollbars=yes,menubar=yes,width=960,height=720,resizable=no,location=no,directories=no,status=no') ;},
						  "Fermer"	    : function() {$('#dialog').off('click', '#createNewDevis');$(this).dialog("close") ;}
				},
				title: dialogTitre
			});
		}
	}
	else {
		$( "#dialog" ).dialog({
			autoOpen: true, height: 550, width: 770, modal: true,
			buttons: {"Imprimer RECAP" : function() {window.open('modals/plan_printRecap.php?&plan='+datas.id, 'RobertPrint', 'scrollbars=yes,menubar=yes,width=960,height=720,resizable=no,location=no,directories=no,status=no') ;},
					  "Fermer"   : function() {$(this).dialog("close");}},
			title: dialogTitre
		});
	}
}


//////////////////////////////////////////////////////////////// GESTION DU PLAN ///////////////////////////////////////////////////////////////////////////////////
// confirme un plan
function confirmPlan (id) {
	var ajaxRequest = "action=confirmPlan&ID="+id ;
	if (confirm('Confirmer le plan N° '+id+', pour en faire une réservation ?')) {
		$("#retourAjaxModalCentre").html("Confirmation... ...").show();
		AjaxJson ( ajaxRequest, "plans_actions", showConfirmPlanIcon );
	}
}
// rafraichi l'icone de résa
function showConfirmPlanIcon (retour) {
	if (retour.error == 'OK') {
		$('#planStatusIcon').attr('src', 'gfx/icones/icon-reservation.png');
		$('#plan_'+retour.idPlan).find('.ui-icon').removeClass('ui-icon-help').addClass('ui-icon-check');
		$("#retourAjaxModalCentre").html('Plan confirmé !');
		setTimeout('$("#retourAjaxModalCentre").hide(300)', 800);
	}
	else {
		$("#retourAjaxModalCentre").html(retour.error);
		setTimeout('$("#retourAjaxModalCentre").hide(300)', 1200);
	}
}

// Supprime un plan
function supprPlan (id) {
	var ajaxRequest = "action=delPlan&ID="+id ;
	if (confirm('Supprimer le plan N° '+id+', sûr ?')) {
		$("#retourAjaxModalCentre").html("Suppression... ...").show();
		AjaxJson ( ajaxRequest, "plans_actions", alerteErr );
	}
}

// RÉAFFICHAGE DES SOUS PLANS QUAND CLIC SUR LE BOUTON 'Détail Jours'
function detailsPlan () {
	$('#dialog').find('#detailPlanLeftDiv').html(detailsContent);
}



//////////////////////////////////////////////////////////////// GESTION DES DEVIS ///////////////////////////////////////////////////////////////////////////////////
function devisPlan (id) {
	$('#dialog').find('#detailPlanLeftDiv')
				.html('<div class="center gros margeTop5 leftText">'
						 +'<div class="inline mid center" style="width:175px;">'
							+'<button class="bouton" id="addFileBtn" onClick="openContrat('+id+')">Nouveau devis</button> '
						 +'</div>'
						 +'<div class="inline mid ui-state-disabled ui-corner-all shadowIn pad5 leftText micro marge10l" id="messageAjaxFileList" style="width:170px;">'
							+'Avant, spécifiez une remise si besoin (ci-contre ->)'
						 +'</div>'
						 +'<div class="ui-state-default ui-corner-all shadowOut  margeTop10 pad10 leftText mini" id="planFileList">'
							 +'Chargement des devis...'
						 +'</div>'
					  +'</div>');
	$('.bouton').button();
	var ajaxRequest = "action=showDevisFiles&idPlan="+id;
	AjaxJson(ajaxRequest, 'plans_actions', listPlanFiles, [id, 'devis']);
}

function checkSalaires () {
	var sals = $('#dialog').find('#totalSalaires').val();
	if (sals == '' || sals == 0 || sals >= 100)
		$('#dialog').find('#salairesTxt').removeClass('red');
	else $('#dialog').find('#salairesTxt').addClass('red');
}

function openContrat (idPlan) {
	if ($('#dialog').find('#salairesTxt').hasClass('red')) {
		alert('Vous devez renseigner le montant des salaires !');
		return;
	}
	$( "#dialogContrat" ).dialog({
			autoOpen: true, height: 550, width: 1000, modal: true,
			buttons: {"Créer DEVIS" : function() { addDevis(idPlan); $(this).dialog("close"); },
					  "Recharger défaut" : function() { reloadContrat(); },
					  "Enregistrer défaut" : function() { saveContrat(); },
					  "Annuler"     : function() { $(this).dialog("close"); }
			}
		});
}

function reloadContrat () {
	if (confirm('Recharger le contrat par défaut ?')) {
		$.get('fct/contrat_location.php', function(data) {
			$('#contratText').val(data);
		});
	}
}

function saveContrat () {
	var contrat = $('#contratText').val();
	var ajaxRequest = "action=saveContrat&contratTxt="+contrat;
	AjaxJson(ajaxRequest, 'plans_actions', alerteErr);
}


function addDevis (idPlan) {
	var remise = $('#remisePercent').val();
	var contrat = encodeURIComponent($('#contratText').val());
	var salaires = $('#totalSalaires').val();
	$('#messageAjaxFileList').html("Création d'un nouveau devis...").removeClass('ui-state-disabled').addClass('ui-state-error');
	$('#addFileBtn').addClass('ui-state-disabled');
	var ajaxRequest = "action=createDevis&id="+idPlan+"&remise="+remise+"&salaires="+salaires+"&contratTxt="+contrat;
	AjaxJson(ajaxRequest, 'plans_actions', refreshDevisPlan, idPlan);
}

function supprDevis (idPlan, fileName) {
	$('#messageAjaxFileList').html("Suppression du devis<br />"+fileName).removeClass('ui-state-disabled').addClass('ui-state-error');
	$('#addFileBtn').addClass('ui-state-disabled');
	var ajaxRequest = "action=supprDevis&idPlan="+idPlan+'&file='+fileName;
	AjaxJson(ajaxRequest, 'plans_actions', refreshDevisPlan, idPlan);
}

function refreshDevisPlan (retour, idPlan) {
	if (retour.error == 'OK') {
		devisPlan(idPlan);
	}
	else $('#messageAjaxFileList').html(retour.error);
}




/////////////////////////////////////////////////////////////// GESTION DES FACTURES /////////////////////////////////////////////////////////////////////////////////
function facturePlan (id) {
	$('#dialog').find('#detailPlanLeftDiv')
				.html('<div class="center gros margeTop5 leftText">'
						 +'<div class="inline mid center" style="width:175px;">'
							+'<button class="bouton" id="addFileBtn" onClick="addFacture('+id+')">Créer la facture</button> '
						 +'</div>'
						 +'<div class="inline mid ui-state-disabled ui-corner-all shadowIn pad5 leftText micro marge10l" id="messageAjaxFileList" style="width:170px;">'
							+'Avant, spécifiez une remise si besoin (ci-contre ->)'
						 +'</div>'
						 +'<div class="ui-state-default ui-corner-all shadowOut  margeTop10 pad10 leftText mini" id="planFileList">'
							 +'Chargement de la facture...'
						 +'</div>'
					  +'</div>');
	$('.bouton').button();
	var ajaxRequest = "action=showFactureFile&idPlan="+id;
	AjaxJson(ajaxRequest, 'plans_actions', listPlanFiles, [id, 'facture']);
}

function addFacture (idPlan) {
	var remise = $('#remisePercent').val();
	$('#messageAjaxFileList').html("Création de la facture...").removeClass('ui-state-disabled').addClass('ui-state-error');
	$('#addFileBtn').addClass('ui-state-disabled');
	var ajaxRequest = "action=createFacture&id="+idPlan+"&remise="+remise;
	AjaxJson(ajaxRequest, 'plans_actions', refreshFacturePlan, idPlan);
}

function supprFacture (idPlan, fileName) {
	$('#messageAjaxFileList').html("Suppression de la facture<br />"+fileName).removeClass('ui-state-disabled').addClass('ui-state-error');
	$('#addFileBtn').addClass('ui-state-disabled');
	var ajaxRequest = "action=supprFacture&idPlan="+idPlan;
	AjaxJson(ajaxRequest, 'plans_actions', refreshFacturePlan, idPlan);
}

function refreshFacturePlan (retour, idPlan) {
	if (retour.error == 'OK') {
		facturePlan(idPlan);
	}
	else $('#messageAjaxFileList').html(retour.error);
}




/////////////////////////////////////////////////////////////// GESTION DES FICHIERS DIVERS /////////////////////////////////////////////////////////////////////////////
function fichiersPlan (id) {
	$('#dialog').find('#detailPlanLeftDiv')
				.html('<div class="center gros margeTop5 leftText">'
						 +'<div id="addFileUploader"></div>'
						 +'<div class="ui-state-default ui-corner-all shadowOut margeTop10 pad10 leftText mini" id="planFileList">'
							 +'Chargement des fichiers...'
						 +'</div>'
					  +'</div>');
	var ajaxRequest = "action=showPlanFiles&idPlan="+id;
	AjaxJson(ajaxRequest, 'plans_actions', listPlanFiles, [id, 'fichier']);

	createUploader(id);
}


function supprFichier (idPlan, fileName) {
	$('#messageAjaxFileList').html("Suppression du fichier<br />"+fileName).removeClass('ui-state-disabled').addClass('ui-state-error');
	$('#addFileBtn').addClass('ui-state-disabled');
	var ajaxRequest = "action=supprFichier&idPlan="+idPlan+'&file='+encodeURIComponent(fileName);
	AjaxJson(ajaxRequest, 'plans_actions', refreshFichierPlan, idPlan);
}

function refreshFichierPlan (retour, idPlan) {
	if (retour.error == 'OK') {
		fichiersPlan(idPlan);
	}
	else alert(retour.error);
}



// AFFICHAGE DES FICHIERS D'UN PLAN (devis, facture ou autre)
function listPlanFiles (retour, idPlan, type) {
	$('#dialog').find('#planFileList').html('');

	$.each(retour, function(i, entree) {
		if (entree == 'NO_FILE') {
			$('#dialog').find('#planFileList').append('<div class="pad5 petit">Pas de '+type+' pour ce plan.</div>');
		}
		else {
			var fileName = entree.file;
			if (type == 'devis') {
				var totalD	 = parseFloat(entree.total);
				$('#dialog').find('#planFileList')
							.append('<div style="float:right;" class="micro">'
										+'<button class="bouton" onClick="supprDevis('+idPlan+', \''+addslashes(fileName)+'\')" title="SUPPRIMER ce devis">'
											+'<span class="ui-icon ui-icon-trash"></span>'
										+'</button>'
									+'</div>'
									+'<div class="inline top ui-state-highlight ui-corner-all pad3 mini" style="margin-top:2px;">'
										+'<a href="fct/downloader.php?dir=PlanDevis&planID='+idPlan+'&file='+encodeURIComponent(fileName)+'" title="CLIC pour télécharger">'
											+'<b>'+fileName+'</b>'+' <i class="petit">('+totalD+' €)</i>'
										+'</a>'
									+'</div>'
									+'<div style="clear: both; margin-bottom: 2px;"></div>');
				$('#remiseMontant').focus().val(totalD.toFixed(2)).blur();
			}
			else if (type == 'facture') {
				$('#dialog').find('#planFileList')
							.append('<div style="float:right;" class="micro">'
										+'<button class="bouton" onClick="supprFacture('+idPlan+', \''+addslashes(fileName)+'\')" title="SUPPRIMER la facture">'
											+'<span class="ui-icon ui-icon-trash"></span>'
										+'</button>'
									+'</div>'
									+'<div class="inline top ui-state-highlight ui-corner-all pad3 mini" style="margin-top:2px;">'
										+'<a href="fct/downloader.php?dir=PlanFacture&planID='+idPlan+'&file='+encodeURIComponent(fileName)+'" title="CLIC pour télécharger">'
											+'<b>'+fileName+'</b>'
										+'</a>'
									+'</div>'
									+'<div style="clear: both; margin-bottom: 2px;"></div>');
			}
			else {
				$('#dialog').find('#planFileList')
							.append('<div style="float:right;" class="micro">'
										+'<button class="bouton" onClick="supprFichier('+idPlan+', \''+addslashes(fileName)+'\')" title="SUPPRIMER ce fichier">'
											+'<span class="ui-icon ui-icon-trash"></span>'
										+'</button>'
									+'</div>'
									+'<div class="inline top ui-state-highlight ui-corner-all pad3 mini" style="margin-top:2px;">'
										+'<a href="fct/downloader.php?dir=PlanFichier&planID='+idPlan+'&file='+encodeURIComponent(fileName)+'" title="CLIC pour télécharger">'
											+'<b>'+fileName+'</b>'
										+'</a>'
									+'</div>'
									+'<div style="clear: both; margin-bottom: 2px;"></div>');
			}
		}
	});
	$('.bouton').button();
}

// Ajoute le fichier venant d'être uploadé à la liste
function addUploadedFile ( idPlan, val ) {
	if ($('#dialog').find('#planFileList').length == 0 )
		$('#dialog').find('#planFileList').html('');
	var fileLink = encodeURIComponent(val);
	$('#dialog').find('#planFileList')
				.append( '<div style="float:right;" class="micro">'
								+'<button class="bouton" onClick="supprFichier('+idPlan+', \''+addslashes(val)+'\')" title="SUPPRIMER ce fichier">'
									+'<span class="ui-icon ui-icon-trash"></span>'
								+'</button>'
							+'</div>'
							+'<div class="inline top ui-state-highlight ui-corner-all pad3 mini" style="margin-top:2px;">'
								+'<a href="fct/downloader.php?dir=PlanFichier&planID='+idPlan+'&file='+fileLink+'" title="CLIC pour télécharger">'
									+'<b>'+val+'</b>'
								+'</a>'
							+'</div>'
							+'<div style="clear: both; margin-bottom: 2px;"></div>' );
	$('.bouton').button();
}




function createUploader (idPlan) {

	uploader = new qq.FileUploader({
		element: document.getElementById('addFileUploader'),
		action: 'fct/uploader.php?dataType=plan&folder='+idPlan,
		debug: false,
        sizeLimit: 62914560,
        minSizeLimit: 0,
		allowedExtensions: ["jpg", "jpeg", "pdf", "png", "bmp"],
		onComplete: function(id, fileName, responseJSON) {
			$(".qq-upload-success > .qq-upload-file").each( function (ind, obj){
					var name = $(this).html();
					name = name.replace(/&amp;/g, '&');
					if ( name == fileName )
						addUploadedFile ( idPlan, fileName );
					$(obj).parent('.uploading_file').remove();
				});
		},

		onProgress: function(id, fileName, loaded, total) {
			var percent = parseInt ( loaded * 100 / total ) ;
			$( ".progressbar[id$='prog_"+id+"']" ).progressbar({value: percent});
		},

        template: '<div class="qq-uploader">' +
						'<div class="qq-upload-drop-area"><span>Glissez des fichiers ici</span></div>' +
						'<div class="inline mid ui-state-disabled ui-corner-all shadowIn pad5 leftText micro marge10l" id="messageAjaxFileList" style="float:right; width:170px; margin-top:2px;">'+
							'Types de fichiers permis :<br /> .JPG, .PNG, .PDF'+
						 '</div>'+
						'<div class="inline mid qq-upload-button" style="width:170px; padding: 1px 5px;">'+
							'<button class="bouton">Ajouter fichiers</button>'+
						'</div>' +
						'<ul class="qq-upload-list"></ul>' +
					'</div>',

        fileTemplate: '<div class="uploading_file mini">' +
							'<span class="qq-upload-file"></span>' +
							'<span class="qq-upload-spinner" style="width:150px;"><div class="progressbar" style="height:13px;" ></div></span>' +
							'<span class="qq-upload-size"></span>' +
							'<a class="qq-upload-cancel" href="#">Annuler</a>' +
							'<span class="qq-upload-failed-text">Erreur</span>' +
						'</div>',

		messages: {
			typeError: "{file} Extension de fichier non permise. Utilisez des fichiers : {extensions}.",
            sizeError: "{file} : Taille de fichier limité à  {sizeLimit}.",
            minSizeError: "{file} est trop petit, la taille minimum est de {minSizeLimit}.",
            emptyError: "{file} est vide !",
            onLeave: "Des fichiers sont en cours de téléchargement. Si vous quittez la page, ils seront corrompus !"
		},
		showMessage: function(message){
            $('#messageAjaxFileList').html(message).addClass('ui-state-error');
		}
	});
}


function attachProgressBar ( id , fileName ){
   $(".qq-upload-file").each( function ( ind, obj  ){
		if ( $(obj).html() == fileName ){
			$(obj).parent('.uploading_file').find('.progressbar').attr('id', 'prog_'+id ) ;
		}
	});
}
