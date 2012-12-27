
$(function () {
	$('#saveInfos').click(function () {
		var ajaxReq = 'action=modifConsts'
		$('input').each(function() {
			var dataType = $(this).attr('id');
			var dataVal = $(this).val();
			if (dataType == 'TVA_VAL') dataVal /= 100;
			ajaxReq += '&'+dataType+'='+dataVal ;
		});
//		alert(ajaxReq);
		AjaxFct(ajaxReq, 'infos_actions', false, 'retourAjax');
	});
});