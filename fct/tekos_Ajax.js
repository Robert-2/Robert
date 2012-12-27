
$(function() {
	$('.selectTekos').click(function() {
		var selected = $(this).attr('id');
		var nomTekos = $(this).attr('nom');
		var ajaxStr  = 'action=select&id='+selected;
		$('#nomTekosModif').html(nomTekos);
		$('#listingPage').animate( {bottom: "185px", opacity: ".5"}, transition );
		$('#modifieurPage').show(transition);
		
		AjaxJson (ajaxStr, "tekos_actions", displaySelTekos);
	});
	
	$('#modifieurPage').on('click', '.modif', function () {
		var idTekos		= $('#modTekosId').val() ;
		var surnom		= $('#modTekosSurnom').val() ;
		var prenom		= $('#modTekosPrenom').val() ;
		var nom			= $('#modTekosNom').val() ;
		var email		= $('#modTekosEmail').val() ;
		var tel			= $('#modTekosTel').val() ;
		var guso		= $('#modTekosGUSO').val() ;
		var cs			= $('#modTekosCS').val() ;
		var birthD		= $('#modTekosBD').val() ;
		var birthP		= $('#modTekosBP').val() ;
		var habilits	= $('#modTekosHabil').val() ;
		var categ		= $('#modTekosCateg').val() ;
		var secu		= $('#modTekosSECU').val();
		var siret		= $('#modTekosSIRET').val();
		var assedic		= $('#modTekosAssedic').val();
		var intermit	= 0;
		var address     = $("#modTekosAdresse").val();
		var cp          = $("#modTekosCP").val();
		var ville       = $("#modTekosVille").val();
		
		if ($('#modTekosIntermit').attr('checked'))
			intermit = 1 ;
			
		var AjaxStr = 'action=modif&id='+idTekos+'&surnom='+surnom+'&GUSO='+guso+'&CS='+cs
						+ '&prenom='+prenom+'&nom='+nom+'&email='+email+'&tel='+tel
						+'&birthDay='+birthD+'&birthPlace='+birthP
						+'&habilitations='+habilits+'&categorie='+categ+'&SECU='+secu
						+'&SIRET='+siret+'&intermittent='+intermit+'&adresse='+ address + '&cp=' + cp + '&ville='+ville + '&assedic='+assedic ;
						
		AjaxFct(AjaxStr, 'tekos_actions', false, 'retourAjax', 'personnel_list_techniciens');
	});
	
	
	$('.printTekos').click(function(){
		var selected = $(this).attr('id');
		printSelTekos(selected);
	});
	
	
	$('.createUser').click(function () {
		var surnom  = $(this).attr('surnom');
		var idTekos = $(this).attr('id');
		var passNewUser = prompt("Permettre à "+surnom+" d'être un utilisateur du Robert ? \n \n Si oui, donnez-lui un mot de passe : ", '');
		if (passNewUser) {
			if (passNewUser.length < 4 ) {
				alert("ATTENTION ! \n\nLe mot de passe doit faire au moins 4 caractères !");
				return;
			}
			var ajaxStr = 'action=createFromTekos&idTekos='+idTekos+'&passNewUser='+passNewUser;
			AjaxFct(ajaxStr, 'user_actions', false, 'retourAjax', 'personnel_list_utilisateurs');
		}
	});
	
	
	$('.deleteTekos').click(function() {
		var id = $(this).attr('id');
		var AjaxStr = 'action=delete&id='+id;
		if (confirm('Supprimer le technicien No '+id+' ?'))
			AjaxFct(AjaxStr, 'tekos_actions', false, 'retourAjax');
	});
	
	$("#addTekos").click(function () {
		var surnom		= $('#newTekosSurnom').val() ;
		var prenom		= $('#newTekosPrenom').val() ;
		var nom			= $('#newTekosNom').val() ;
		var email		= $('#newTekosEmail').val() ;
		var tel			= $('#newTekosTel').val() ;
		var guso		= $('#newTekosGUSO').val() ;
		var cs			= $('#newTekosCS').val() ;
		var birthD		= $('#newTekosBD').val() ;
		var birthP		= $('#newTekosBP').val() ;
		var categ		= $('#newTekosCateg').val() ;
		var secu		= $('#newTekosSECU').val();
		var assedic		= $('#newTekosAssedic').val();
		var siret		= $('#newTekosSIRET').val();
		var intermit	= 0;
		var address     = $("#newTekosAdresse").val();
		var cp          = $("#newTekosCP").val();
		var ville       = $("#newTekosVille").val();
		
		if ($('#newTekosIntermit').attr('checked')) intermit = 1 ;
		
		if (surnom == '' || categ == '' || prenom == '' || nom == '') {
			alert('Vous devez remplir tous les champs marqués d\'une étoile !');
			return;
		}

		if (siret == '') siret = 'N/A';
		var strAjax = 'action=add&surnom='+surnom+'&prenom='+prenom+'&nom='+nom
					 +'&email='+email+'&tel='+tel+'&GUSO='+guso+'&CS='+cs
					 +'&birthDay='+birthD+'&birthPlace='+birthP+'&categorie='+categ
					 +'&SECU='+secu+'&SIRET='+siret
					 +'&intermittent='+intermit+'&adresse='+ address + '&cp=' + cp + '&ville='+ville + '&assedic='+assedic ;
		AjaxFct(strAjax, 'tekos_actions', false, 'retourAjax');
	});

	// partie uploads tekos //

	$(".showDiplomsTekos").click( function () {
		var name = $(this).parents("tr").find('.tekSurnom').html() ;
		uploader.setParams({folder: name});
		$('#modalTekName').html( name );
		$("#file-list").html();
		$('.bouton').button();
		
		// AJAX recupere le contenu du dossier DATA du tekos
		var data = 'action=fileList&user=' + name ;
		AjaxJson ( data , 'tekos_actions', displayFiles ) ;
		
		$("#modalTekosFiles").dialog({
			height: 300,
			width : 500, 
			title: 'Liste des fichiers de : ' + name ,
			modal: true,
			buttons:{'Ok' : function() {$(".qq-upload-file").remove();$("#modalTekosFiles").dialog('close');}}
		});
	});

	$("#file-list").on( 'click', '.deleTekFile', function () {
		var name = encodeURIComponent($(this).attr('id'));
		var tek  = $('#modalTekName').html();
		var data = "action=delTekFile&dir=Tekos&data="+ tek +"&file=" + name;

		AjaxJson ( data , 'tekos_actions' , deleteFile ) ; 

	});
	
});

function displaySelTekos (data) {
	$('#modifieurPage :input').val();
	$('#modTekosId').val(data.id);
	$('#modTekosSurnom').val(data.surnom);
	$('#modTekosPrenom').val(data.prenom);
	$('#modTekosNom').val(data.nom);
	$('#modTekosCateg').val(data.categorie);
	$('#modTekosEmail').val(data.email);
	$('#modTekosTel').val(data.tel);
	$('#modTekosSECU').val(data.SECU);
	$('#modTekosBD').val(data.birthDay);
	$('#modTekosBP').val(data.birthPlace);
	$('#modTekosGUSO').val(data.GUSO);
	$('#modTekosCS').val(data.CS);
	$('#modTekosAssedic').val(data.assedic);
	$('#modTekosSIRET').val(data.SIRET);
	$('#modTekosCP').val(data.cp);
	$('#modTekosVille').val(data.ville);
	$('#modTekosAdresse').val(data.adresse);
	
	if (data.intermittent == 1)
		$('#modTekosIntermit').attr('checked', 'checked');
	else $('#modTekosIntermit').removeAttr('checked');
}


function printSelTekos (id) {
	window.open('modals/tekos_printDetails.php?&tek='+id, 'RobertPrint', 'scrollbars=yes,menubar=yes,width=960,height=720,resizable=no,location=no,directories=no,status=no');
}


function deleteFile ( retour ){
	$.each( retour , function ( ind, obj ) {
		if ( ind !=  'Error' ) {
			var idDiv = "#fileTekos_" + obj ;
			$('.fileTek').each( function ( ind, obj2 ){
				var x = $(this).attr('id') ;
				var y = 'fileTekos_' + obj ;
				if ( y == x )
					$(this).remove();
			});
		}
		else {
			alert ( obj ); 
		}
	});
	if ($('#file-list').find('.fileTek').length == 0 )
		$('#file-list').html('Aucun fichier.');
}


function displayFiles( data ){
	$('#file-list').html('');
	if ( data.length == 0 ){
		$('#file-list').html("Aucun fichier enregistré pour ce tekos");
		return;
	} 
		
	$.each( data , function (ind, val ){
		if ( ind != 'Error')
			addUploadedFile ( val );
		else
			$('#file-list').append( val ) ;
	});
}


function addUploadedFile ( val ) {
	var dataTek = $('#modalTekName').html();
	if ($('#file-list').find('.fileTek').length == 0 )
		$('#file-list').html('');
	var fileLink = encodeURIComponent(val);
	$('#file-list').append( '<div id="fileTekos_'+val+'" class="fileTek">'
								+ '<div class="ui-icon ui-icon-trash inline pointer deleTekFile" id="'+val+'"></div>'
								+ '<a target="_new" class="marge10l" href="fct/downloader.php?dir=Tekos&idTekos='+dataTek+'&file='+fileLink+'">'+val+'</a>'
						  + '</div>' );
}


function createUploader(){
	
	uploader = new qq.FileUploader({
		element: document.getElementById('file-uploader'),
		action: 'fct/uploader.php?dataType=tekos',
		debug: false,
        sizeLimit: 62914560,   
        minSizeLimit: 0,   
		allowedExtensions: ["jpg", "jpeg", "pdf", "png", "bmp"],
		onComplete: function(id, fileName, responseJSON){
			$(".qq-upload-success > .qq-upload-file").each( function (ind, obj){
					var name = $(this).html();
					name = name.replace(/&amp;/g, '&');
					if ( name == fileName )
						addUploadedFile ( fileName );
					$(obj).parent('.uploading_file').remove();
				});
			
		},

		onProgress: function(id, fileName, loaded, total){
			var percent = parseInt ( loaded * 100 / total ) ;
			$( ".progressbar[id$='prog_"+id+"']" ).progressbar({value: percent});
		},
		
        template: '<div class="qq-uploader">' + 
						'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>' +
						'<div class="qq-upload-button"><button class="bouton">Ajouter des fichiers</button></div>' +
						'<ul class="qq-upload-list"></ul>' + 
					'</div>',

        fileTemplate: '<div class="uploading_file">' +
							'<span class="qq-upload-file"></span>' +
							'<span class="qq-upload-spinner" style="width:150px;"><div class="progressbar" style="height:13px;" ></div></span>' +
							'<span class="qq-upload-size"></span>' +
							'<a class="qq-upload-cancel" href="#">Annuler</a>' +
							'<span class="qq-upload-failed-text">Erreur</span>' +
						'</div>',
            
		messages: {
			typeError: "{file} Extension de fichier non permise. Utilisez des fichiers : {extensions}.",
            sizeError: "{file} : Taille de fichier limité à  {sizeLimit}.",
            minSizeError: "{file} est trop petit, la taille minimum est de {minSizeLimit}.",
            emptyError: "{file} est vide !",
            onLeave: "Des fichiers sont en cours de téléchargement. Si vous quittez la page, ils seront corrompus !"  
		}
	});	
}


function attachProgressBar ( id , fileName ){
   $(".qq-upload-file").each( function ( ind, obj  ){
		if ( $(obj).html() == fileName ){
			$(obj).parent('.uploading_file').find('.progressbar').attr('id', 'prog_'+id ) ;
		}
	});
}
