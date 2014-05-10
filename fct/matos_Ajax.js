
$(function() {

	///// Filtrage du matos et des packs par catégorie VERSION 2 (additif)
	$('#filtresDiv').off('click', '.filtre');
	$('#filtresDiv').on('click', '.filtre', function() {
		$('.matosLine').hide();

		if ( $(this).hasClass('ui-state-error') )
			 $(this).removeClass('ui-state-error');
		else $(this).addClass('ui-state-error');

		var stillFiltred = false;
		var filtredExt	 = false;
		$('.filtre').each(function(i, obj){
			$('.sousCategLine').show();
			var categ = $(obj).attr('id');
			if ($(obj).hasClass('ui-state-error')) {
				if (categ == 'int-ext') {
					filtredExt = true;
				}
				else {
					$('.cat-'+categ).show(10, function(){refreshSousCatLine();});
					stillFiltred = true;
				}
			}
			else $('.cat-'+categ).hide(10, function(){refreshSousCatLine();});
		});

		if (stillFiltred == false) {
			$('.sousCategLine').show();
			$('.matosPik').show();
			$('.packPik').show();
			$('.matosLine').show();
			$('.matosExterne').hide();
		}

		if (filtredExt == true) {
			$('.matosInterne').hide();
			if (stillFiltred == false) {
				$('.matosExterne').show(10, function(){refreshSousCatLine();});
			}
		}
		else {
			$('.matosExterne').hide();
		}
	});


	// sélection d'un matos
	$('.selectMatos').click(function() {
		var idSel  = $(this).attr('id');
		var nomSel = $(this).attr('nom');
		var ajaxStr  = 'action=select&id='+idSel;
		$('#nomMatosModif').html(nomSel);
		$('#listingPage').animate( {bottom: "255px", opacity: ".5"}, transition );
		$('#modifieurPage').show(transition);
		AjaxJson(ajaxStr, 'matos_actions', displaySelMatos);
	});


	// modification d'un matos
	$('#modifieurPage').on('click', '.modif', function () {
		var idMatos		= $('#modMatosId').val();
		var label		= encodeURIComponent($('#modMatosLabel').val());
		var ref			= $('#modMatosRef').val();
		var categ		= $('#modMatosCateg').val();
		var sscateg		= $('#modMatosSousCateg').val();
		var Qtotale		= $('#modMatosQteTot').val();
		var dateAchat	= $('#modMatosDateAchat').val();
		var ownerExt	= $('#modMatosExtOwner').val() ;
		var tarifLoc	= $('#modMatosTarif').val();
		var valRemp		= $('#modMatosValRemp').val();
		var panne		= $('#modMatosPanne').val();
		var remarque	= encodeURIComponent($('#modMatosRem').val());
		var externe		= 0;
		if ($('#modMatosExterne').attr('checked')) externe	= 1 ;

		var AjaxStr = 'action=modif&id='+idMatos+'&label='+label+'&ref='+ref
						+'&categorie='+categ+'&sousCateg='+sscateg
						+'&Qtotale='+Qtotale+'&dateAchat='+dateAchat
						+'&tarifLoc='+tarifLoc+'&valRemp='+valRemp+'&panne='+panne
						+'&externe='+externe+'&ownerExt='+ownerExt
						+'&remarque='+remarque ;
		AjaxFct(AjaxStr, 'matos_actions', false, 'retourAjax', 'matos_list_detail');
	});


	// Suppression d'un matos
	$('.deleteMatos').click(function () {
		var id = $(this).attr('id');
		var nom = $(this).attr('nom');
		var AjaxStr = 'action=delete&id='+id;
		if (confirm('Supprimer le matériel "'+nom+'" ? Sûr ??'))
			AjaxJson(AjaxStr, 'matos_actions', alerteErr);
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


	// Ajout d'un matos
	$("#addMatos").click(function () {
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

		if (label == '' || ref == '' || categ == '' || Qtotale == '' || tarifLoc == '' || valRemp == '') {
			alert('Vous devez remplir tous les champs marqués d\'une étoile !');
			return;
		}
		var strAjax = 'action=addMatos&label='+label+'&ref='+ref
					 +'&categorie='+categ+'&sousCateg='+Souscateg
					 +'&Qtotale='+Qtotale+'&dateAchat='+dateAchat
					 +'&tarifLoc='+tarifLoc+'&valRemp='+valRemp
					 +'&externe='+externe+'&ownerExt='+ownerExt
					 +'&remarque='+remarque ;
		AjaxFct(strAjax, 'matos_actions', false, 'retourAjax', 'matos_list_detail');
	});

});

function refreshSousCatLine () {
	$('.sousCategLine').each(function(){
		if ($(this).next().attr('style') == 'display: none;')
			$(this).hide();
	});
}


function displaySelMatos (data) {
	$('#modMatosId').val(data.id);
	$('#modMatosRef').val(data.ref);
	$('#modMatosLabel').val(data.label);
	$('#modMatosQteTot').val(data.Qtotale);
	$('#modMatosTarif').val(data.tarifLoc);
	$('#modMatosValRemp').val(data.valRemp);
	$('#modMatosCateg').val(data.categorie);
	$('#modMatosSousCateg').val(data.sousCateg);
	$('#modMatosPanne').val(data.panne);
	$('#modMatosDateAchat').val(data.dateAchat);
	$('#modMatosExtOwner').val(data.ownerExt);
	$('#modMatosRem').val(data.remarque);
	if (data.externe == '1') {
		 $('#modMatosExterne').attr('checked', 'checked');
		 $('#dateAchatDiv').hide();
		 $('#chezQuiDiv').show();
	}
	else {
		$('#modMatosExterne').removeAttr('checked');
		 $('#chezQuiDiv').hide();
		 $('#dateAchatDiv').show();
	}
}


/////////////////////////////////////// OBSOLETE (sauf debug) ///////////////////////////////
function supprMatos (idMatos) {
	var AjaxStr = 'action=delete&id='+idMatos;
	if (confirm('Supprimer le matériel No '+idMatos+' ?'))
		AjaxFct(AjaxStr, 'matos_actions');
}
