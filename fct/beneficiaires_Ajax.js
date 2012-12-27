
$(function() {
	
	$('.selectStruct').click(function () {
		var selected  = $(this).parents('.structInfos').find('.structID').html();
		var nomStruct = $(this).parents('.structInfos').find('.structLabel').html();
		var ajaxStr   = 'action=selectStruct&id='+selected;
		$('#nomStructModif').html(nomStruct);
		$('#listingPage').animate( { bottom: "185px", opacity: ".5" }, transition );
		$('#modifieurPage').show(transition);
		AjaxJson (ajaxStr, "beneficiaires_actions", displaySelStruct);
	});
	
	$('.selectInterloc').click(function () {
		var selected = $(this).attr('id');
		var nomInterloc = $(this).attr('nom');
		var ajaxStr  = 'action=selectInterloc&id='+selected;
		$('#nomInterlocModif').html(nomInterloc);
		$('#listingPage').animate( { bottom: "185px", opacity: ".5" }, transition );
		$('#modifieurPage').show(transition);
		AjaxJson (ajaxStr, "beneficiaires_actions", displaySelInterloc);
	});
	
	
	$('#addStruct').click(function() {
		var dataStr = 'action=addStruct';
		var label	= encodeURIComponent($('#newStrucLabel').val());
		var raisS	= encodeURIComponent($('#newStrucRS').val());
		var type	= $('#newStrucType').val();
		var adress	= $('#newStrucAdr').val();
		var CP		= $('#newStrucCP').val();
		var ville	= $('#newStrucVille').val();
		var email	= $('#newStrucMail').val();
		var tel		= $('#newStrucTel').val();
		var siret	= $('#newStrucSIRET').val();
		
		if (label == '' || raisS == '' || adress == '' || CP == '' || ville == '') {
			alert ('Il manque une ou plusieurs information(s) ! (champs avec une étoile)') ;
			throw 'Incomplet';
		}
		
		dataStr += '&label='+label+'&NomRS='+raisS+'&type='+type+'&adresse='+adress
				+'&codePostal='+CP+'&ville='+ville+'&email='+email+'&tel='+tel
				+'&SIRET='+siret;
		AjaxFct(dataStr, 'beneficiaires_actions', false, 'retourAjax', 'benef_list_struct');
		
	});
	
	 
//	$('#addInterloc').click(function() {
//		var dataStr = 'action=addInterloc';
//		var nom		= $('#newInterlocNom').val();
//		var label	= $('#newInterlocSurnom').val();
//		var StructID= $('#newInterlocStruct').val();
//		var adress	= $('#newInterlocAdr').val();
//		var CP		= $('#newInterlocCP').val();
//		var ville	= $('#newInterlocVille').val();
//		var email	= $('#newInterlocMail').val();
//		var tel		= getTelnumber(); 
//		var poste	= $('#newInterlocPoste').val();
//		
//		if (nom == '' || adress == '' || CP == '' || ville == '' || StructID == 0) {
//			alert ('Il manque une ou plusieurs information(s) ! (champs avec une étoile)') ;
//			return false;
//		}
//		dataStr += '&label='+label+'&idStructure='+StructID+'&nomPrenom='+nom+'&adresse='+adress
//				+'&codePostal='+CP+'&ville='+ville+'&email='+email+'&tel='+tel + '&poste=' + poste ;
//		AjaxJson(dataStr, 'beneficiaires_actions', false, 'retourAjax', 'benef_list_interloc');
//	});
	
	
	$('.deleteStruct').click(function() {
		var selected  = $(this).parents('.structInfos').find('.structID').html();
		var nomStruct = $(this).parents('.structInfos').find('.structLabel').html();
		
		var strAjax = 'action=supprStruct&id='+selected;
		if (confirm('Supprimer la structure '+nomStruct+' ? Sûr ?'))
			AjaxJson (strAjax, 'beneficiaires_actions', supprStruct );
//			AjaxFct(strAjax, 'beneficiaires_actions', false, 'retourAjax', 'benef_list_struct');
	});
	
	
	$('.deleteOneInterloc').click(function() {
		var idInterloc = $(this).attr('id');
		var strAjax = 'action=supprInterloc&id='+idInterloc;
		if (confirm('Supprimer l\'interlocuteur N° '+idInterloc+' ? Sûr ?'))
			AjaxJson (strAjax, 'beneficiaires_actions', supprInterlock );
	});


	$('.printStruct').click( function (){
		var printerWin =  window.open('','Structure','width=1200,height=600');
		content = $(this).parents(".structItem") ;
		$(content).find('.structInterlock').show();

		var script = '<script type="text/javascript">window.onload( window.print(); )</script>'
		var closeWindow = '<a class="printHide" href="javascript:window.close()">Fermer cette fenetre</a>';
		$('.hidePreview').hide();
		var html = '<html><head><title>Impression Bénéficiaire</title><link type="text/css" href="./'+ cssFile +'/jquery-ui-1.8.17.custom.css" rel="stylesheet" />	<link type="text/css" href="./css/ossature.css" rel="stylesheet" />	<link type="text/css" href="./css/ossature_print.css" rel="stylesheet" media="print"/></head><body>'+ script+'<div id="print">' + $('<div />').append($(content).clone()).html() + '</div> ' + closeWindow + '</body></html>';

		printerWin.document.open();
		printerWin.document.write(html);
		printerWin.document.close();
		printerWin.print();
		$('.hidePreview').show();

	    return false;
	});
	
	
	$('#modifieurPage').on('click', '.modif', function () {
		var typeMod = $(this).attr('id');
		var strAjax = 'action=modif';
		
		if (typeMod == 'structure') {
			strAjax += 'Struct&id='	 + $('#modStrucId').val()
					+  '&label='	 + encodeURIComponent($('#modStrucLabel').val())
					+  '&NomRS='	 + encodeURIComponent($('#modStrucRS').val())
					+  '&type='		 + $('#modStrucType').val()
					+  '&adresse='	 + $('#modStrucAdr').val()
					+  '&codePostal='+ $('#modStrucCP').val()
					+  '&ville='	 + $('#modStrucVille').val()
					+  '&email='	 + $('#modStrucMail').val()
					+  '&tel='		 + $('#modStrucTel').val()
					+  '&SIRET='	 + $('#modStrucSIRET').val()
					+  '&remarque='  + encodeURIComponent($('#modStrucRem').val());
			var sousPage = 'benef_list_struct';
		}
		else if (typeMod == 'interloc') {
			strAjax += 'Interloc&id='+ $('#modInterlocId').val()
					+  '&label='	 + $('#modInterlocLabel').val()
					+  '&nomPrenom=' + $('#modInterlocNom').val()
					+  '&idStructure=' + $('#modInterlocStruct').val()
					+  '&nomStruct=' + $('#modInterlocStruct option:selected').text()
					+  '&adresse='	 + $('#modInterlocAdr').val()
					+  '&codePostal='+ $('#modInterlocCP').val()
					+  '&ville='	 + $('#modInterlocVille').val()
					+  '&email='	 + $('#modInterlocMail').val()
					+  '&poste='	 + $('#modInterlocPoste').val()
					+  '&tel='		 + $('#modInterlocTel').val()
					+  '&remarque='  + encodeURIComponent($('#modInterlocRem').val())
					+  '&typeRetour=noJson';
			var sousPage = 'benef_list_interloc';
		}
		$('#modifieurPage').hide(transition);
		$('#listingPage').animate( { opacity: "1" }, transition );
		AjaxFct(strAjax, 'beneficiaires_actions', false, 'retourAjax', sousPage);
	});


	// gere la multi input de telehpone ( changement de champ quand necessaire )
	$(".phoneInput").keyup( function ( event ){
		var nextInput = $(this).next('.phoneInput');
		if ( $(this).val().length == 2 && $(nextInput).length == 1)
			$(nextInput).focus(); 
	}).keydown( function (event){
		var prevInput = $(this).prev('.phoneInput');
		if ( event.keyCode == 8 && $(this).val().length == 0 && $(prevInput).length == 1 )
			$(prevInput).focus();
	}).focus(function(){this.select();})  ;
	
	
	
	$('.structInterlock').on( 'click', ".btnAddInterlock", function () {
		var modif    = $(this).hasClass('modifInterlock') ;
		var adr      = $(this).parents(".structItem").find('.structAdress').html() ;
		var cp       = $(this).parents(".structItem").find('.structcPostal').html() ;
		var ville    = $(this).parents(".structItem").find('.structVille').html() ;
		var struct   = $(this).parents(".structItem").find('.structLabel').html() ;
		var structID = $(this).parents(".structItem").find('.structID').html() ;
		var title    = 'Ajouter un interlocuteur';

		$("#newInterlocStructName").val ( struct ); 
		$("#newInterlocAdr").val ( adr ); 
		$("#newInterlocCP").val ( cp ); 
		$("#newInterlocVille").val ( ville ); 
		$("#newInterlocStructID").val ( structID );

		if ( modif == true  ){
			var nom    = $(this).parents('.interlockItem').find('.nomPrenom').html();
			var surnom = $(this).parents('.interlockItem').find('.label').html();
			var adr    = $(this).parents('.interlockItem').find('.adresse').html();
			var CP     = $(this).parents('.interlockItem').find('.codePostal').html();
			var ville  = $(this).parents('.interlockItem').find('.ville').html();
			var email  = $(this).parents('.interlockItem').find('.email').html();
			var tel    = $(this).parents('.interlockItem').find('.tel').html();
			var poste  = $(this).parents('.interlockItem').find('.poste').html();
			var rem    = $(this).parents('.interlockItem').find('.remarque').html();
			var id     = $(this).parents('.interlockItem').attr('id');
			title    = 'Modifier un interlocuteur';

			$("#newInterlocNom").val ( nom );
			$("#newInterlocSurnom").val ( surnom );
			$("#newInterlocAdr").val ( adr );
			$("#newInterlocCP").val ( CP );
			$("#newInterlocVille").val ( ville );
			$("#newInterlocMail").val ( email );
			$("#newInterlocPoste").val ( poste );
			$("#newInterlocRem").val ( rem );
			$("#modInterID").val ( id );
			setTelNumber( tel , '.phoneInput');
		}
		
		$('#addInterlok').dialog({
			height: 500,
			width : 800, 
			title: title ,
			modal: true,
			buttons: {
				"OK": function() { addInterlock( modif ) ; },
				"Annuler": function() { $( this ).dialog( "close" ); }	},
			close: function(){ shortRemarque() ; $('#addInterlok input').val(''); } 
		});
	});
	
	
	$('.structInterlock').on( 'click', '.deleteInterlock' , function() {
		var idInterloc = $(this).parents('.interlockItem').attr('id');
		var strAjax = 'action=supprInterloc&id='+idInterloc;
		if (confirm('Supprimer l\'interlocuteur N° '+idInterloc+' ? Sûr ?'))
			AjaxJson (strAjax, 'beneficiaires_actions', supprInterlock );
	});
	
	
	var lastStruct ; 
	$( ".structInfos" ).click(function (e) {
		if ( lastStruct != $(this).parents('.structItem').find('.structLabel').html() ) {
			$('.structInterlock').hide(300);
			$('.structInfos').removeClass('ui-state-highlight');
			$(this).parents('.structItem').find('.structInterlock').addClass('ui-state-highlight').slideToggle(300);
			$(this).addClass('ui-state-highlight');
			lastStruct = $(this).parents('.structItem').find('.structLabel').html();
		}
		else {
			$(this).removeClass('ui-state-highlight').parents('.structItem').find('.structInterlock').hide(300);
			lastStruct = '';
		}
	});
	
	
	shortRemarque ();
	$('.structInterlock').hide();
	
});


function displaySelStruct (data) {
	$('#modStrucId').val(data.id);
	$('#modStrucLabel').val(data.label);
	$('#modStrucRS').val(data.NomRS);
	$('#modStrucType').val(data.type);
	$('#modStrucAdr').val(data.adresse);
	$('#modStrucCP').val(data.codePostal);
	$('#modStrucVille').val(data.ville);
	$('#modStrucMail').val(data.email);
	$('#modStrucTel').val(data.tel);
	$('#modStrucSIRET').val(data.SIRET);
	$('#modStrucRem').val(data.remarque);
}

function displaySelInterloc (data) {
	$('#modInterlocId').val(data.id);
	$('#modInterlocLabel').val(data.label);
	$('#modInterlocNom').val(data.nomPrenom);
	$('#modInterlocStruct').val(data.idStructure);
	$('#modInterlocAdr').val(data.adresse);
	$('#modInterlocCP').val(data.codePostal);
	$('#modInterlocVille').val(data.ville);
	$('#modInterlocMail').val(data.email);
	$('#modInterlocTel').val(data.tel);
	$('#newInterlocPoste').val(data.poste);
	$('#modInterlocRem').val(data.remarque);
}

function addInterlock( modif ){

		if ( modif ){
			var dataStr = 'action=modifInterloc&id=' + $('#modInterID').val()  ;
			var fct = modifInterlockDiv ;
		}
		else{
			var dataStr = 'action=addInterloc';
			var fct = addInterlockDiv ;
		}
			
		var nom		= $('#newInterlocNom').val();
		var label	= $('#newInterlocSurnom').val();
		var nomStruct = $('#newInterlocStructName').val();
		var StructID  = $('#newInterlocStructID').val();
		var adress	= $('#newInterlocAdr').val();
		var CP		= $('#newInterlocCP').val();
		var ville	= $('#newInterlocVille').val();
		var email	= $('#newInterlocMail').val();
		var tel		= getTelnumber(); 
		var poste	= $('#newInterlocPoste').val();
		var rem	= $('#newInterlocRem').val();
		
		if (nom == '' || adress == '' || CP == '' || ville == '' || StructID == 0) {
			alert ('Il manque une ou plusieurs information(s) ! (champs avec une étoile)') ;
			return false;
		}
		
		dataStr += '&label='+label+'&idStructure='+StructID+'&nomStruct=' + nomStruct + '&nomPrenom='+nom+'&adresse='+adress
				+'&codePostal='+CP+'&ville='+ville+'&email='+email+'&tel='+tel + '&poste=' + poste + '&remarque=' + rem ;

		AjaxJson ( dataStr , 'beneficiaires_actions', fct );

}

function getTelnumber (){
	var phone = '';
	$(".phoneInput").each( function (ind, obj ) {
		phone += $(obj).val();
	});
	
	return phone;
}

function setTelNumber ( tel , classSelect ){

	if ( classSelect == undefined ) classSelect = '.phoneInput' ; 

	var maxlength = $(classSelect).attr('maxlength');

	$(classSelect).each( function (ind, obj){
		var digit = tel.substr( ind * maxlength, maxlength );
		$(obj).val(digit);
	});

}

function supprInterlock( retour ){
	if ( retour.success == 'SUCCESS'){
		$('.interlockItem[id="'+retour.id+'"]').hide(600) ;
		$('#retourAjax').html('Interlocuteur supprimé !<br />').show();
		setTimeout(function(){clearDiv('retourAjax');}, 1500);
	}
	else
		alert ( retour.success );
}


function supprStruct( retour ){
	if ( retour.success == 'SUCCESS'){
		$('.structItem[id="'+retour.id+'"]').hide(600) ;
		$('#retourAjax').html('Structure supprimée !<br />').show();
		setTimeout(function(){clearDiv('retourAjax');}, 1500);
	}
	else
		alert ( retour.success );
}


function addInterlockDiv( retour ){
	if ( retour.success == 'SUCCESS'){
		if ( retour.info.poste != '' ) var poste = ' ( <span class="poste">' + retour.info.poste + '</span> )'; else var poste = '';
		if ( retour.info.email != '' ) var email = "<a class='email' href='mailto:" + retour.info.email + "'>" + retour.info.email +"</a>"; else var email = '';
		$('.structVide-'+retour.info.idStructure).remove();
		$('.structInterlock[id="struct-'+retour.info.idStructure+'"]')
			.append("<div class='interlockItem cinquieme pad10 marge30l margeTop10 inline ui-corner-all ui-state-default ui-state-focus' id='"+ retour.info.id +"'>"
						+"<div class='center'>"
							+"<span class='gros label'>"+ retour.info.label + "</span>"
							+"<div class='inline ui-state-default pad3 ui-corner-all marge10l doigt' style='box-shadow: 1px 1px 4px #888888;'>"
								+"<span class='ui-icon ui-icon-pencil btnAddInterlock modifInterlock hidePreview printHide' title='modifier'></span>"
							+"</div>"
							+"<div class='inline ui-state-default pad3 ui-corner-all marge10l doigt' style='box-shadow: 1px 1px 4px #888888;'>"
								+"<span class='ui-icon ui-icon-trash deleteInterlock hidePreview printHide' title='supprimer'></span>"
							+"</div>"
						+"</div>"
						+"<br />"
						+"<div>"
							+"<span class='nomPrenom'>"+ retour.info.nomPrenom +"</span>"
							+ poste
						+"</div>"
						+"<div class='adresse'>"+ retour.info.adresse +"</div>"
						+"<div>"
							+"<span class='codePostal'>"+ retour.info.codePostal +"</span>"
							+"<span class='ville'> "+ retour.info.ville +"</span>"
						+"</div>"
						+"<br />"
						+"<div>"
							+"<div class='ui-icon ui-icon-mail-closed inline'></div>"
							+ email
						+"</div>"
						+"<div>"
							+"<span class='ui-icon ui-icon-contact inline'></span>"
							+"<span class='tel'>"+ retour.info.tel +"</span>"
						+"</div>"
						+"<p></p>"
						+"<div>"
							+"<div class='ui-icon ui-icon-comment inline'></div>"
							+"<div class='remarque inline'>"+ retour.info.remarque +"</div>"
						+"</div>"
				+"</div>");
								
		$('.interlockItem[id="'+retour.info.id+'"]').hide().show(500);
		$('#addInterlok').dialog( "close" );
		
		$('#retourAjax').html('Interlocuteur ajouté !<br />').show();
		setTimeout(function(){clearDiv('retourAjax');}, 1500);
	}
	else
		alert ( retour.success );
}


function modifInterlockDiv ( retour ){
	if ( retour.success == 'SUCCESS'){
		var id = retour.id
		$.each( retour.info , function (ind, obj) {
			if ( ind == 'email' )
				$('.interlockItem[id="'+id+'"]').find('.' + ind ).attr('href', obj );
			$('.interlockItem[id="'+id+'"]').find('.' + ind ).html(obj);
		});
		$('#addInterlok').dialog( "close" );
		$('#retourAjax').html('Interlocuteur modifié !<br />').show();
		setTimeout(function(){clearDiv('retourAjax');}, 1500);
	}
	else
		alert ( retour.success );
}


function shortRemarque (){

	$('.remarque').each( function (ind, obj){
		var chaine = $(obj).html();

		if ( $(obj).parents('.remarqueStruct').length == 1  )
				var longueur = 150 ;
		else
				var longueur = 10 ;
		
		if ( chaine.length > 50 ){
			var debutchaine = chaine.substr(0, longueur);
			var finchaine   = chaine.substr( chaine.length - longueur ,longueur );
			$(obj).html( debutchaine + '<span style="color:black">...</span>' + finchaine) ;
			$(obj).attr( 'title', chaine) ;


		}
	});

}
