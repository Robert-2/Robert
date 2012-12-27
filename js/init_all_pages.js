

$(function() {
	$(".miniCal" ).datepicker({dateFormat: 'yymmdd', firstDay: 1, changeMonth: true, changeYear: true});		// Calendrier inline
	$(".inputCal").datepicker({dateFormat: 'dd/mm/yy', firstDay: 1, changeMonth: true, changeYear: true});		// Calendrier sur focus d'input
	$(".bouton"  ).button();																					// pour faire des jolis boutons
	
	$("#themeSel").change(function () {									// Fonction de choix de thème
		var newTheme = $("#themeSel").val();
		var dataStr = 'action=modifTheme&id='+ID_USER+'&theme='+newTheme;
		AjaxFct(dataStr, 'user_actions', 'reload');
	});
	
	$('input').focus(function() {
		if ($(this).val() == 'votre email' || $(this).val() == 'pass')
			$(this).val('');
	});

	$(document).on( 'blur' , ".EmailInput" , function() {
		var email = $(this).val();
		if ( email == '' ) return ;
		if ( ! verifyEmail(email) )
			alert ('Adresse Email invalide');
	});

	$(document).on( 'keydown' , ".NumericInput", function(event) {
			// Allow: backspace, delete, tab, escape, point
		if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 110 || event.keyCode == 190 || 
			// Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) || 
			// Allow: Ctrl+C
			(event.keyCode == 67 && event.ctrlKey === true) || 
			// Allow: Ctrl+V
			(event.keyCode == 86 && event.ctrlKey === true) || 
			// Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
				return;
		}
		else {
			// Ensure that it is a number and stop the keypress
			if ((event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault(); 
			}   
		}
	});
});



function abortAjax() {
	$('#logo').children('img').attr('src', 'gfx/Robert2.png');
	$('#bigDiv').css('cursor', 'auto');
}


// Fonction Ajax - méthode de récupération HTML. Paramètres :
// @dataStr   = chaine de requête à envoyer,
// @dest      = nom du fichier php de traitement (sans l'extension 'php'),
// @reload    = auto reload, si n'est pas null ou false, recharge la page en cours,
// @divRetour = id de la div où afficher le retour 
//				OU BIEN 
//				nom de la modal à mettre dans l'url GET
function AjaxFct (dataStr, dest, reload, divRetour, urlToGo ) {
	if (divRetour == null || divRetour == undefined || divRetour == '')
		divRetour = 'debugAjax';
	$.ajaxq('ajaxQueue', {
		url: "./fct/"+dest+".php",
		type: "POST",
		data: dataStr,
		success: function (retour) {
			if (reload != null && reload != undefined && reload != false && retour == '') {
				window.location.reload();
			}
			else {
				if (retour != '') {
					$("#"+divRetour).html('<a class="bouton" href="#" style="float:right; font-size:0.9em; top:-5px;" onclick="reloadAtUrl(\''+urlToGo+'\')" title="rafraichir la page">oki</a>'+retour);
					$("#"+divRetour).show(300);
					$("#"+divRetour).effect('pulsate', 300);
					$(".bouton").button();
				}
			}
		},
		error: function () {
			alert('Erreur Ajax ! vérifiez votre connexion à internet. Il se peut aussi que le serveur soit temporairement inaccessible... WTF!');
		}
	});
}
////////////////////////////////////////////////////////////////////////


// Fonction Ajax - méthode de récupération JSON. Paramètres :
// @dataStr  = chaine de requête à envoyer,
// @dest     = nom du fichier php de traitement (sans l'extension 'php'),
// @callback = nom de la fonction qui va traiter le JSON
// @params   = un paramètre (string), ou un tableau des paramètres à envoyer à la fonction de callback si besoin
// remarque : le décodage JSON se fait ici, pas besoin de le faire après... On peut directement traiter l'objet data
function AjaxJson (dataStr, dest, callback, params) {
	var parametres = new Array();
	parametres = parametres.concat(params);
	$.ajaxq('ajaxQueue', {
		url: "./fct/"+dest+".php",
		type: "POST",
		data: dataStr,
		success: function (retour) {
			try {var data = jQuery.parseJSON(retour);}
			catch (err) { alert('ERREUR JSON : '+err+'\nRETOUR PHP :\n\n'+ retour); abortAjax(); }
			parametres.unshift(data);
			callback.apply(this, parametres);
		},
		error: function () {
			alert('Erreur Ajax ! vérifiez votre connexion à internet. Il se peut aussi que le serveur soit temporairement inaccessible... WTF!');
		}
	});
}
////////////////////////////////////////////////////////////////////////


function reloadAtUrl(urlToGo) {
	if ( urlToGo == 'undefined' || $(document).getUrlParam("sousPage") == urlToGo ) {
		window.location.reload();
	}
	else {
		if (urlToGo == 'calendrier')
			window.location = 'index.php?go=calendrier';
		else {
			var urlBase  = window.location.href.split('&')[0];
			var goTo = urlBase+'&sousPage='+urlToGo;
			var goToSsPage = goTo.replace(/\#/, '');
			window.location = goToSsPage;
		}
	}
}



function stripslashes (str) {
	return (str + '').replace(/\\(.?)/g, function (s, n1) {
        switch (n1) { 
			case '\\': return '\\';
			case '0' : return '\u0000';
			case ''  : return '';
			default  : return n1;
		}
    });
}

function addslashes (str) {
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}



function checkChar (evt) {
	var keyCode = evt.which ? evt.which : evt.keyCode;
	var interdit = 'àâäãçéèêëìîïòôöõùûüñ &*?!:;,\t#~"^¨%$£?²¤§%*+@()[]{}<>|\\/`\'';
	if (interdit.indexOf(String.fromCharCode(keyCode)) >= 0) {
		return false;
	}
}

// juste une petite aide si firebug bug !!! LOL
function jsonViewer (data) {
	var jsonView = '';
	for (k in data) {
		jsonView += k +' : ' + data[k] + '<br /> ';
		if (typeof(data[k]) == "object") {
			for (l in data[k]) {jsonView += '--- > ' + l +' : ' + data[k][l] + '<br /> ';}
		}
	};
	return jsonView ;
}



function clearDiv (divE) {
	$("#"+divE).html('');
	$("#"+divE).hide(300);
}

function verifyEmail( email ){
var status = false;     
var emailRegEx = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
     if (email.search(emailRegEx) == -1) {
          status = false ;
     }
	else {
          status = true;
     }
     return status;
}
