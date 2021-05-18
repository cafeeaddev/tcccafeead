var urlUpload = urlTemp[0] + '/upload.php';
var arquivos = new Object();

arquivos.excluir = function(tccid, groupid, stage, tipo, nome){
    var form = {acao: 'excluirArquivo',tccid: tccid, groupid: groupid, stage: stage, tipo: tipo, nome: nome};
    $.post(urlAjax, form, function(data){ console.log(data)
        var dados = jQuery.parseJSON(data);

        var tipoCompleto = (tipo === 'c')? 'correcoes' : 'postagens';
        if(dados.status == 1){
            console.log("#cArquivos_" + groupid + '_' + stage + '_' + tipoCompleto);

            $("#cArquivos_" + groupid + '_' + stage + '_' + tipoCompleto).html(dados.html.html);

            popUp.fecha();
        }

    });
};

arquivos.upload = function(tccid, groupid, stage, tipo, arquivos){


	var urlEnvio = urlUpload + '?tccid=' + tccid + '&groupid=' + groupid + '&stage=' + stage+ '&tipo=' + tipo;
    carregando.abre('carregando arquivos');

    $.ajax({
       url: urlEnvio,  //Server script to process data
       type: 'POST',
       xhr: function() {  // Custom XMLHttpRequest
           var myXhr = $.ajaxSettings.xhr();
           if(myXhr.upload){ // Check if upload property exists
               //myXhr.upload.addEventListener('progress',arquivos.uploadProgresso, false); // For handling the progress of the upload
           }
           return myXhr;
       },
       //beforeSend: beforeSendHandler,
       success: function(data){

           //var dados = jQuery.parseJSON(data);
           dados = data;
           if(dados.status === 1){
               console.log(dados);
               $("#cArquivos_" + groupid + '_' + stage + '_' + tipo).html(dados.html);
               $("#tabelaProtocolos tr:last").after(dados.protocolo.html);
                    var novoStatus = (tipo === 'correcoes')? 'orientado': 'enviado';
                    var idNovoStatus = (tipo === 'correcoes')? 5 : 3;
                    var form = {acao: novoStatus, groupid: groupid};
                    console.log("Novo Status:"+novoStatus + " GROUPO ID:" + groupid + " URL:" + urlAjax);

                    if(tipo !== 'correcoes'){
                        if(parseInt(dados.nArquivos) >= parseInt($('#postagem_'+ groupid + '_' + stage).data('limiteenvios'))){
                            $("#iF_" + groupid + '_' + stage + ", #iFL_" + groupid + '_' + stage).remove();
                        }
                    }
                    $("#iF_" + groupid + '_' + stage).val('');

                    var urlTemp = window.location.href.split('/view.php');

                    var urlAjax = urlTemp[0] + '/acoesListaDeGrupos.php';

                    console.log("VAI FAZER POST PARA " + urlAjax);

                    $.post(urlAjax, form, function(data){
                    	console.log("RECEBEU " + data + " DE RESPOSTA");
                        if(data == idNovoStatus){
                            grupos.mudaStatus(groupid, idNovoStatus);
                        }
                    });
           }

           if ( dados.status === 2 ){
             $("#cArquivos_" + groupid + '_' + stage + '_' + tipo).html(dados.html);
             $("#tabelaProtocolos tr:last").after(dados.protocolo.html);
             var html = 'Erro ao enviar o arquivo! <br><strong>FORMATO INVÁLIDO</strong>!<br> Apenas imagens, documentos word, excel e pdf são permitidos!';
             popUp.abre("erroUpload","Erro de Upload", html,"voltar","ok","confirm","m", "vermelho", "bola", "",function(){
                popUp.fecha();
             });

           }

           carregando.fecha();
       },
       error: function(data){console.log("ERRO:" + data);},
       // Form data
       data: arquivos,
       //Options to tell jQuery not to process data or worry about content-type.
       cache: false,
       contentType: false,
       processData: false
   });
};
