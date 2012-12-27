
$(function () {
	$('#menuBug').click(function(){
		$('.menuBH').removeClass('ui-state-highlight').addClass('ui-state-default');
		$(this).addClass('ui-state-highlight');
		$('.BHsection').hide();
		$('#bugDiv').show();
	});
	$('#menuWishes').click(function(){
		$('.menuBH').removeClass('ui-state-highlight').addClass('ui-state-default');
		$(this).removeClass('ui-state-default').addClass('ui-state-highlight');
		$('.BHsection').hide();
		$('#wishesDiv').show();
	});
	$('#menuPanic').click(function(){
		$('.menuBH').removeClass('ui-state-highlight').addClass('ui-state-default');
		$(this).removeClass('ui-state-default').addClass('ui-state-highlight');
		$('.BHsection').hide();
		$('#panicDiv').show();
	});
	
	
/// TRAITEMENT DES BUGS
	$('#addBugBtn').click(function() {
		$('#dialogBug').dialog({
				autoOpen: true, height: 400, width: 550, modal: true,
				buttons: {"AJOUTER" : function() {addBug();},
						  "Fermer"	: function() {$(this).dialog("close");}
						 }
		});
	});
	
	$('.bugFixeur').click(function() {
		var idBug  = $(this).attr('bug');
		var fixeur = $(this).attr('fixer');
		var strAjax = 'action=modXML&type=bug&id='+idBug+'&fixer='+fixeur;
		AjaxJson(strAjax, 'bugHunter_actions', showBugFixer);
	});
	
	$('#bugsList').on('click', '.bugKiller', function() {
		var idBug = $(this).attr('bug');
		var strAjax = 'action=supprXML&type=bug&id='+idBug;
		AjaxJson(strAjax, 'bugHunter_actions', removeBug);
	});
	
	
	
/// TRAITEMENT DES WISHES
	$('#addWishBtn').click(function() {
		$('#dialogWish').dialog({
				autoOpen: true, height: 400, width: 550, modal: true,
				buttons: {"AJOUTER" : function() {addWish();},
						  "Fermer"	: function() {$(this).dialog("close");}
						 }
		});
	});
	
	$('.wishFixeur').click(function() {
		var idWish = $(this).attr('wish');
		var fixeur = $(this).attr('fixer');
		var strAjax = 'action=modXML&type=wish&id='+idWish+'&fixer='+fixeur;
		AjaxJson(strAjax, 'bugHunter_actions', showWishFixer);
	});
	
	$('#wishesList').on('click', '.wishKiller', function() {
		var idWish = $(this).attr('wish');
		var strAjax = 'action=supprXML&type=wish&id='+idWish;
		AjaxJson(strAjax, 'bugHunter_actions', removeWish);
	});
	
	
	
/// BOUTON PANIC !!
	$('#sendPanic').click(function() {
		var messageTxt = $('#panicMessage').val();
		if (messageTxt == '') {
			alert("Merci d'être plus clair...");
			return;
		}
		messageTxt = encodeURIComponent(messageTxt);
		var strAjax = 'action=sendPanic&type=panic&prenomUser='+prenomUser+'&message='+messageTxt;
		AjaxJson(strAjax, 'bugHunter_actions', alerteErr);
	});
	
});
// FIN DU DOCUMENT READY



///////////////////////////////////////////////////////////// FONCTIONS DES BUGS //////////////////////////////////////////////////////////

function addBug () {
	var descr = $('#newBugDescr').val();
	var repro = $('#newBugRepro').val();
	if (descr == '' || repro == '') {alert("Merci d'être plus précis !");return;}
	var strAjax = 'action=addToXML&type=bug&id='+nextIDbug+'&descr='+descr+'&repro='+repro+'&user='+prenomUser;
	AjaxJson(strAjax, 'bugHunter_actions', refreshBugList);
}

function refreshBugList (data) {
	if (data.error != undefined && data.error != '') {
		 alert(data.error);
		 return;
	}
	nextIDbug++;
	$('#bugsList').append('<div class="ui-state-default ui-corner-all pad3 marge15bot" id="bug-'+data.id+'">'+
							'<div class="inline top" style="width:130px;"><i>par <b>'+data.by+'</b></i></div>'+
							'<div class="inline top">'+data.descr+'</div>'+
							'<br />'+
							'<div class="fixerDiv inline top mini" style="width:130px;">'+
								data.mailSent +
							'</div>'+
							'<div class="inline top pad10 margeTop5 shadowIn ui-corner-all">'+data.repro+'</div>'+
						'</div>');
	$('#dialogBug').dialog("close");
}

function showBugFixer (data) {
	if (data.error != undefined && data.error != '') {
		 alert(data.error);
		 return;
	}
	$('#bug-'+data.id).children('.fixerDiv')
				  .html('<span class="ui-state-error ui-corner-all" style="padding:1px;"><b>'+data.fixer+'</b> s\'en occupe</span>');
	if (data.fixer == prenomUser)
		$('#bug-'+data.id).children('.fixerDiv')
				  .append('<br /><button class="ui-state-error bouton bugKiller margeTop5" bug="'+data.id+'"><b>Kill da Bug</b></button>');
	$('.bouton').button();
}

function removeBug (data) {
	$('#bug-'+data.id).remove();
}




/////////////////////////////////////////////////////////////// FONCTIONS DES WISHES ////////////////////////////////////////////////////////

function addWish () {
	var descr = $('#newWishDescr').val();
	var prior = $('#newWishPrio').val();
	if (descr == '') {alert("Merci d'être plus précis !");return;}
	var strAjax = 'action=addToXML&type=wish&id='+nextIDwish+'&descr='+descr+'&prio='+prior+'&user='+prenomUser;
	AjaxJson(strAjax, 'bugHunter_actions', refreshWishList);
}

function refreshWishList (data) {
	if (data.error != undefined && data.error != '') {
		 alert(data.error);
		 return;
	}
	nextIDwish++;
	$('#wishesList').append('<div class="ui-state-default ui-corner-all pad5 marge15bot" id="wish-'+data.id+'">'+
							'<div class="inline top" style="width:130px;"><i>par <b>'+data.by+'</b></i></div>'+
							'<div class="inline top mini" style="width:90px;">Priorité <b>'+data.prio+'</b>/10</div>'+
							'<div class="inline top">'+data.descr+'</div>'+
							'<br />'+
							'<div class="fixerDiv inline top mini" style="width:220px;">'+
								data.mailSent +
							'</div>'+
						'</div>');
	$('#dialogWish').dialog("close");
}

function showWishFixer (data) {
	if (data.error != undefined && data.error != '') {
		 alert(data.error);
		 return;
	}
	$('#wish-'+data.id).children('.fixerDiv')
				  .html('<span class="ui-state-error ui-corner-all" style="padding:1px;"><b>'+data.fixer+'</b> s\'en occupe</span>');
	if (data.fixer == prenomUser)
		$('#wish-'+data.id).children('.fixerDiv')
				  .append('<br /><button class="ui-state-error bouton wishKiller margeTop5" wish="'+data.id+'"><b>DONE ?</b></button>');
	$('.bouton').button();
}

function removeWish (data) {
	$('#wish-'+data.id).remove();
}