
var separator	= ' ';
var matosIdQte	= {} ;
var tekosIds	= [] ;

// Retour au calendrier pendant l'ajout de plan (annule tout)
function retourCalendar () {
//	if (mode_ajout == false)
		window.location = '?go=calendrier';
//	else window.close();
}


$(function() {

/// Init de l'accordéon des packs
	$("#packsHolder").accordion({header: '.packPik', disabled: true, active: false, alwaysOpen: false, autoHeight: false, collapsible: true, animated: true});

/// ouverture du bon accordion (pour éviter l'ouverture sur boutons + et -)
	$('.accordionOpen').click(function() {
		var idToOpen = $(this).parents('.packPik').attr('id');
		$("#packsHolder").accordion( {change: function(event, ui) {
											$("#packsHolder").accordion("option", "disabled", true);
											$("#packsHolder").removeClass('ui-state-disabled');
											$("#packsHolder").find('div').removeClass('ui-state-disabled');
											$("#packsHolder").find('div').removeClass('ui-state-hover');
											$("#packsHolder").find('div').removeClass('ui-state-focus');}
		});
		$("#packsHolder").accordion( "activate", false );
		$("#packsHolder").accordion( "enable" );
		$("#packsHolder").accordion( "activate", $("#"+idToOpen) );
	});


/// SWITCH du matos, affichage du détail ou des packs
	$('#togglePacksMatos').click(function () {
		var actuel = $(this).children().html();
		if (actuel == 'MATÉRIEL au détail') {
			$(this).children().html('PACKS de matériel');
			$(this).attr('title', 'voir le matériel en pack pour gagner du temps');
			$('#packsHolder').hide();
			$('#matosHolder').show();
			$('#messHelpMatos').html('Bouton "+" pour ajouter un matériel,<br /> tapez ensuite la quantité voulue, ou "-" pour l\'enlever.');
		}
		else {
			$(this).children().html('MATÉRIEL au détail');
			$(this).attr('title', 'voir le matériel en détail pour être plus précis');
			$('#matosHolder').hide();
			$('#packsHolder').show();
			$('#messHelpMatos').html('Bouton "+" pour ajouter un pack,<br /> Bouton "-" pour en enlever un.');
		}

	});


/// ajout d'un matos à la volée, vite fait
	$('#add_Matos_Rapide').click(function (){
		$('#addMatosModal').dialog({
			autoOpen: true,
			width : 790,
			height: 360,
			title: 'Ajouter un matériel' ,
			modal: true,
			buttons: {'Ok'      : function() { if ( ! addMatosToBDD () ) return ; $(this).dialog('close'); },
					  'Annuler' : function() { $(this).dialog('close'); }
			},
			close  : function() { $(".addMatosInput").val('');  }
		});
	});
	// Si click sur matos externe, change l'info de date par "chez qui ?"
	$('.externeBox').click(function () {
		if ($(this).attr('checked') == 'checked') {
			$('#dateAchatDiv').hide();
			$('#chezQuiDiv').show();
		}
		else {
			$('#dateAchatDiv').show();
			$('#chezQuiDiv').hide();
		}
	});


///// Filtrage du matos et des packs par catégorie VERSION 2 (additif)
	$('.filtreMatos').click(function() {
		$('.matosPik').hide(); $('.packPik').hide(); $('.matosLine').hide();

		if ( $(this).hasClass('ui-state-error') )
			 $(this).removeClass('ui-state-error');
		else $(this).addClass('ui-state-error');

		var stillFiltred = false;
		$('.filtreMatos').each(function(i, obj){
			$('.sousCategLine').show();
			var categ = $(obj).attr('id');
			if ($(obj).hasClass('ui-state-error')) {
				if (categ == 'int-ext') {
					$('.matosInterne').hide();
					$('.matosExterne').show(10, function(){ refreshSousCatLine(); });
				}
				else $('.cat-'+categ).show(10, function(){ refreshSousCatLine(); });
				stillFiltred = true;
			}
			else $('.cat-'+categ).hide(10, function(){ refreshSousCatLine(); });
		});

		if (stillFiltred == false) {
			$('.sousCategLine').show();
			$('.matosPik').show();
			$('.packPik').show();
			$('.matosLine').show();
		}
	});


///// Filtrage du matos et des packs par catégorie VERSION 1 (exclusif)
//	$('.filtreMatos').click(function() {
//		var categ = $(this).attr('id');
//		$('.matosPik').hide();
//		$('.packPik').hide();
//		$('.matosLine').hide();
//		if ($(this).hasClass('ui-state-error')) {
//			$('.filtreMatos').removeClass('ui-state-error');
//			$('.sousCategLine').show();
//			$('.matosPik').show();
//			$('.packPik').show();
//		}
//		else {
//			$('.filtreMatos').removeClass('ui-state-error');
//			$('.sousCategLine').show();
//			if (categ == 'int-ext') {
//				$('.matosInterne').hide();
//				$('.matosExterne').show(10, function(){
//					$('.sousCategLine').each(function(){
//						if ($(this).next().attr('style') == 'display: none;')
//							$(this).hide();
//					});
//				});
//			}
//			else {
//				$('.cat-'+categ).show(10, function(){
//					$('.sousCategLine').each(function(){
//						if ($(this).next().attr('style') == 'display: none;')
//							$(this).hide();
//					});
//				});
//			}
//			$(this).addClass('ui-state-error');
//		}
//	});


/// Click sur le plus ou sur la croix pour ajouter un matos à la liste
	//$(".matos_plus").click(function() {
	$("#matosHolder").on ( 'click', '.matos_plus' , function() {
		var name	  = $(this).parents('.matos_name').html();
		var isExterne = $(this).parents(".matosPik").hasClass('matosExterne');
		var id = 0;
		if( $(this).children('button').hasClass('plus') == true ) {
			$(this).parents('.matosPik').addClass('ui-state-highlight');
			$(this).parent('.matosDispo').find('.qtePikInput').removeClass("hide").addClass('show');
			$(this).parents().children('.qtePik').addClass("padV10");
			$(this).children('button').removeClass('plus').addClass('moins').children('span').children('span').removeClass('ui-icon-plusthick').addClass('ui-icon-minusthick');
			$(this).parents().children('.qtePik').children('.qtePikInput').focus();
			id   = parseInt($(this).parents().children('.qtePik').attr('id'), 10);
			matosIdQte[id] = 1 ;
		}
		else {
			$(this).parent('.matosDispo').find('.qtePikInput').removeClass('show').addClass('hide');
			$(this).children('button').removeClass('moins').addClass('plus').children('span').children('span').removeClass('ui-icon-minusthick').addClass('ui-icon-plusthick');
			$(this).parents().children('.qtePik').removeClass("padV10");
			$(this).parents('.matosPik').removeClass('ui-state-highlight');
			id   = parseInt($(this).parents().children('.qtePik').attr('id'), 10);
			delete matosIdQte[id];

		}
		qteMatos_update(id);
		prixTotal();
		aLouer();

		if (isExterne == false)
			recalcDispoPacks();
	});


/// après avoir tapé une quantité de matos
	$("#matosHolder").on ( 'blur', ".qtePikInput", function() {
		var id		= parseInt($(this).parents('.qtePik').attr('id'), 10) ;
		var newQte	= $(this).val();
		matosIdQte[id]  = parseInt(newQte, 10) ;
		qteMatos_update(id);
		prixTotal();
		aLouer();
		recalcDispoPacks();
	});


/// click sur bouton plus d'un pack
	$(".pack_plus").click ( function (){
		var plusmoins = $(this).attr('id');
		var maximumpack  = parseInt( $(this).parents(".packPik").find('.qteDispo_MAX').html(), 10 );
		var currentpack  = parseInt( $(this).parents(".packPik").find('.qteDispo_QTE').html(), 10 );
		var isExterne	 = $(this).parents(".packPik").hasClass('matosExterne');

		var idPack = $(this).parents(".packPik").attr("id");
		idPack = idPack.substr(5) ;
		var PackItems = $("#packDetail-" + idPack).children(".packItem") ;
		var currentVoulu = parseInt( $('#qtePik-'+idPack).html(), 10 );

		if ( isExterne == false ) {
			if ( currentpack >= maximumpack && plusmoins == 'moins' ) return;
		}
		if ( currentVoulu <= 0 && plusmoins == 'moins') return;

		$(PackItems).each ( function (ind, obj) {
			var idMatos = $(obj).attr('id') ;
			idMatos = idMatos.substr(3);
			var qteAdd = $(obj).find(".need").html();
			qteAdd = parseInt( qteAdd, 10 ) ;
			if ( plusmoins == "moins" ) qteAdd = 0 - qteAdd ;
			addMatos ( idMatos , qteAdd ) ;
		});
		prixTotal();
		aLouer();

		var ajoute = 1;
		if ( plusmoins == "moins" ) ajoute = -1;

		if (isExterne == false) {
			recalcDispoPacks();
		}
		else {
			var voulu = currentVoulu + ajoute;
			$('#qtePik-'+idPack).html(voulu);
			if (voulu <= 0)
				$('#qtePik-'+idPack).hide();
			else $('#qtePik-'+idPack).show();
		}
	});


});
//// FIN DU DOCUMENT READY


///////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////// FONCTIONS D'UPDATE DE SÉLECTION MATOS //////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////

function refreshSousCatLine () {
	$('.sousCategLine').each(function(){
		if ($(this).next().attr('style') == 'display: none;')
			$(this).hide();
	});
}


function checkBenefExist (benefName) {
	if (benefName == '') return;
	if (autoCompleteBENEF.indexOf(benefName) == -1) {
		if (confirm("Le bénéficiaire n'existe pas encore dans la base de données. \n\nvoulez vous le renseigner maintenant ?")) {
			$('#addBenefDialog').load('modals/benef_add_struct.php', function() {
				$('#newStrucLabel').val(benefName);
				$('#addStruct').hide();
			});
			$('#addBenefDialog').dialog({
				autoOpen: true, height: 400, width: 700, modal: true,
				buttons: {"Enregistrer" : function(s) {
											try {$('#addStruct').click();}
											catch(err){if(err=='Incomplet') return;}
											$(this).dialog("close");
											$('#retourAjax').ajaxStop(function(){
												$(this).children('.bouton').hide();
											});
											setTimeout("clearDiv('retourAjax')", 2000);
										},
						  "Bah, plus tard"   : function() {$(this).dialog("close");}
				}
			});
		}
	}
	else return;
}


function displayTekosDispo (data) {
	jQuery.each( data, function (id, dispo) {
		var iconeDispo = "<img src='gfx/icones/icon-"+dispo+".png' alt='"+dispo+"' />";
		$("#modalTekos").find("#tek-"+id).children(".tekosDispo").html(iconeDispo);
	});
}


function displayTekosMatos (data) {
	if (data.tekos.length < 1) { alert('La liste des techniciens est vide.\n\nAjoutez des techniciens pour pouvoir continuer...'); window.location.href = "index.php?go=gens"; }
	if (data.matos.length < 1) { alert('La liste du matériel est vide.\n\nAjoutez au moins un matériel pour pouvoir continuer...'); window.location.href = "index.php?go=materiel"; }
//	if (data.packs.length < 1) { alert('La liste des packs de matériel est vide.\n\nVous pouvez utiliser les packs pour ajouter des listes de matériel rapidement.'); }

	$("#periode").html( '<b>'+data.periodeStart +'</b> au <b>'+ data.periodeEnd +'</b>' );

	$('#displayNbPlanSimult').html('');
	if (mode_ajout == false) data.nbPlansPeriode --;
	if ( data.nbPlansPeriode > 0 ) {
		var plurielPlan = '';
		if ( data.nbPlansPeriode > 1 )
			plurielPlan = 's';
		$('#displayNbPlanSimult').html( "Attention ! Déjà <b>" + data.nbPlansPeriode + " plan" + plurielPlan + "</b> en même temps dans cette période !");
	}

	jQuery.each( data.tekos, function (i, val) {
		var iconeDispo = '';
		if (val.iconeTekos == 'option') {
			iconeDispo = "<img src='gfx/icones/icon-"+val.iconeTekos+".png' alt='"+val.iconeTekos+"' popup='Déjà en option, "+val.busyTekosPlan+"' />";
		}
		else if (val.iconeTekos == 'partiel' || val.iconeTekos == 'optionPartiel') {
			iconeDispo = "<img src='gfx/icones/icon-"+val.iconeTekos+".png' alt='"+val.iconeTekos+"' popup='Technicien déja pris :<br />";
			jQuery.each( val.busyTekosDay, function (iB, valB) {
				iconeDispo += valB+"<br />";
			});
			iconeDispo += "' />";
		}
		else {
			iconeDispo = "<img src='gfx/icones/icon-"+val.iconeTekos+".png' alt='"+val.iconeTekos+"' />";
		}

		$("#tek-"+val.idtek).children(".tekosDispo").html(iconeDispo);
	});

	jQuery.each( data.matos, function (i, valMatos) {
		var idMatos	  = valMatos.idMatos;
		var qtePanne  = valMatos.panne;
		var qteTotale = valMatos.Qtotale;
		var qteDispo  = valMatos.Qdispo;
		var qteAttente= valMatos.Qattente;
		var infosPlans= valMatos.infoPlans;		// Array des infos des plans ou c'est pris
		var isFullParc= valMatos.fullParc;
		qteTotale -= qtePanne;
		qteDispo  -= qtePanne;

		$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_total").html ( qteTotale );
		if (matosIdQte[idMatos] == undefined)
			$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_update").html( qteDispo );
		else $("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_update").html( qteTotale - matosIdQte[idMatos] );
		$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_onload").html( qteDispo );

		if (isFullParc == 'false' ) {
			var messagePopup = '';
			$.each(infosPlans, function(i, info){
				if (info.qteA > 0)
					messagePopup += '<b>'+info.qteA+'</b> en attente sur <b>'+info.titre+'</b><br />(géré par <b>'+info.owner+'</b>)<br />';
				else
					messagePopup += '<b>'+info.qteC+'</b> déjà pris sur <b>'+info.titre+'</b><br />(géré par <b>'+info.owner+'</b>)<br />';
			});
			if ( qteAttente != 0 )
				 messagePopup += '<b>total en attente : ' + qteAttente+'</b>';

			$("#matos-"+idMatos).children(".matosDispo").children(".qteDispo").attr('popup', messagePopup);
		}

		if (qtePanne != 0 ) {
			var panneTxt = "<span class='mini red'>(+ " + qtePanne + " en pannne !)</span>";
			$("#matos-"+idMatos).children(".matosDispo").children(".qtePanne").html( panneTxt );
		}

		if (qteDispo <= 0)
			$("#matos-"+idMatos).addClass('ui-state-error');
		else
			$("#matos-"+idMatos).removeClass('ui-state-error');
	});

	jQuery.each( data.packs, function (i, valPack) {
		var idPack		= valPack.id;
		var refPack		= valPack.ref;
		var detailPack	= valPack.detail;
		var dispoPack	= valPack.qteMatDispo;
		var qtePack		= valPack.QTE;

		$('#pack-'+idPack).children(".packDispo").find('.qteDispo_QTE').html(qtePack);
		$('#pack-'+idPack).children(".packDispo").find('.qteDispo_MAX').html(qtePack);

		$.each( dispoPack, function (id, qte ) {
			var need = $('#packDetail-'+idPack).children('#pD-'+id).children('div').children('.need').html();
			need = parseInt(need, 10);
			if (mode_ajout == false) {
				var qteResteDepart = parseInt($('#matos-'+id).find('.qteDispo_update').html(), 10);
				qte = qteResteDepart;
			}
			var qteTxt = qte;
			if (need > qte) {
				$('#packDetail-'+idPack).children('#pD-'+id).addClass('ui-state-error');
				qteTxt += ' (pas assez)';
			}
			else $('#packDetail-'+idPack).children('#pD-'+id).removeClass('ui-state-error');
			$('#packDetail-'+idPack).children('#pD-'+id).children('.dispo').html(qteTxt);
		});
	});
	recalcDispoPacks();
	initToolTip('#etape-2', -100);
	initToolTip('#etape-3', -120);
}



///////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////// FONCTIONS D'UPDATE DE SÉLECTION MATOS //////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////


function addMatos ( id, qte ) {
	if ( matosIdQte[id] == undefined ) matosIdQte[id] = 0 ;
	matosIdQte[id] = parseInt( matosIdQte[id], 10 ) + parseInt( qte, 10 )   ;
	qteMatos_update( id );
}


function qteMatos_update ( id ) {
	var max  =  $("#matos-" + id).children(".matosDispo").find(".qteDispo_onload").html()
	max = parseInt ( max, 10 );
	var pu = $("#matos-" + id).find(".matos_PU").html() ;
	pu = parseFloat ( pu );
	var qte = parseInt ( matosIdQte[id], 10 );
	if (isNaN(qte)) qte = 0;
	var ptotal =  pu * qte  ;

	$("#matos-" + id).removeClass('ui-state-highlight');
	$("#matos-" + id).find(".matos_PRICE").html ( ptotal );
	$("#matos-" + id).children(".matosDispo").find(".qtePikInput").val( qte );
	$("#matos-" + id).children(".matosDispo").find(".qteDispo_update").html( max - qte );
	if (qte <= 0) {
		$("#matos-" + id).children(".matosDispo").find(".qtePikInput").hide();
		delete matosIdQte[id];
		$("#matos-" + id).children(".matosDispo").find(".qtePik").removeClass('padV10');
		$("#matos-" + id).find('.matos_plus').children('button').removeClass('moins').addClass('plus').find('.ui-icon').removeClass('ui-icon-minusthick').addClass('ui-icon-plusthick');
	}
	else {
		$("#matos-" + id).addClass('ui-state-highlight');
		$("#matos-" + id).children(".matosDispo").find(".qtePikInput").show();
		$("#matos-" + id).children(".matosDispo").find(".qtePik").addClass('padV10');
		$("#matos-" + id).find('.matos_plus').children('button').removeClass('plus').addClass('moins').find('.ui-icon').removeClass('ui-icon-plusthick').addClass('ui-icon-minusthick');
	}

	$(".pD-" + id).each ( function (ind , obj ){
		var reste = max - matosIdQte[id];
		if (isNaN(reste)) reste = max;
		$(obj).children(".dispo").html( reste );
	});

	if (mode_ajout == true)
		refreshEtapesBtns(1);
}


function prixTotal () {
	var divTotal	= "#bigTotal" ;
	var divSsTotaux = "#sousTotal";
	var matosPrix	= $(".matosPik").find(".matos_PRICE");
	var prices		= [] ;

	$(matosPrix).each ( function (ind, obj) {
		var categ = $(obj).parents(".matosPik").children(".matos_categ").children("img").attr("alt");
		var tmpPrice = parseFloat( $(obj).html() );
		if ( ! isNaN(tmpPrice) ) {
			if (prices[categ] == undefined) prices[categ] = 0 ;
			prices[categ] += tmpPrice ;
		}
	});
	$(divSsTotaux).html('');
	var total = 0 ;
	for ( cat in prices ) {
		$(divSsTotaux).append( cat +' : ' + prices[cat] + '€<br />' );
		total += prices[cat] ;
	}
	$(divTotal).html('TOTAL : ' + total + '€' );
}


function aLouer (){
	var divOutput = "#extAlouer" ;
	var matos = $(".matosPik");
	var listToRent = '';
	$(divOutput).html('Rien, pour le moment...');

	$(matos).each ( function (ind, obj){
		var qteOnload = parseInt ( $(obj).find(".qteDispo_onload").html(), 10 );
		var id = $(obj).attr("id");
		id = id.substr(6);
		id = parseInt ( id, 10 );
		var qteAsked = matosIdQte[id] ;
		var moins = qteOnload - qteAsked ;
		var externe = $(obj).children('.matos_name').attr('ext');
		var name = $(obj).children(".matos_name").html();

		if ( moins < 0 && ! isNaN(moins) && externe == '0' ) {
			if ( qteOnload < 0 ) moins = moins - qteOnload ;
			else moins = qteOnload - qteAsked ;
			listToRent += '<div class="margeTop5" style="border-bottom: 1px dashed #666;">' + (moins * -1 ) + ' x ' + name + '</div>' ;
		}
		else if (externe == '1' && qteAsked > 0) {
			listToRent += '<div class="margeTop5" style="border-bottom: 1px dashed #666;">' + qteAsked + ' x ' + name + '</div>';
		}
	});
	if (listToRent != '')
		$(divOutput).html(listToRent);
}


function recalcDispoPacks () {
	$('.packPik').each( function(i, pack) {
		var id = $(pack).attr('id');
		var idPack = id.substr(5);
		$('#qtePik-'+idPack).html('');
//		if ($(pack).hasClass('matosExterne') == true) return true;

		var itemsObj = $('#packDetail-'+idPack).children('.packItem');
		qteOK = 100000;
		var nbItemsInPack = 0;
		var nbPackPickable = {};
		$(itemsObj).each(function(i) {
			var idMatos = $(this).attr('id');
			idMatos = idMatos.substr(3);

			nbItemsInPack++;
			var need  = parseInt($(this).find('.need').html(), 10);
			var dispo = parseInt($(this).find('.dispo').html(), 10);
			if (isNaN(dispo)) dispo = 0;
			var voulu = parseInt($('#matos-'+idMatos).find('.qtePikInput').val(), 10);
			var qtePossible = Math.floor(dispo / need);
//			$('#qtePik-'+idPack).append('<br />matos'+idMatos+' need='+need+' want='+voulu+' dispo='+dispo+';');
			if (qtePossible < qteOK)
				qteOK = qtePossible;
			if (voulu > 0) {
				var vouluInPack = Math.floor(voulu / need) ;
				nbPackPickable[idMatos] = vouluInPack;
			}
			else {
				$('#qtePik-'+idPack).html('0');
				$('#qtePik-'+idPack).hide();
			}
		});

		$(pack).find('.qteDispo_QTE').html(qteOK);

		var nbItemsWantInPack = Object.keys(nbPackPickable).length;
		var controlQteWanted = [];
//		$('#qtePik-'+idPack).append('Items voulu in pack = '+ nbItemsWantInPack + ', Items in Pack : '+nbItemsInPack+'<br />');$('#qtePik-'+idPack).show();
		if (nbItemsWantInPack == nbItemsInPack) {			// Si même nombre d'item dans le pack que d'item pris (qui ont un "pick")
			$.each(nbPackPickable, function (id, qte) {
				if (qte >= 1)
					controlQteWanted.push(qte);
			});
			if (controlQteWanted.length == nbItemsInPack) {
				var minPossiblePacks = Math.min.apply( Math, controlQteWanted );
				$('#qtePik-'+idPack).html(minPossiblePacks);
				$('#qtePik-'+idPack).show();
			}
			else $('#qtePik-'+idPack).hide();
		}
	});
}


function resetTekosToSP (timeStamp, tekosListHTML ) {
	$(".spInfos#"+ timeStamp).find(".tekosSPlist").html(tekosListHTML);
	$("#modalTekos").dialog('close');
}

/// Modifie la chaine Tekos pour UN sous plan
function addTekosToSP(timeStamp, tekosList){
	var teks = tekosList.join(' ');
	var strAjax = 'action=SousPlanModifTek&spDate='+timeStamp+'&tekList='+teks;
	if (mode_ajout == true) strAjax += '&typeSess=plan_add';
	else strAjax += '&typeSess=plan_mod';
	AjaxFct ( strAjax, "plans_actions", false, 'retourAjax');
	$("#modalTekos").dialog('close');
}



function addMatosToBDD(){
		var label		= encodeURIComponent($('#newMatosLabel').val()) ;
		var ref			= $('#newMatosRef').val() ;
		var categ		= $('#newMatosCateg').val() ;
		var Souscateg	= $('#newMatosSousCateg').val() ;
		var Qtotale		= $('#newMatosQtotale').val() ;
		var dateAchat	= $('#newMatosDateAchat').val() ;
		var ownerExt	= $('#newMatosExtOwner').val() ;
		var tarifLoc	= $('#newMatosTarifLoc').val() ;
		var valRemp		= $('#newMatosValRemp').val() ;
		var remarque	= encodeURIComponent($('#newMatosRemark').val()) ;
		var externe		= 0;
		if ($('#newMatosExterne').attr('checked')) externe	= 1 ;

		if (label == '' || ref == '' || categ == '' || Qtotale == '' || tarifLoc == '' || valRemp == '' ) {
			alert('Vous devez remplir tous les champs marqués d\'une étoile !');
			return false;
		}
		var strAjax = 'action=addMatosJson&label='+label+'&ref='+ref
					 +'&categorie='+categ+'&sousCateg='+Souscateg
					 +'&Qtotale='+Qtotale+'&dateAchat='+dateAchat
					 +'&tarifLoc='+tarifLoc+'&valRemp='+valRemp
					 +'&externe='+externe+'&ownerExt='+ownerExt
					 +'&remarque='+remarque ;
		AjaxJson(strAjax, 'matos_actions', matos_list_detail);
		return true;
}


function matos_list_detail( retour ){
	if ( retour.success != 'SUCCESS' ) { alert (retour.success) ;  return; }

	retour = retour.matos ;
	var externeIcon = '';
	var externeClass = '';
	var externeHideDispo = '';
	if ( retour.externe == '1'){
		externeIcon = "<img src='gfx/icones/matosExterne.png' alt='externe' popup='matériel externe au parc !<br />A louer chez <b>"+ retour.ownerExt +"</b>' />";
		externeClass = "matosExterne"  ;
		externeHideDispo = "class='hide'"  ;
	}

	var newMatos= "<div id='matos-"+ retour.id +"' class='ui-state-default matosPik cat-"+ retour.categorie + " " +externeClass+" pad3'>"
						+" <div class='inline mid rightText' style='width:100px; '>"
							+" <span class='ui-state-disabled'>DETAIL</span>"
						+" </div>"
						+" <div class='inline mid rightText' style='width:100px;'>"
							+ externeIcon
						+" </div>"
						+" <div class='inline mid matos_categ rightText' style='width:100px;'>"
							+" <img src='gfx/icones/categ-"+ retour.categorie +".png' alt='"+ retour.categorie +"' title='catégorie "+ retour.categorie +"' class='marge30l' />"
						+" </div>"
						+" <div class='inline mid quart leftText pad30L matos_name' ext='"+ retour.externe +"'>"+ retour.ref +"</div>"
						+" <div class='inline mid quart matosDispo rightText mini' style='width:200px;'>"
							+" <div class='inline mid qteDispo'>"
								+" <div><span>Total : </span><span class='qteDispo_total'> "+ retour.Qtotale + " </span></div>"
								+" <div "+ externeHideDispo +"><span>Dispo : </span><span class='qteDispo_update'> "+ retour.Qtotale + " </span></div>"
								+" <div class='hide'><span class='qteDispo_onload'> "+ retour.Qtotale + " </span></div>"
								+" <div class='qtePanne center'></div>"
							+" </div>"
							+" <div class='inline mid qtePik bordFin bordSection' id='"+ retour.id+"'><input type='text' class='qtePikInput hide' size='2' value='0' /></div>"
							+" <div class='inline mid matos_plus'><button class='bouton plus'><span class='ui-icon ui-icon-plusthick'></span></button></div>"
						+" </div>"
						+" <div class='inline mid quart'>"
							+" <div class='inline mid demi petit rightText'><span class='matos_PU'>"+ retour.tarifLoc +" €</span></div>"
							+" <div class='inline mid demi gros'> = <span class='matos_PRICE'>0</span> €</div>"
						+" </div>"
				 +" </div>" ;

	$('#matosHolder').find('.sousCategLine[idSsCat*="'+retour.sousCateg+'"]').show().after( newMatos ) ;
	$('.bouton').button();
}

