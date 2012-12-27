
var transition = 300;	// temps global des animations (en ms)

$(function() {
	
//	$('#logo').hover(													// Pour animer le logo sur mouseOver
//		function(){
//			$(this).children('img').attr('src', 'gfx/Robert-anim2.png');
//		},
//		function(){
//			$(this).children('img').attr('src', 'gfx/Robert2.png');
//		}
//	);
	
	$('#logo').ajaxStart(function(){
		$(this).children('img').attr('src', 'gfx/Robert-anim2.png');
	});
	$('#logo').ajaxStop(function(){
		$(this).children('img').attr('src', 'gfx/Robert2.png');
	});
	
	$('#bigDiv').ajaxStart(function(){
		$(this).css('cursor', 'wait');
	});
	$('#bigDiv').ajaxStop(function(){
		$(this).css('cursor', 'auto');
	});
	
	// clic sur un gros sous-menus ou un mini sous-menus des pages
	$('.sousMenuIcon').click(function() {
		$('.sousMenuIcon').removeClass('ui-state-highlight');
		$(this).addClass('ui-state-highlight');
		$('.sousMenuIcon').next().children('.sousMenuBtns').hide(transition);
		$(this).next().children('.sousMenuBtns').show(transition);
	});
	
	// clic sur un bouton des sous-menus des pages
	$('.sMenuBtn').click(function () {								// Il suffit de mettre le nom de fichier à charger
		var pageToLoad = $(this).attr('id');						// dans l'attribut ID de l'élément sur lequel on click
		$('#retourAjax').hide();
		$('.sousMenuPage').hide(transition);
		$('.miniSousMenu').show(transition, function() {
			$('.pageContent').html('Page en chargement...');
			$('.pageContent').load('modals/'+pageToLoad+'.php');
			$('.pageContent').show(transition);
		});
	});
	
	// clic sur un bouton des mini-sous-menus des pages
	$('.miniSmenuBtn').click(function () {
		var pageToLoad = $(this).attr('id');
		$('#retourAjax').hide();
		$('.pageContent').hide(transition, function() {
			$('.pageContent').html('Page en chargement...');
			$('.pageContent').load('modals/'+pageToLoad+'.php');
			$('.pageContent').show(transition);
		});
	});
	
	
	// clic sur le bouton "cherche"
	$('.chercheBtn').click(function () {
		var pageToLoad = $(this).attr('id');
		var searchFor  = $('#chercheInput').val();
		var searchWhat = $('#filtreCherche').val();
		if (searchFor != '') {
			$('#retourAjax').hide();
			$('.pageContent').load( 'modals/'+pageToLoad+'.php', {'searchingfor':searchFor, 'searchingwhat':searchWhat} );
		}
		else {
			var urlBase  = window.location.href.split('&')[0];
			urlBase = urlBase.replace(/\#/, '');
			window.location = urlBase+'&sousPage='+pageToLoad;
		}
	});
	
	
	// set de la hauteur des icones menus
	resizeIcones();
	// icones des menus mouseover
	$(".menu_icon").hover( function (){icon_Hover($(this), 'in') ;}, function (){icon_Hover($(this), 'out') ;})
	
	// pour faire joli dans les tableaux
	$('.pageContent').on('hover', '.tableListe tbody tr', function (event) {
		if ($(this).hasClass('titresListe') == false) {
			if (event.type == 'mouseenter')
				$(this).addClass('ui-state-focus');
			else $(this).removeClass('ui-state-focus');
		}
	});
	
	$('#unsetPlanAdd').click(function(){
		var strAjax = 'action=unsetSessionPlan&type=plan_add';
		AjaxJson(strAjax, 'plans_actions', alerteErr);
	});
	$('#unsetPlanMod').click(function(){
		var strAjax = 'action=unsetSessionPlan&type=plan_mod';
		AjaxJson(strAjax, 'plans_actions', alerteErr);
	});
	
	
	$('.pageContent').on('click', '.closeModifieur', function() {
		$('tr').removeClass('ui-state-highlight');
		$('#modifieurPage').hide(transition);
		$('#listingPage').animate( {bottom: "0px", opacity: "1"}, transition );
	});
	
});


function alerteErr (retour) {
	var url = window.location.href;
	if (retour.error == 'OK') {
		if (retour.type == 'reloadModal') {
			$('#modalGestionSousCatMatos').load('./modals/matos_gere_sous_cat.php');
			$('#modalGestionSousCatMatos').ajaxStop(function() {
				$('.bouton').button();
			});
		}
		else if (retour.type == 'reloadPage') {
			window.location = url;
		}
		else if (retour.type == 'removeDiv') {
			$('#raccourci_'+retour.divId).remove();
			var divContent = $('#raccourcisPlans').html();
			if (divContent.length <= 4)
				$('#raccourcisPlans').hide(300);
		}
//		else alert('erreur : '+retour.message);
	}
	else {
		alert(retour.error);
		if (retour.type == 'reloadPage') {
			window.location = url;
		}
	}
}

function resizeIcones (){
	var qte     = $(".menu_icon").length ;
	var hauteur = $(".L").height() ;

	var heightItem = hauteur / qte ;
	heightItem = heightItem - heightItem * 0.5 ;

	$(".menu_icon").each( function (ind, obj) {
		$(obj).find("img").height( heightItem );
	});
}

function icon_Hover ( elem, inout ) {
	if ( inout == 'in' )
		$(elem).addClass('ui-state-active');
	else
		$(elem).removeClass('ui-state-active');
}
