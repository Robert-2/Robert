$(function () {
	$('#saveInfos').click(function () {
		var ajaxReq = 'action=modifConsts'
		$('.inputConsts').each(function() {
			var dataType = $(this).attr('id');
			var dataVal = $(this).val();
			if (dataType == 'TVA_VAL') dataVal /= 100;
			ajaxReq += '&'+dataType+'='+dataVal ;
		});
		AjaxFct(ajaxReq, 'infos_actions', false, 'retourAjax');
	});

	$('#changeLogo').on('submit', function (e) {
        e.preventDefault();

        var formdata = (window.FormData) ? new FormData($(this)[0]) : null;
        var data = (formdata !== null) ? formdata : $(this).serialize();

        $.ajax({
            url         : 'fct/infos_actions.php',
            type        : 'POST',
            contentType : false,   // obligatoire pour de l'upload
            processData : false,   // obligatoire pour de l'upload
            dataType    : 'json',
            data        : data,
            success     : function (retour) {
                if (retour.error) {
                    if (retour.error === "OK") {
                        window.location = 'index.php?go=infos';
                    } else {
                        $('#retourAjax').html(retour.message).show();
                    }
                }
                else { alert('ERREUR :\n\n'+ retour); }
            }
        });
    });
});
