
function showToolTip (toAff, decalage) {
	$('#toolTipPopup').html(toAff);
	$('#toolTipPopup').show();
	$(document).bind('mousemove', function(e) {
		var sy = $('#toolTipPopup').parent().scrollTop();
		var tx = e.pageX + decalage;
		var ty = e.pageY + sy - 80 ;
		$('#toolTipPopup').css({'left': tx+'px', 'top': ty+'px', 'z-index': '9999'});
	});
}


function hideToolTip () {
	$('#toolTipPopup').hide();
}

function initToolTip (fromWhere, decalageX) {
	if (decalageX == undefined || decalageX == '') decalageX = 10;
	$(fromWhere).on('mouseenter', '[popup]', function() {
		var toAff = $(this).attr('popup');
		if ( toAff != '') {
			toAff = stripslashes(toAff);
			showToolTip(toAff, decalageX);
		}
	}).on('mouseleave', '[popup]', function() {
		hideToolTip();
	});
}

/* EXEMPLE D'UTILISATION :
*  doit toujours être appellée dans un $(function) (= document.ready)
*  la div 'fromWhere' doit être une div parente des tags ayant l'attribut [popup='']
	$(function() {
		initToolTip('#planTekosMatos');
	});
* 
* 
*/