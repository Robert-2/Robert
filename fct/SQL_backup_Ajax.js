
$(document).ready(function() {
	$('#dumpSQL').click(function() {
		tableToSave = $('#tableList').val();
		if (confirm('Sauvegarder la base de donnée ?'))
			AjaxFct ('dump='+tableToSave, 'SQL_backup', false, 'retourAjax');
	});
	
	$('#restoreSQL').click(function() {
		fileBackup = $('#dumpList').val();
		if (fileBackup != '----') {
			if (confirm('ATTENTION ! Vous allez effectuer une restauration de la base de données avec le fichier :\r\n'+fileBackup+'\r\nCette action est irréversible !\r\n \r\nCONTINUER ?'))
				AjaxFct ('restore=all&fileBackup='+fileBackup, 'SQL_backup', false, 'retourAjax');
		}
		else alert('Merci de choisir un fichier !');
	});

	
	
	$('#downloadSQL').click(function() {
		fileBackup = $('#dumpList').val();
		if (fileBackup != '----') {
			window.open ('fct/downloader.php?dir=sql&file=' + fileBackup );
		}
		else alert('Merci de choisir un fichier !');
	});

	
});


