var oldEmail = '';
var forceRetapePass = false;
$(function() {
	$('#divAddInfoUsers').appendTo('#infosUserDiv');

	$('#modifInfoUserActif').click(function() {
		var idUser = $(this).attr('idUser');
		$( "#dialogMyInfos" ).dialog({
			autoOpen: true, height: 450, width: 680, modal: true,
			buttons: {"Enregistrer" : function() {saveUserActifInfos(idUser);},
						"Annuler" : function() {$(this).dialog("close");}
			}
		});
	});
	
	// check du changement de mail
	$('#infosUserDiv').on('focus', '#modUserActif-email', function() {
		if (oldEmail == '')
			oldEmail = $(this).val();
	});
	$('#infosUserDiv').on('blur', '#modUserActif-email', function() {
		var newEmail = $(this).val();
		if (newEmail != oldEmail) {
			if (forceRetapePass == false) {
				$('#infosUserDiv').append('<div class="inline top center marge30l mini ui-state-error ui-corner-all pad5 messageSpecial" style="width: 175px;">'
											+'Vous venez de changer votre adresse email ! Merci de retaper votre mot de passe.<br /><i class="petit">C\'est aussi l\'occasion de le changer !</i>'
										+'</div>');
				forceRetapePass = true;
				$('#modUserActif-Pass').focus();
			}
		}
		else forceRetapePass = false;
		
		if (forceRetapePass == false) {
			$('.messageSpecial').remove();
		}
	});
	
/// Pour Admin seulement : -------------------------------------------------------------------------------------------------------------------------------------
	$('#infosUserDiv').on('click', '#addInfoUsers', function() {
		var objDivAddBtn = $('#divAddInfoUsers');
		$('#divAddInfoUsers').remove();
		var toAppend = '<div class="inline top center marge30l blockAddInfo" style="width: 175px;">'
						+'<div class="ui-widget-header ui-corner-all mini">'
							+'<input type="text" class="addToUsersKey" size="15" onKeypress="return checkChar(event);" /></div>'
							+'<input type="text" class="addToUsersVal" size="15" />'
							+'<div class="inline nano"><button class="bouton delInfoUsers" id="del-"><span class="ui-icon ui-icon-minus"></span></button></div>'
							+'<br /><br />'
						+'</div>';
		$('#infosUserDiv').append(toAppend);
		$(objDivAddBtn).appendTo('#infosUserDiv');
		$('.bouton').button();
	});


	$('#infosUserDiv').on('click', '.delInfoUsers', function() {
		var ToDel = $(this).attr('id');
		var infoToDel = ToDel.substr(4);
		if (infoToDel != '') {
			if (confirm("Supprimer l'info \""+infoToDel+"\" ?? Sûr ?"))
				deleteInfoFromUsers(infoToDel);
		}
		else $(this).parents('.blockAddInfo').remove();
	});
/// --------------------------------------------------------------------------------------------------------------------------------------------------------------
});


function saveUserActifInfos (idUser) {
	var infos = {}; var abort = false;
	$('.blockModInfo').each(function() {
		var infoKey = $(this).children('input').attr('id');
		infoKey = infoKey.substr(13);
		var infoVal = $(this).children('input').val();
		infos[infoKey] = infoVal;
		if (infoKey == 'email') {
			if(!verifyEmail(infoVal)) {
				alert('l\'email n\'est pas valide !');abort = true;
			}
		}
	});
	$('.blockAddInfo').each(function() {
		var infoKey = $(this).children('div').children('.addToUsersKey').val();
		var infoVal = $(this).children('.addToUsersVal').val();
		if (!infoKey) {
			alert('Vous devez spécifier un nom d\'info pour en ajouter !')
			abort = true;
		}
		if (! infos[infoKey])
			infos[infoKey] = infoVal;
		else {
			alert('L\'info "'+infoKey+'" existe déjà !');
			abort = true;
		}
	});
	var newPassword = $('#modUserActif-Pass').val();
	if (forceRetapePass == true && (newPassword == '' || newPassword.length < 4)) {
		alert('Vous devez obligatoirement retaper ou modifier votre mot de passe si vous changez d\'adresse email !');
		abort = true;
	}
	if (newPassword != '') {
		if (newPassword.length < 4 ) {
			alert('Le mot de passe doit faire au moins 4 caractères !');
			abort = true;
		}
		infos['password'] = newPassword;
	}
	if (abort == true) return;
	
	var ajaxStr = 'action=modifOwnUser&id='+idUser;
	$.each(infos, function(key, val) {
		ajaxStr += '&'+key+'='+val;
	});
	AjaxJson(ajaxStr, 'user_actions', alerteErr);
}



function deleteInfoFromUsers (infoToDel) {
	var ajaxStr = 'action=supprInfoFromBDD&info='+infoToDel;
	AjaxJson(ajaxStr, 'user_actions', alerteErr);
	$('#del-'+infoToDel).parents('.blockModInfo').remove();
}

