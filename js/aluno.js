//CRIANDO UMA VARIÁVEL COM A URL A QUAL AS REQUISIÇÕES AJAX DEVEM SER ENVIADAS
var urlTemp = window.location.href.split('/view');
var urlAlunos = urlTemp[0] + '/acoesAlunos.php';
var urlAjax = urlTemp[0] + '/acoesListaDeGrupos.php';

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
    console.log("aluno.js");
};

grupos.atualizaStatus = function(idGroup){
    var form = {acao: 'grupoVerificaStatus',idTcc: $("#idTcc").val(), idGroup: idGroup};
    $.post(urlAjax, form, function(data){
        grupos.mudaStatus(idGroup, data);
        return(data);
    });
};
grupos.mudaStatus = function(groupid, status){
    $("#status_" + groupid).html(grupos.stati[status-1].name);
    $("#groupContainer_" + groupid).attr('class', '').addClass('groupContainer').addClass(grupos.stati[status-1].classname);
};

var menuPostagens = new Object();

menuPostagens.iniciar = function(){
    $(".menuPostagens td").on('click', function(){
        $("#group_" + $(this).data('group') + " .menuPostagens td").removeClass('sSelecionado');
        $(this).addClass('sSelecionado');
        var left = ($(this).data('stage') * 100) -100;
        $("#deslizante_" + $(this).data('group')).animate({
            left: - left+'%'

        }, 500);
    });
    $(".menuHerdados td").on('click', function(){
        $("#group_" + $(this).data('group') + " .menuHerdados td").removeClass('sSelecionado');
        $(this).addClass('sSelecionado');
        var left = ($(this).data('stage') * 100) -100;
        $("#deslizanteH_" + $(this).data('group')).animate({
            left: - left+'%'

        }, 500);
    });
    $(".menuPostagens").each(function(){
        //var item = $(this).data('avancar') + 1;
        //$("#group_" + $(this).data('groupid') + " .menuPostagens td").removeClass('sSelecionado');
        //$("#menuItem_" + $(this).data('groupid') + "_" + item).addClass('sSelecionado');
        //var left = (item * 100) - 100;
        //$("#deslizante_" + $(this).data('groupid')).css('left', - left+'%');
    });
};

$('body').ready(function(){
    grupos.iniciar();
    menuPostagens.iniciar();

    $(".upload").on("change", function(e){
        var groupid = $(this).data('groupid');
        var stage = $(this).data('stage');
        var files = e.target.files;
        var formData = new FormData();

		var maxfile = $(this).data('maxfile');
		var arquivosArquivos = $('a.stage_p_'+stage);
		console.log("MAXFILE " + maxfile);
		console.log("ARQUIVOS" + arquivosArquivos.length );
		if( arquivosArquivos.length < maxfile ){
			var sobra = maxfile - arquivosArquivos.length;

			$.each(files, function(key, value)
			{
				formData.append(key, value);
			});

			if( files.length <= sobra){
				arquivos.upload($("#idTcc").val(), groupid, stage, 'postagens', formData);
			}else{
				alert ("EXCEDEU O LIMITE DE ARQUIVOS");
			}
		}else{
			alert ("EXCEDEU O LIMITE DE ARQUIVOS");
		}
    });
    $(".form button").on("click", function(){
        carregando.abre();
        var form = new Object();

        form.acao = 'gravarFormulario';
        form.tccid = $("#idTcc").val();
        form.groupid = $(this).data('group');
        $.each($("#form_" + form.groupid + ' input, ' + "#form_" + form.groupid + ' select, '+ "#form_" + form.groupid + ' textarea'), function(){
            var nome = 'f_' + $(this).data('formulario');
            form[nome] = $(this).val();
        });

        $.post(urlAlunos, form, function(data){
           console.log(data);

            if(data === '1'){
				carregando.mensagem("Formulário Enviado com Sucesso, aguarde a aprovação!");
				setTimeout(function(){
					carregando.fecha();
				}, 2000);
            }


        });

    });

    $("body").delegate('.excluir',"click", function(){
        var groupid = $(this).data('groupid');
        var stage = $(this).data('stage');
        var tipo = $(this).data('tipo');
        var nome = $(this).data('nome');
        var tccid = $("#idTcc").val();
        console.log(urlAjax);
        var html = 'Tem certeza que deseja excluir o arquivo <br><strong>"' + nome + '"</strong>?<br> Esta ação não poderá ser revertida!';
        popUp.abre("excluiArquivo","Excluir Arquivo", html,"não","sim","confirm","m", "vermelho", "bola", "",function(){
            arquivos.excluir(tccid, groupid, stage, tipo, nome);
        });
    });

});
