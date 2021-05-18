//CRIANDO UMA VARIÁVEL COM A URL A QUAL AS REQUISIÇÕES AJAX DEVEM SER ENVIADAS
var urlTemp = window.location.href.split('/view.php');
var urlAjax = urlTemp[0] + '/acoesListaDeGrupos.php';


function apenasNumeros(e) {
    var focused = $(':focus');
    if(focused.hasClass('apenasNumeros')){
    if(e.keyCode === 13){
        var focused = $(':focus');
        if(focused.hasClass('notaGrupo')){
        	if( focused.val() != "" && focused.val() != "0.00"){
        		nota.atualiza(focused.data('tcc'), focused.data('group'), focused.data('type'), focused.val(), focused.data('levelid'));
        	}
        }
        if(focused.hasClass('notaStage')){
        	if( focused.val() != "" && focused.val() != "0.00"){
        		notaStage.atualizaNota(focused.data('tcc'), focused.data('stage'), focused.data('group'), focused.val(),focused.data('levelid'));

        	}
        }
    }
    else if(((e.keyCode < 37) || (e.keyCode > 40))){

        var focused = $(':focus');
        var valor = focused.val();
        if((e.keyCode !== 96 || e.keyCode !== 48)&& valor === '0.00'){
            focused.val('');
        }else{
            var expression1 = /[^0-9]/g;
            var expression2 = /^0*/;
            valor = valor.replace(expression2, '');
            valor = valor.replace(expression1, '');
            while(valor.length < 3){
                valor = '0'+valor;
            }
            var total = valor.length;

            if(total >= 3){
                var valor1 = valor.substring(0,total - 2);
                var valor2 = valor.substring(total-2, total);
                valor = valor1 + '.' + valor2;

            }
            focused.val(valor);
        }
        }
    }
}

function camposExtras(e) {
    var focused = $(':focus');
    if(focused.hasClass('camposExtras')){
    if(e.keyCode === 13){
        var focused = $(':focus');
        if(focused.hasClass('extraGrupo')){
        	if( focused.data('valor') != undefined){
        		nota.atualiza(focused.data('tcc'), focused.data('group'), focused.data('type'), focused.data('valor'), focused.data('levelid'));
        	}
        }
        if(focused.hasClass('extraStage')){
        	if( focused.data('valor') != undefined){
        		notaStage.atualizaNota(focused.data('tcc'), focused.data('stage'), focused.data('group'), focused.data('valor'), focused.data('levelid'));

        	}
        }
    }
    else if(((e.keyCode < 37) || (e.keyCode > 40))){

        var focused = $(':focus');
        var valor = focused.data('valor');
        if((e.keyCode !== 96 || e.keyCode !== 48)&& valor === '0.0'){
        	focused.data('valor', '');
        }else{
            var expression1 = /[^0-9]/g;
            var expression2 = /^0*/;
            valor = valor.replace(expression2, '');
            valor = valor.replace(expression1, '');
            while(valor.length < 3){
                valor = '0'+valor;
            }
            var total = valor.length;

            if(total >= 3){
                var valor1 = valor.substring(0,total - 2);
                var valor2 = valor.substring(total-2, total);
                valor = valor1 + '.' + valor2;

            }
            focused.data('valor', valor);
        }
        }
    }
}

function iniciaCamposNumericos() {
  if ($('.apenasNumeros').length > 0) {
    $(document).keyup(apenasNumeros);
  }

  if ($('.camposExtras').length > 0) {
	    $(document).keyup(camposExtras);
  }
}

var alunos = new Object();

alunos.iniciar = function(){
    $("body").delegate(".estrelaVazia16", "click", function(){
       var enrolid = $(this).data('enrolmentid');
       popUp.abre("confirmaUploader","Uploader","Tem certeza de que deseja conceder a " + $("#uNome_" + enrolid).html() + " permissão para realizar uploads e submeter formulários?","não","sim","confirm","m", "verde", "bola", "",function(){alunos.definirUploader(enrolid);});
    });
    $("body").delegate(".estrelaCheia16","click", function(){
        var enrolid = $(this).data('enrolmentid');
        popUp.abre("cancelaUploader","Uploader","Tem certeza de que deseja remover a permissão de " + $("#uNome_" + enrolid).html() + " para realizar uploads e submeter formulários?","não","sim","confirm","m", "vermelho", "bola", "",function(){alunos.removerUploader(enrolid);});
    });
};
alunos.definirUploader = function(idEnrol){
    var form = {acao: 'confereUpload', idEnrol: idEnrol};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#botUp_" + idEnrol).removeClass('estrelaVazia16').addClass('estrelaCheia16');
            popUp.fecha();
        }
    });

};
alunos.removerUploader = function(idEnrol){
    var form = {acao: 'removeUpload', idEnrol: idEnrol};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#botUp_" + idEnrol).removeClass('estrelaCheia16').addClass('estrelaVazia16');
            popUp.fecha();
        }
    });
};

var bemvindo = new Object();

bemvindo.iniciar = function(){
    bemvindo.telaInicial();
    $('body').delegate('.boasVindas',"click", function(){
        $("#fStatus").val($(this).data('status'));
        filtros.filtrar();
        popUp.fecha("boasvindas");
    });
};

bemvindo.telaInicial = function(){


    var total = new Array();
    $('.groupContainer').each(function(){
        var item = $(this);
        var status = item.data('status');
        if(total[status] === undefined){
            total[status] = {n : 1, classname: item.data('classname')};
        }else{
            total[status].n++;
        }
    });
    var html = '';
    $.each(total, function(key, value){
        if(value !== undefined){
            var name = $(".s_" + key).html();
            html += '<div class = "boasVindas ' +value.classname + '" data-status = "' + key + '">' + name +'<br>' + value.n +'</div>';

        }
    });
    popUp.abre("boasvindas","Bem Vindo!",html,"não","sim","message","800px-300px", "branco", "bola");
};

var filtros = new Object();

filtros.iniciar = function(){
    $("#filtrar").on('click', function(){
        filtros.filtrar();
    });
};

filtros.filtrar = function(){
    var status = $("#fStatus").val();
    var nomeGrupo = $("#fNomeGrupo").val();
    var nomeAluno = $("#fNomeAluno").val();
    var nomeProfessor = ($("#fNomeProfessor").length > 0) ? $("#fNomeProfessor").val() : '';

	var sPageURL = window.location.search.substring(1);
    var sURLVariables = sPageURL.split('&');
    var sParameterName = sURLVariables[0].split('=');
    var id = sParameterName[1];

	var url = window.location.origin+"/mod/tcccafeead/view.php?id="+id;
  url = window.location.href;
  var partes = url.split("?");

  console.log(partes[0]);

  url =partes[0]+"?id="+id;

	if( nomeGrupo != '' ){
		url += "&nomeGrupo="+nomeGrupo;
	}

	if( nomeAluno != '' ){
		url += "&nomeAluno="+nomeAluno;
	}

	if( nomeProfessor != '' ){
		url += "&nomeProfessor="+nomeProfessor;
	}

	if( status != '' ){
		url += "&status="+status;
	}

    window.location.href = url;

};



var formulario = new Object();

formulario.iniciar = function(){
    $("body").delegate('.icoformConfirm','click', function(){
        if($(this).data("per") === 'coord'){
			var comentario = $('#comentario_'+$(this).data('group')+'_'+$(this).data('formtype'));
            formulario.avisoCancela($(this).data('group'), $(this).data('formtype'), comentario.val() );
        }
    });

	 $("body").delegate('.icoformCancela','click', function(){
        if($(this).data("per") === 'coord'){
			var comentario = $('#comentario_'+$(this).data('group')+'_'+$(this).data('formtype'));
            formulario.avisoCancela($(this).data('group'), $(this).data('formtype'), comentario.val() );
        }
    });

	$("body").delegate('.icoformCancelaMorto','click', function(){
        if($(this).data("per") === 'coord' || $(this).data("per") === 'prof' ){
			var comentario = $('#comentario_'+$(this).data('group')+'_'+$(this).data('formtype'));
            formulario.avisoNegado($(this).data('group'), $(this).data('formtype'), comentario.val() );
        }
    });

	$("body").delegate('.txtComentario','focusout', function(){
        if($(this).data("per") === 'coord' || $(this).data("per") === 'prof' ){
			var comentario = $('#comentario_'+$(this).data('group')+'_'+$(this).data('formtype'));
			var old = $(this).data('old');
			if( comentario.val() != '' && comentario.val() != old ){
				formulario.salvaComentario($(this).data('group'), $(this).data('formtype'), comentario.val() );
			}
        }
    });

    $("body").delegate('.icoformConfirmMorto','click', function(){
		var comentario = $('#comentario_'+$(this).data('group')+'_'+$(this).data('formtype'));
        formulario.avisoConfirma($(this).data('group'), $(this).data('formtype'), comentario.val());

    });
};
formulario.cancelaItem = function(idGroup, idFormType, comentario){
    var form = {acao: 'formCancelaItem',idTcc: $("#idTcc").val(), idGroup: idGroup, idFormType: idFormType, comentario:comentario};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#groupContainer_" + idGroup).removeClass('enviado').addClass('aguardandoAprovacao');
            $("#bot_" + idGroup + "_" + idFormType).removeClass('icoformConfirm').addClass('icoformConfirmMorto');
			$("#bot_nao_" + idGroup + "_" + idFormType).removeClass('icoformCancela').addClass('icoformCancelaMorto');
            popUp.fecha();
            grupos.atualizaStatus(idGroup);
        }
    });
};

formulario.negaItem = function(idGroup, idFormType, comentario){
    var form = {acao: 'formNegaItem',idTcc: $("#idTcc").val(), idGroup: idGroup, idFormType: idFormType, comentario:comentario};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#groupContainer_" + idGroup).removeClass('enviado').addClass('aguardandoAprovacao');
            $("#bot_" + idGroup + "_" + idFormType).removeClass('icoformConfirm').addClass('icoformConfirmMorto');
			$("#bot_nao_" + idGroup + "_" + idFormType).removeClass('icoformCancelaMorto').addClass('icoformCancela');
            popUp.fecha();
            grupos.atualizaStatus(idGroup);
        }
    });
};

formulario.confirmaItem = function(idGroup, idFormType, comentario){
    var form = {acao: 'formConfirmaItem',idTcc: $("#idTcc").val(), idGroup: idGroup, idFormType: idFormType, comentario:comentario};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#groupContainer_" + idGroup).removeClass('aguardandoAprovacao').addClass('enviado');
            $("#bot_" + idGroup + "_" + idFormType).removeClass('icoformConfirmMorto').addClass('icoformConfirm');
			$("#bot_nao_" + idGroup + "_" + idFormType).removeClass('icoformCancela').addClass('icoformCancelaMorto');
            popUp.fecha();
            grupos.atualizaStatus(idGroup);
        }
    });
};

formulario.salvaComentario = function(idGroup, idFormType, comentario){
    var form = {acao: 'formSalvaComentario',idTcc: $("#idTcc").val(), idGroup: idGroup, idFormType: idFormType, comentario:comentario};
    $.post(urlAjax, form, function(data){
        if(data === '1'){
            $("#groupContainer_" + idGroup).removeClass('aguardandoAprovacao').addClass('enviado');
            popUp.fecha();
            grupos.atualizaStatus(idGroup);
        }
    });
};


formulario.avisoCancela = function(idGroup, idFormType, comentario){
    popUp.abre("cancelaForm","Cancelamento","Deseja desaprovar este item? Os alunos somente poderão enviar postagem quando todos itens forem aprovados.","não","sim","confirm","m", "vermelho", "bola", "",function(){formulario.cancelaItem(idGroup, idFormType, comentario);});
};

formulario.avisoNegado = function(idGroup, idFormType, comentario){
    popUp.abre("cancelaForm","Cancelamento","Deseja negar este item? Os alunos somente poderão enviar postagem quando todos itens forem aprovados.","não","sim","confirm","m", "vermelho", "bola", "",function(){formulario.negaItem(idGroup, idFormType, comentario);});
};

formulario.avisoConfirma = function(idGroup, idFormType, comentario){
    popUp.abre("confirmaForm","Aprovação","Deseja confirmar este item?","não","sim","confirm","m", "verde", "bola", "",function(){formulario.confirmaItem(idGroup, idFormType, comentario);});
};


var grupos = new Object();

grupos.iniciar = function(){
    $('.groupContainer > table td:not(:last-child)').on("click", function(){
        var group = $(this).parent().parent().parent().data('group');
        if($("#groupDetalhes_" + group).css('display') !== 'block'){
            $("#groupDetalhes_" + group).slideDown();
        }else{
            $("#groupDetalhes_" + group).slideUp();
        }
    });
    grupos.stati = jQuery.parseJSON($("#groupStati").html());

};

grupos.atualizaStatus = function(idGroup){
    var form = {acao: 'grupoVerificaStatus',idTcc: $("#idTcc").val(), idGroup: idGroup};
    $.post(urlAjax, form, function(data){
        grupos.mudaStatus(idGroup, data);
        return(data);
    });
};
grupos.mudaStatus = function(groupid, status){
    $("#status_" + groupid).html(grupos.stati[status].name);
    $("#groupContainer_" + groupid).attr('class', '').addClass('groupContainer').addClass(grupos.stati[status].classname);

	if( status == 7 ){
		$("#nt_" + groupid).prop( "disabled", true );
		$("#na_" + groupid).prop( "disabled", true );
		$("#tema_"+groupid).prop( "disabled", true );
		$("#data_"+groupid).prop( "disabled", true );
	}else{
		$("#nt_" + groupid).prop( "disabled", false );
		$("#na_" + groupid).prop( "disabled", false );
		$("#tema_" + groupid).prop( "disabled", false );
		$("#data_" + groupid).prop( "disabled", false );
	}
};

var subCabecalho = new Object();

var nota = new Object();

nota.iniciar = function(){

	$("select.notaGrupo").on("change", function(){
        var clicado = $(this);
        if( $(this).val() != "" && $(this).val() != "0" && $(this).val() != "0.0" ){
        	nota.atualiza(clicado.data('tcc'), clicado.data('group'), clicado.data('type'), clicado.data('banca'), clicado.data('postagem'), $(this).val(), clicado.data('levelid'));
        }
    });

	$("input.notaGrupo").on("change", function(){
        var clicado = $(this);
        if( $(this).val() != "" && $(this).val() != "0" && $(this).val() != "0.0" ){
        	nota.atualiza(clicado.data('tcc'), clicado.data('group'), clicado.data('type'), clicado.data('banca'), clicado.data('postagem'), $(this).val(), clicado.data('levelid'));
        }
    });

	$("input.notaGrupo").on("blur", function(){
        var clicado = $(this);
        if( $(this).val() != "" && $(this).val() != "0" && $(this).val() != "0.0" ){
        	nota.atualiza(clicado.data('tcc'), clicado.data('group'), clicado.data('type'), clicado.data('banca'), clicado.data('postagem'), $(this).val(), clicado.data('levelid'));
        }
    });


    $(".extraGrupo").on("blur", function(){
        var clicado = $(this);
        if( $(this).val() != undefined ){
        	nota.atualiza(clicado.data('tcc'), clicado.data('group'), clicado.data('type'), clicado.data('banca'), clicado.data('postagem'), clicado.data('valor'), clicado.data('levelid'));
        }
    });

    $(".extraGrupo").on("click", function(){
        var idSucesso = (Number($(this).data('type')) === 1)? '#sucessoNT_' + $(this).data('group') : '#sucessoNA_' + $(this).data('group');
        $(idSucesso).css("display", 'none');
    });


    $(".fecharNotas").on("click", function(){
        var campo = $(this);
        var valor = ($("#nm_" +campo.data('group')).length > 0)? $("#nm_" +campo.data('group')).val() : $("#nt_" +campo.data('group')).val() ;
        if(valor !== '' && valor !== '--'){
            var html  = 'Esta ação irá conceder nota <b>' + valor + '</b> <br> para todos integrantes do grupo <b>' +$("#nomeGrupo_" + campo.data('group')).html() + '</b>.';
                html += '<br>O status do grupo será alterado para "Finalizado" <br>e ele não será mais editável para os professores.';
            popUp.abre("confirmaFechamento","Finalizar",html,"cancela","confirma","confirm","m", "verde", "bola", "",function(){
                nota.fechar(campo.data('tcc'), campo.data('group'), valor);
            });
        }else{
            var html = 'Para realizar o fechamento <br>é preciso que haja uma nota final';
            popUp.abre("alertaFechamento","Fechamento",html,"não","sim","alert","m", "vermelho", "bola", "");
        }
    });
    $(".abrirNotas").on("click", function(){

        var campo = $(this);
        var html  = 'Esta ação tornará disnponível <br> o lançamento de notas<br> para todos professores <br> do grupo <b>' +$("#nomeGrupo_" + campo.data('group')).html() + '</b>.';
                html += '<br><br>O status do grupo será alterado<br> para "Em correção".';
            popUp.abre("confirmaAbertura","Reabrir",html,"cancela","confirma","confirm","m", "verde", "bola", "",function(){
                nota.abrir(campo.data('tcc'), campo.data('group'));
            });

    });
};
nota.atualiza = function(tcc, group, type, banca, postagem, valor, levelid){

	var tema = "";
	var data = "";
	var unixtime = "";

	if(  $("#tema_"+group) != null && $("#tema_"+group).val() != null ){
		tema = $("#tema_"+group).val();
	}

	if ( type == 2 ){

		data = "";
		unixtime = 0;
		console.log("Tema:" +tema);
		console.log("Grupo: " + group );
		console.log( data );
		console.log( unixtime );
	}

	if( type == 3 ){

		console.log("Tema:" +tema);
		console.log("Grupo: " + group );
		data = $("#data_"+group).val();
		if( data != null && data != undefined ){
			unixtime = new Date(data).getTime() / 1000
			console.log( data );
			console.log( unixtime );
		}

	}

	if( type == "1" ){
		data = "";
		unixtime = 0;
		console.log("Tema:" +tema);
		console.log("Grupo: " + group );
		console.log( data );
		console.log( unixtime );
	}

    var form = {acao: 'atualizaNota', tcc: tcc, group: group, type: type, banca:banca, postagem:postagem, valor: valor, tema:tema, data:unixtime};
    $.post(urlAjax, form, function(data){
            var idSucesso = (Number(type) === 1)? '#sucessoNT_' + group : '#sucessoNA_' + group;
            $(idSucesso).css("display", 'inline-block');

            var nt = $("#nt_" + group).val();
            var na = $("#na_" + group).val();
            if(nt !== '--' && nt !== '' && na !== '--' && na !== '' && levelid == '2'){
                $("#nm_" + group).val(Math.round((Number(nt) + Number(na)) / 2 * 100) / 100);
            }else if(nt !== '--' && nt !== '' && na !== '--' && na !== '' && levelid == '1'){
            	$("#nm_" + group).val(Math.round((Number(nt) + Number(na)) * 100) / 100);
            }else{
                $("#nm_" + group).val('--');
            }
            console.log(data);
            grupos.atualizaStatus(group);

    });
};
nota.fechar = function(tcc, group, valor){
	console.log(tcc);
	console.log(group);
	console.log(valor);
    var form = {acao: 'fecharNotas', tcc: tcc, group: group, valor: valor};
    $.post(urlAjax, form, function(data){
    	console.log(data);
        var dados = $.parseJSON(data);
        popUp.fecha("confirmaFechamento");
        if(dados.status !== 'ok'){
            popUp.abre("erroFechamento","ERRO",dados.mensagem,"OK","sim","alert","m", "vermelho", "bola", "");

        }
        grupos.mudaStatus(group, 7);
        $("#nt_" + group + ", #na_" + group + ", #nm_" + group).attr("disabled", "disabled");
    });

};
nota.abrir = function(tcc, group){

    var form = {acao: 'abrirNotas', tcc: tcc, group: group};
    $.post(urlAjax, form, function(data){ console.log(data);
        var dados = $.parseJSON(data);
        popUp.fecha("confirmaAbertura");
        if(dados.status !== 'ok'){
            popUp.abre("erroAbertura","ERRO",dados.mensagem,"OK","sim","alert","m", "vermelho", "bola", "");

        }
        grupos.atualizaStatus(group);
        //$("#nt_" + group + ", #na_" + group + ", #nm_" + group).attr("disabled", "disabled");
    });

};

var notaStage = new Object();

notaStage.iniciar = function(){
    $(".notaStage").on("change", function(){
        var clicado = $(this);
        notaStage.atualizaNota(clicado.data('tcc'), clicado.data('stage'), clicado.data('group'), $(this).val(), clicado.data('levelid'));

    });
    $(".caixaDeNotasPost input").on("click", function(){
        $("#sucesso_"+$(this).data('tcc')+"_"+$(this).data('stage')+"_"+$(this).data('group')).css("display", 'none');
    });
};
notaStage.atualizaNota = function(tcc, stage, group, valor, levelid){


    var form = {acao: 'atualizaStageGrade', tcc: tcc, stage: stage, group: group, valor: valor};
    $.post(urlAjax, form, function(data){console.log(data)
        if(data === '1'){
            $("#sucesso_"+tcc+"_"+stage+"_"+group).css("display", 'inline-block');
            var nNotas = 0;
            var total = 0;
            $("#groupContainer_" + group + ' .notaStage').each(function(){
                if($(this).val() !== ''){
                    nNotas++;
                    total += Number($(this).val());
                }

            });
            if($("#nt_" + group).length > 0){
                var media = Math.round((total )* 100) / 100;
                $("#nt_" + group).val(media);
                if($("#nt_" + group).val() !== '--' && $("#na_" + group).val() !== '--' && levelid == '2'){
                    var na = Number($("#na_" + group).val());
                    $("#nm_" + group).val(Math.round((media + na) / 2 * 100) / 100);
                }

                if($("#nt_" + group).val() !== '--' && $("#na_" + group).val() !== '--' && levelid == '1'){
                    var na = Number($("#na_" + group).val());
                    $("#nm_" + group).val(Math.round((media + na) * 100) / 100);
                }
            }
			var clicado = $("#nt_" + group);
            grupos.atualizaStatus(group);
			nota.atualiza(clicado.data('tcc'), clicado.data('group'), clicado.data('type'), clicado.data('banca'), clicado.data('postagem'), clicado.val(), clicado.data('levelid'));
        }
    });
};

subCabecalho.iniciar = function(){
    $(".subCabecalho").on("click", function(){
        var bloco =  $("#" + $(this).data('block'));
        if((bloco).css("display") === 'none'){
            $(this).removeClass('fechado').addClass('aberto');
            bloco.slideDown();
        }else{
            $(this).removeClass('aberto').addClass('fechado');
            bloco.slideUp();
        }
    });
};



$("body").ready(function(){
    alunos.iniciar();
    formulario.iniciar();
    grupos.iniciar();
    subCabecalho.iniciar();
    nota.iniciar();
    notaStage.iniciar();
    filtros.iniciar();
    //bemvindo.iniciar();
    iniciaCamposNumericos();

    //OPERAÇÕES COM ARQUIVOS
    $("body").delegate('.envios a',"click", function(){
        var groupid = $(this).parent().parent().data('groupid');
        var form = {acao: 'emCorrecao', groupid: groupid};
        $.post(urlAjax, form, function(data){
            if(data === '4'){
                grupos.mudaStatus(groupid, 4);
            }
        });
    });
    $(".upload").on("change", function(e){
        var groupid = $(this).data('groupid');
        var stage = $(this).data('stage');
        var files = e.target.files;
        var formData = new FormData();
		var maxfile = $(this).data('maxfile');
		var arquivosArquivos = $('a.stage_p_'+stage);
		var role = $(this).data('role');

		console.log( arquivosArquivos.length );
		console.log( "Role " + role);

		if( role == 'prof' || role == 'coord' ){

			$.each(files, function(key, value)
			{
				formData.append(key, value);
			});


			arquivos.upload($("#idTcc").val(), groupid, stage, 'correcoes', formData);
		} else {


			if( arquivosArquivos.length < maxfile ){
				var sobra = maxfile - arquivosArquivos.length;

				$.each(files, function(key, value)
				{
					formData.append(key, value);
				});

				if( files.length <= sobra){
					arquivos.upload($("#idTcc").val(), groupid, stage, 'correcoes', formData);
				}else{
					//arquivos.upload($("#idTcc").val(), groupid, stage, 'correcoes', formData);
				}
			}else{
				//arquivos.upload($("#idTcc").val(), groupid, stage, 'correcoes', formData);
			}
		}
    });



    $(".downloadProf").click(function(e){
    	carregando.abre('download do arquivo');
    	e.preventDefault();
    	var link = $(this).attr("href");
    	var groupid = $(this).attr("data-groupid");
    	window.location.href = link;
    	carregando.fecha();
    	grupos.mudaStatus(groupid, 4);
    });

    $("body").delegate('.excluir',"click", function(){
        var groupid = $(this).data('groupid');
        var stage = $(this).data('stage');
        var tipo = $(this).data('tipo');
        var nome = $(this).data('nome');
        var tccid = $("#idTcc").val();

        var html = 'Tem certeza que deseja excluir o arquivo <br><strong>"' + nome + '"</strong>?<br> Esta ação não poderá ser revertida!';
        popUp.abre("excluiArquivo","Excluir Arquivo", html,"não","sim","confirm","m", "vermelho", "bola", "",function(){
            arquivos.excluir(tccid, groupid, stage, tipo, nome);
        });
    });
$("#teste").on('click', function(){

});
});
