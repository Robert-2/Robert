
var separator	= ' ';
var matosIdQte	= {} ;
var tekosIds	= [] ;

$(document).ready (function() {
	
	$("#PacksHolder").accordion({header: '.packPik', active: false, alwaysOpen: false, autoHeight: false, animated: false}); 
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////    AJOUT DE PLAN    /////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
/// Click sur le bouton 'suivant' lors d'ajout de nouveau plan
	$("#PlanAddNext1").click(function() {
		var incomplet = false;
		var ajaxRequest = 'action=afficheTekosMatos';
		var dateStartPick = $( "#picker_start" ).datepicker("getDate");
		var dateStart = $.datepicker.formatDate('yymmdd', dateStartPick ) ;
		var dateEndPick = $( "#picker_end" ).datepicker("getDate");
		var dateEnd = $.datepicker.formatDate('yymmdd', dateEndPick ) ;
		
		$(".newPlan_data").each (function() {
			var data = $(this).attr("id") ;
			var val  = $(this).val() ;
			if (data == 'titre' || data == 'beneficiaire' || data == 'lieu' || data == 'picker_start') {
				if (val == '' || val == undefined) {incomplet = true;return false;} // arrête le each
			}
			ajaxRequest += "&" + data + '=' + val ;
			if (data == 'titre')
				$('#rappelTitrePlan').html(val.toUpperCase());
			if(data == 'lieu')
				$('#rappelLieuPlan').html(val);
			if(data == 'beneficiaire')
				$('#rappelBenefPlan').html(val);
		});
		
		if (incomplet) {alert ('Vous devez remplir TOUS les champs avec une étoile !!');return ;}
		
		ajaxRequest += "&start="+dateStart ;
		if (dateEnd < dateStart) {
			alert('Date de fin antérieure à date de début !');
			return;
		}
		ajaxRequest += "&end="+dateEnd ;
		
		$("#planInfos").hide();
		
		AjaxJson ( ajaxRequest, "plans_actions", displayTekosMatos );
		
		$("#planTekosMatos").show();
		
	});
	
	
/// Click sur le bouton 'précédent' lors d'ajout de nouveau plan
	$('#PlanAddPrev1').click(function() {
		$("#planTekosMatos").hide();
		$("#planInfos").show();
	});
	
	
/// Click sur un TEKOS pour l'ajouter à la liste
	$('#planTekosMatos').on('click', ".tekosPik", function() {
		var idTekos   = $(this).children('.tek_name').attr('id');

		if ( $(this).hasClass('ui-state-highlight') ) {
			$(this).removeClass('ui-state-highlight');
			tekosIds.splice(tekosIds.indexOf(idTekos), 1 );
		}
		else {
			$(this).addClass('ui-state-highlight');
			tekosIds.push( idTekos );
		}
	});
	
	
/// Click sur le plus ou sur la croix pour ajouter un matos à la liste
	$('#planTekosMatos').on('click', ".toggleMatos", function() {
		var name = $(this).parents('.matos_name').html();
		
		if( $(this).children('a').hasClass('plus') ) {
			$(this).parents('.matosPik').addClass('ui-state-highlight');
			//$(this).parents().children('.qtePik').html(" <input type='text' class='qtePikInput' size='2' value='1' />");
			$(this).parent('.matosDispo').find('.qtePikInput').removeClass("hide").addClass('show');
			$(this).parents().children('.qtePik').addClass("padV10");
			$(this).children('a').removeClass('plus').addClass('moins').css('padding', '3px 8px').html('x');
			$(this).parents().children('.qtePik').children('.qtePikInput').focus();
		}
		else {
			//$(this).parents().children('.qtePik').html("");
			$(this).parent('.matosDispo').find('.qtePikInput').removeClass('show').addClass('hide');
			$(this).children('a').removeClass('moins').addClass('plus').css('padding', '6px 12px').html('+');
			$(this).parents().children('.qtePik').removeClass("padV10");
			$(this).parents('.matosPik').removeClass('ui-state-highlight');
			var id   = parseInt($(this).parents().children('.qtePik').attr('id'), 10);
			delete matosIdQte[id];
		}
		$('.bouton').button();
	});
	
/// après avoir tapé une quantité de matos
	$('#planTekosMatos').on('blur', ".qtePikInput", function() {
		var id		= parseInt($(this).parents('.qtePik').attr('id'), 10) ;
		var newQte	= $(this).val();
		matosIdQte[id]  = parseInt(newQte, 10) ;
		qteMatos_update(id);
		prixTotal();
		aLouer();
	});
	
/// click sur bouton plus d'un pack
	$(".pack_plus").click ( function (){
		var plusmoins = $(this).attr('id');
		var maximumpack = $(this).parents(".packPik").find('.qteDispo_MAX').html()
		var currentpack = $(this).parents(".packPik").find('.qteDispo_QTE').html()

		if ( currentpack >= maximumpack && plusmoins == 'moins') return ; 
		
		var id = $(this).parents(".packPik").attr("id");
		id = id.substr(5) ;
		PackItems = $("#packDetail-" + id).children(".packItem") ;

		$(PackItems).each ( function (ind, obj) {
			var idMatos = $(obj).attr('id') ;
			idMatos = idMatos.substr(3)
			var qteAdd = $(obj).children(".need").html();
			qteAdd = parseInt ( qteAdd, 10) ;
			if ( plusmoins == "moins" ) qteAdd = 0 - qteAdd ;

			addMatos ( idMatos , qteAdd ) ;
			
			})
		prixTotal();
		aLouer();

		var divQte = $(this).parents(".packPik").find(".qteDispo_QTE")
		//var qte = $(divQte).html()
		if ( plusmoins == "moins" ) qte_a = 1;  else qte_a = -1 ;
		qte = parseInt ( currentpack, 10 ) + qte_a ;
		if ( qte < 0 ) $(divQte).addClass('ui-state-error'); else $(divQte).removeClass('ui-state-error');

		$(this).parents(".packPik").find(".qteDispo_QTE").html( qte )
	})
	
	
/// Click sur le bouton Enregistrer, on save le Plan
	$('#planTekosMatos').on('click', '.plan_save', function() {
		var ajaxRequest = "action=newPlan" ;
		var incomplet = false ;
		
		var type = $(this).attr('id');
		ajaxRequest += "&type=" + type ;
		
		$(".newPlan_data").each ( function () {
			var data = $(this).attr("id") ;
			var val  = $(this).val() ;
			
			if ( data == "picker_end" ){
				var dateend = $( "#picker_end" ).datepicker("getDate");
				var je = $.datepicker.formatDate('dd', dateend ) ; 
				var me = $.datepicker.formatDate('mm', dateend ) ; 
				var ae = $.datepicker.formatDate('yy', dateend ) ; 
				ajaxRequest += "&jour_end=" + je + "&mois_end=" + me + "&annee_end=" + ae ;
				return true ;  // prochaine iteration du each
			}
			
			if ( data == "picker_start" ){
				var datestart = $( "#picker_start" ).datepicker("getDate");
				var js = $.datepicker.formatDate('dd', datestart ) ; 
				var ms = $.datepicker.formatDate('mm', datestart ) ; 
				var as = $.datepicker.formatDate('yy', datestart ) ; 
				ajaxRequest += "&jour_start=" + js + "&mois_start=" + ms + "&annee_start=" + as ;
				return true ;  // prochaine iteration du each
			}
			
			ajaxRequest += "&" + data + '=' + val ;
		});
		
		if ( Object.keys(matosIdQte).length > 0 )
			var matosRequest = JSON.encode(matosIdQte);
		else incomplet = 'Vous devez choisir du matériel...';
		
		if (tekosIds.length > 0 )
			tekosRequest = tekosIds.join(' ') ;
		else incomplet = 'Vous devez choisir au moins un technicien...';
		
		if (incomplet == false) {
			ajaxRequest += "&materiel=" + matosRequest + "&techniciens=" + tekosRequest ;
			AjaxFct ( ajaxRequest, "plans_actions", false, "debugAjax" );
		}
		else alert(incomplet);
	});

	

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////   FILTRAGE DES TEKOS ET MATOS    ////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
/// Catégories des tekos
	$("#selectTekosCateg").change(function () {
		$('.tekosPik').show();
		var selection = $(this).val();
		if (selection == 'Tout') return;
		$('.tekosPik').each(function(){
			var categ = $(this).children('.tek_categ').children('img').attr('alt');
			if (categ != selection && categ != 'polyvalent') {
				$(this).hide();
			}
		});
	});
	
/// SWITCH du matos, affichage du détail ou des packs
	$('#packMatosSwitch').click(function () {
		var actuel = $(this).children().html();
		if (actuel == 'MATÉRIEL au détail') {
			$(this).children().html('PACKS de matériel');
			$('#PacksHolder').hide();
			$('#MatosHolder').show();
		}
		else {
			$(this).children().html('MATÉRIEL au détail');
			$('#MatosHolder').hide();
			$('#PacksHolder').show();
		}
		
	});
	
/// Catégories du matos
	$("#selectMatosCateg").change(function () {

		var selection = $(this).val();
		if (selection == 'Tout') {$('.matosPik').show();$('.packPik').show();return;}
		$('.matosPik').each(function(){
			var categ = $(this).children('.matos_categ').children('img').attr('alt');
			if (categ != selection)
				$(this).hide();
			else
				$(this).show();
		
		});

		$('.packDetail').hide(); 
		$('.packPik').each(function( ind, obj ){
			var categ = $(obj).children('.pack_categ').children('img').attr('alt');
			if (categ != selection) 
				$(this).hide(); 
			else
				$(this).show();
			
		});
		
	});


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////    AFFICHAGE DE PLAN    ///////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/// selectionne un Plan dans le combo et charge ses données
	var id ;
	$("#plansTitres").change( function () {
		var id = $(this).val();
		var ajaxReq = "action=loadPlan&methode=json&ID=" + id ;
		AjaxJson (ajaxReq, "plans_actions", displayDetailPlan);
	});
	
	
/// affiche le plan passé en paramètre GET dans l'url
	var planToLoad = $(document).getUrlParam("plan");
	if (planToLoad != null) {
		$("#plansTitres").val(planToLoad);
		var ajaxReq = "action=loadPlan&methode=json&ID=" + planToLoad ;
		AjaxJson (ajaxReq, "plans_actions", displayDetailPlan);
	}
	

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////    MODIFICATION DE PLAN    //////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/// modifie les infos du plan
	$("#displayPlanSel").on( 'click' , '#modifPlanBtn', function (e) {
		var id = $("#plansTitres").val();
		if ( id == "noSelect" ) return ;
		var ajaxReq = "action=modPlan&id="+id ;
		ajaxReq += "&titre="	   + $('#titrePlan').val();
		ajaxReq += "&lieu="		   + $('#lieuPlan').val();
		ajaxReq += "&beneficiaire=" + $('#benefPlan').val();
		
		var dateStart = $( "#debutPlan" ).datepicker("getDate");
		var js = $.datepicker.formatDate('dd', dateStart ) ; 
		var ms = $.datepicker.formatDate('mm', dateStart ) ; 
		var as = $.datepicker.formatDate('yy', dateStart ) ; 
		ajaxReq += "&date_jour_start=" + js + "&date_mois_start=" + ms + "&date_annee_start=" + as ;
		
		var dateEnd = $( "#finPlan" ).datepicker("getDate");
		var je = $.datepicker.formatDate('dd', dateEnd ) ; 
		var me = $.datepicker.formatDate('mm', dateEnd ) ; 
		var ae = $.datepicker.formatDate('yy', dateEnd ) ; 
		ajaxReq += "&date_jour_end=" + je + "&date_mois_end=" + me + "&date_annee_end=" + ae ;
		
		AjaxFct ( ajaxReq, "plans_actions", false, "debugAjax" );
	});

	
/// supprime le plan
	$("#displayPlanSel").on( 'click' , '#supprPlanBtn', function (e) {
		var id = $("#plansTitres").val();
		if ( id == "noSelect" ) return ;
		var ajaxRequest = "action=delPlan&ID="+id ;
		if (confirm('Supprimer le plan N° '+id+', sûr ?'))
			AjaxFct ( ajaxRequest, "plans_actions", false, "debugAjax" );
	});
	
	
	
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////  MODIFICATION SOUS PLANS    /////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	var comment = '';
	$("#displayPlanSel").on ( 'focus' , '.modifSPrem', function (e) {
		comment = $(this).val() ;
	});
	
	$("#displayPlanSel").on ( 'blur' , '.modifSPrem', function (e) {
		var idPlan = $("#plansTitres").val() ;
		if ( idPlan == "noSelect" ) return ;
		var idsousplan = $(this).parent().attr('id') ;
		var newComment = $(this).val() ;
		if ( newComment == '' || newComment == comment ) return;
		var ajaxRequest = "action=updateSousPlanRem&IDplan="+idPlan+"&IDsousplan="+idsousplan+"&comment="+newComment ;
		AjaxFct ( ajaxRequest, "plans_actions", false, "debugAjax" ) ;
	});
		
});



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////  FONCTIONS    ////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function addMatos ( id, qte ) {

	if ( matosIdQte[id] == undefined ) matosIdQte[id] = 0 ;
	matosIdQte[id] = parseInt( matosIdQte[id], 10 ) + parseInt( qte, 10 )   ;
	qteMatos_update( id );
}

function qteMatos_update ( id ){

	var max  =  $("#matos-" + id).children(".matosDispo").find(".qteDispo_onload").html() 
	max = parseInt ( max, 10 );
	var pu = $("#matos-" + id).find(".matos_PU").html() ;
	pu = parseInt ( pu, 10 );
	var qte = parseInt ( matosIdQte[id], 10 );
	ptotal =  pu * qte  ;

	$("#matos-" + id).find(".matos_PRICE").html ( ptotal );
	$("#matos-" + id).children(".matosDispo").find(".qtePikInput").val( matosIdQte[id] );
	$("#matos-" + id).children(".matosDispo").find(".qteDispo_update").html( max - matosIdQte[id] );

	$(".pD-" + id).each ( function (ind , obj ){
		$(obj).children(".dispo").html( max - matosIdQte[id] );
	});

}

function prixTotal(){
	var divOutput = "#debugAjax" ;
	var matos = $(".matosPik").find(".matos_PRICE");
	var prices      = [] ;
	
	$(matos).each ( function (ind, obj){
		var categ = $(obj).parents(".matosPik").children(".matos_categ").children("img").attr("alt");
		var tmpPrice = parseInt ( $(obj).html(), 10 ) ;
		if ( ! isNaN (tmpPrice) ){
			if (prices[categ] == undefined )  prices[categ] = 0 ; 
			prices[categ] += tmpPrice ;
		}
	})

	$(divOutput).html('');
	var total = 0 ; 
	for ( p in prices ) {
		$(divOutput).append('<span>'+ p +'</span> total : ' + prices[p] + '<br />' );
		total += prices[p] ; 
	}

	$(divOutput).append('<p>total : ' + total + '</p>' );

}


function aLouer (){
	var divOutput = "#debugAjax" ;
	var matos = $(".matosPik");
	$(divOutput).append('<p>A LOUER :</p>' );
	
	$(matos).each ( function (ind, obj){

		var qteOnload  = parseInt ( $(obj).find(".qteDispo_onload").html(), 10 );
		var id   = $(obj).attr("id");
		id = id.substr(6);
		id = parseInt ( id, 10 );
		var qteAsked = matosIdQte[id] ;

		var moins = qteOnload - qteAsked ;
		if ( moins < 0 && ! isNaN (moins) ){
			var name               = $(obj).children(".matos_name").html();
			if ( qteOnload < 0  ) moins = moins - qteOnload ; else moins = qteOnload - qteAsked ;
			$(divOutput).append( name + ' : ' + (moins * -1 ) + '<br />' );			
		}
	})
}




/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////   AFFICHAGE des détails d'ajout de plan (Tekos et Matos)    /////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function displayTekosMatos (data) {
	$("#periode").html( data.periodeStart +'</b> au <b>'+ data.periodeEnd +'</b>' );
	$('#displayNbPlanSimult').html('');
	if ( data.nbPlansPeriode != 0 ) {
		var plurielPlan = '';
		if ( data.nbPlansPeriode > 1 )
			plurielPlan = 's';
		$('#displayNbPlanSimult').html( "Attention ! Déjà <b>" + data.nbPlansPeriode + " plan" + plurielPlan + "</b> en même temps dans cette période !");
	}
	
	jQuery.each( data.tekos, function (i, val) {
		var iconeDispo = '';
		if (val.iconeTekos == 'partiel') {
			iconeDispo = "<img src='gfx/icones/icon-"+val.iconeTekos+".png' alt='"+val.iconeTekos+"' popup='Technicien déja pris le :<br />";
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
		
		qteTotale -= qtePanne;
		qteDispo  -= qtePanne;
		
		var matosDispo = "Total : " + qteTotale + "<br /> Dispo : " + qteDispo ;	
		$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_total").html ( qteTotale );
		$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_update").html( qteDispo );
		$("#matos-"+idMatos).children(".matosDispo").find(".qteDispo_onload").html( qteDispo );
 
		
		if ( qteAttente != 0 )
			$("#matos-"+idMatos).children(".matosDispo").children(".qteDispo").attr('popup', qteAttente + ' en attente de confirmation !');
		
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
			var need = $('#packDetail-'+idPack).children('#pD-'+id).children('.need').html();
			need = parseInt(need, 10);
			var qteTxt = qte + ' dispo';
			$('#packDetail-'+idPack).children('#pD-'+id).children('.dispo').html(qteTxt);
			if (need > qte) {
				$('#packDetail-'+idPack).children('#pD-'+id).children('.dispo').addClass('ui-state-error');
			}
			else $('#packDetail-'+idPack).children('#pD-'+id).children('.dispo').removeClass('ui-state-error');
		});
		
	});
	
	$('.plan_save').show();
}



/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////   AFFICHAGE des détails d'un plan (Sélection pour modif)    /////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


function displayDetailPlan (data) {
	var pluriel = '';
	if (data.nbSousPlans > 1) pluriel = 's';
	$('#dureePlan').html('Durée : <b>'+data.nbSousPlans+'</b> jour'+pluriel);
	$('#debutPlan').datepicker('setDate', data.timeDebut);
	$('#finPlan').datepicker('setDate', data.timeFin);
	$('#titrePlan').val(data.titre);
	$('#lieuPlan').val(data.lieu);
	$('#benefPlan').val(data.benef);
	
	var detailsContent = '';
	for (sp in data.sousPlans) {
		detailsContent += '<div class="inline pad5 spSlot" id="'+data.sousPlans[sp]['id']+'">';
		detailsContent += '		<div class="ui-widget-header ui-corner-all padV10">'+data.sousPlans[sp]['jour']+'</div>'
					   +  '		avec <b class="ui-state-default ui-corner-all">'+data.sousPlans[sp]['tekos']+'</b><br />';
		detailsContent += '		<textarea class="modifSPrem" id="'+data.sousPlans[sp]['timestamp']+'" rows="5" cols="20">'+data.sousPlans[sp]['rem']+'</textarea>' ;
		detailsContent += '</div>';
	}
	detailsContent += '<div><button class="bouton">SAUVEGARDER les détails</button></div>';
	$('#ListSpPlan').html(detailsContent);
	
	
	$('#displayPlanSel').show();
}
