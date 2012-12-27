

$(document).ready(function() {
	
	// Filtrage de la liste du matos par catégories et Switch vue du matos interne / externe
	if (filtreRunning == false) {			// Pour ne le lancer qu'une seule fois !
		$('.filtre').click(function() {
			filtreRunning = true;
			$('.pDetail').hide();
			var categ = $(this).attr('id');
			$('.matosLine').hide();
			if ($(this).hasClass('ui-state-error')) {
				$('.filtre').removeClass('ui-state-error');
				if (categ == 'int-ext') {
					$('.matosExterne').hide();
					$('.matosInterne').show();
				}
				else $('.matosInterne').show();
			}
			else {
				$('.filtre').removeClass('ui-state-error');
				if (categ == 'int-ext') {
					$('.matosInterne').hide();
					$('.matosExterne').show();
				}
				else $('.cat-'+categ).show();
				$(this).addClass('ui-state-error');
			}
		});
	}
	$('.filtreD').click(function() {
		var categ = $(this).attr('id');
		$('.matosLine').hide();
		if ($(this).hasClass('ui-state-error')) {
			$('.filtreD').removeClass('ui-state-error');
			if (categ == 'int-ext') {
				$('.matosLine').show();
			}
			else $('.matosLine').show();
		}
		else {
			$('.filtreD').removeClass('ui-state-error');
			if (categ == 'int-ext') {
				$('.matosLine').hide();
				$('.ui-state-active').show();
			}
			else $('.cat-'+categ).show();
			$(this).addClass('ui-state-error');
		}
	});
	
	
	// montre les détails d'un pack en dessous
	$('.showPDtr').click(function() {
		$('.pDetail').hide();
		var idPack = $(this).attr('id');
		$('#packDetailTR-'+idPack).toggle();
	});
	
	
	// Sélection d'un pack
	$('.selectPack').click(function() {
		var idSel  = $(this).attr('id');
		var nomSel = $(this).attr('nom');
		var AjaxStr  = 'action=select&id='+idSel;
		$('.packContent').html('');
		$('#nomPackModif').html(nomSel);
		$('#listingPage').animate( {bottom: "185px", opacity: ".5"}, transition );
		$('#modifieurPage').show(transition);
		$('tr').removeClass('ui-state-highlight');
		$(this).parents('tr').addClass('ui-state-highlight');
		AjaxJson(AjaxStr, 'packs_actions', displaySelPack);
	});
	
	
	// Suppression d'un pack
	$('.deletePack').click(function () {
		var id = $(this).attr('id');
		var nom = $(this).attr('nom');
		var AjaxStr = 'action=delete&id='+id;
		if (confirm('Supprimer le pack "'+nom+'" ? Sûr ??'))
			AjaxFct(AjaxStr, 'packs_actions', false, 'retourAjax', 'matos_list_packs');
	});
	
	
	// modification d'un matos
	$('#modifieurPage').on('click', '.modif', function () {
		var idPack		= $('#modPackId').val();
		var label		= encodeURIComponent($('#modPackLabel').val()) ;
		var ref			= $('#modPackRef').val() ;
		var categ		= $('#modPackCateg').val() ;
//		var tarifLoc	= $('#modPackTarif').val() ;
//		var valRemp		= $('#modPackValRemp').val() ;
		var remarque	= encodeURIComponent($('#modPackRem').val());
		var listMatos	= JSON.stringify(listDetailPack);
		var externe		= 0;
		if ($('#modPackExterne').attr('checked')) externe	= 1 ;
		
		if (label == '' || ref == '' || categ == '') {
			alert('Il manque le nom, la référence ou la catégorie !');
			return;
		}
		var AjaxStr = "action=modif&id="+idPack+"&label="+label+"&ref="+ref
						+"&categorie="+categ
//						+"&tarifLoc="+tarifLoc+"&valRemp="+valRemp
						+"&externe="+externe+"&remarque="+remarque+'&detail='+listMatos ;
		AjaxFct(AjaxStr, 'packs_actions', false, 'retourAjax', 'matos_list_packs');
	});
	
	
	// Ajout d'un pack (bouton save)
	$("#addPack").click(function () {
		var label		= encodeURIComponent($('#newPackLabel').val()) ;
		var ref			= $('#newPackRef').val() ;
		var categ		= $('#newPackCateg').val() ;
		var detail		= $('#newPackDetail').val() ;
		var remarque	= encodeURIComponent($('#newPackRemark').val()) ;
		var listMatos	= JSON.stringify(listDetailPack);
		var externe		= 0;
		if ($('#newPackExterne').attr('checked')) externe	= 1 ;
		
		if (label == '' || ref == '' || categ == '') {
			alert('Vous devez remplir tous les champs marqués d\'une étoile !');
			return;
		}
		var AjaxStr = 'action=addPack&label='+label+'&ref='+ref+'&categorie='+categ
					 +'&detail='+detail
					 +'&externe='+externe+'&remarque='+remarque+'&detail='+listMatos ;
		AjaxFct(AjaxStr, 'packs_actions', false, 'retourAjax', 'matos_list_packs');
	});
	
	
	// Ouverture de la fenêtre d'ajout de matos au pack
	$('#addDetail').click(function () {
		if ( $('#newPackExterne:checked').length > 0 ) $('#int-ext[class*="filtreD"]').click();
		$( "#Dialog" ).dialog({
			autoOpen: true, height: 600, width: '90%', modal: true,
			buttons: {"Terminé" : function() {$(this).dialog("close");}
			},
			title: 'Ajout de détail dans le pack'
		});
	});
	
	
	// Ajout de matos dans le pack
	$('.addMatosToPack').click(function() {
		var idMatos  = $(this).attr('id');
		var refMatos = $(this).attr('ref');
		var qte = $('#qteAdd-'+idMatos).val();
		if (qte == "" || qte == 0) qte = 1;
		else qte++;
		addMatosToPack (idMatos, refMatos, qte);
	});
	
	$('.decMatosToPack').click(function() {
		var idMatos  = $(this).attr('id');
		var refMatos = $(this).attr('ref');
		var qte = $('#qteAdd-'+idMatos).val();
		if (qte != "" && qte != 0) {
			qte--;
			if (qte == 0) deleteMatosFromPack (idMatos);
			else addMatosToPack (idMatos, refMatos, qte);
		}
	});
	
	
	
	// enlever un matos de la sélection lors d'ajout dans un pack
	$('.packContent').on('click', '.deleteMatosFromPack', function() {
		var idMatos = $(this).parent().attr('idMatos');
		deleteMatosFromPack(idMatos);
	});
	
	// corriger la quantité de matos à ajouter au pack
	$('.packContent').on('change', '.qteMatosCorrection', function() {
		var idMatos = $(this).parent().attr('idMatos');
		var qte		= $(this).val();
		$('.corr-'+idMatos).val(qte);
		$('#qteAdd-'+idMatos).val(qte);
		listDetailPack[idMatos] = qte;
		if (qte == 0) deleteMatosFromPack (idMatos);
	});
	
	
	// Affichage du contenu du pack pour la modif
	$('#showPackContent').click(function(){
		$('.matosLine').show();
		if ( $('#modPackExterne:checked').length > 0 ) $('#int-ext[class*="filtreD"]').click();
		var ref = $('#modPackRef').val();
		$('#DialogDetailPack').dialog({
			autoOpen: true, height: 600, width: '90%', modal: true,
			buttons: {"Terminé" : function() {$(this).dialog("close");}
			},
			title: 'Modification du contenu du pack "'+ref+'"'
		});
	});
	
});


function addMatosToPack (idMatos, refMatos, qte) {
	$('#qteAdd-'+idMatos).val(qte);
	$('.packVideHelp').remove();
	if (listDetailPack[idMatos] != undefined)
		$('.corr-'+idMatos).val(qte);
	else {
		$('.packContent').append('<div class="inline top marge5 center mini matosDetailDiv">'
									+'<div class="ui-widget-header ui-corner-top pad3" style="width:110px;">'+refMatos+'</div>'
									+'<div class="ui-widget-content ui-corner-bottom pad3 qteMatosDiv" idMatos="'+idMatos+'">'
										+'<input type="text" class="qteMatosCorrection corr-'+idMatos+'" value="'+qte+'" size="3" title="correction quantité" /> '
										+'<button class="bouton deleteMatosFromPack" title="enlever du pack"><span class="ui-icon ui-icon-trash"></span></button>'
									+'</div>'
								+'</div>');
		$('.bouton').button();
	}
	listDetailPack[idMatos] = qte;
}



function deleteMatosFromPack (idMatos) {
	$('#qteAdd-'+idMatos).val("");
	delete listDetailPack[idMatos];
	$('div[idMatos*="'+idMatos+'"]').parents('.matosDetailDiv').remove();
}



// Remplissage du modifieur
function displaySelPack (data) {
	var qtePack = $('#qtePack-'+data.id).html();
	$('#modPackId').val(data.id);
	$('#modPackRef').val(data.ref);
	$('#modPackLabel').val(data.label);
//	$('#modPackQteTot').html(qtePack);
	$('#modPackQteTot').html(data.Qtotale);
	$('#modPackTarif').html(data.tarifLoc+' €');
//	$('#modPackValRemp').val(data.valRemp);
	$('#modPackCateg').val(data.categorie);
	$('#modPackRem').val(data.remarque);
	if (data.externe == '1')
		 $('#modPackExterne').attr('checked', 'checked');
	else $('#modPackExterne').removeAttr('checked');
	
	var detailPack = $.parseJSON(data.detail);
	$('.inputQteAdd').val('');
	$.each(detailPack, function(idMatos, qte) {
		listDetailPack[idMatos] = qte;
		$('.corr-'+idMatos).val(qte);
		$('#qteAdd-'+idMatos).val(qte);
		$('.packContent').append('<div class="inline top marge5 center mini matosDetailDiv">'
										+'<div class="ui-widget-header ui-corner-top pad3" style="width:110px;">'+refMatos[idMatos]+'</div>'
										+'<div class="ui-widget-content ui-corner-bottom pad3 qteMatosDiv" idMatos="'+idMatos+'">'
											+'<input type="text" class="qteMatosCorrection corr-'+idMatos+'" value="'+qte+'" size="3" title="correction quantité" /> '
											+'<button class="bouton deleteMatosFromPack" title="enlever du pack"><span class="ui-icon ui-icon-trash"></span></button>'
										+'</div>'
									+'</div>');
	});
	$('.packVideHelp').remove();
}
