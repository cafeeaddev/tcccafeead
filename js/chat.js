//CRIANDO UMA VARIÁVEL COM A URL A QUAL AS REQUISIÇÕES AJAX DEVEM SER ENVIADAS
var urlTemp = window.location.href.split('/view.php');
var urlChat = urlTemp[0] + '/acoesListaDeGrupos.php';

var chat = new Object();
chat.timer = '';
chat.idElemento = '';
chat.tccid = 0;
chat.groupid = 0;
chat.iniciar = function(){
    chat.carregaTextoPreDefinido();
    $(".groupContainer > table td:last-child, .chatA").on("click", function(){
        chat.abrirJanela($("#idTcc").val(), $(this).data("groupid"), $(this).data('groupname'), $(this).data('institution'));
    });
    $("body").delegate('.enviaM', 'click', function(){
        chat.enviaMensagem($("#chatI_"+ $(this).data('tccid') + '_' + $(this).data('groupid')).val());
    });
    $( "body" ).delegate('.chatBox textarea', 'keyup',function(e) {
        var code = e.keyCode || e.which;
        if(code == 13) {
          chat.enviaMensagem($(this).val());
        }
    });
    $( "body" ).delegate('.chatBox textarea', 'click',function(e) {
        $('#lpd_' + $(this).data('id')).fadeOut();
    });
    $( "body" ).delegate('.preBot', 'click',function(e) {
        $('#lpd_' + $(this).data('id')).fadeToggle();
    });
    $( "body" ).delegate('.preBot > div > div', 'click',function(e) {
        var idElemento = $(this).parent().parent().data('id');

        $('#chatI_' + idElemento).val($(this).data('text')).focus();
    });
    setInterval(function(){chat.consultaNovas();}, 30000);
};
chat.listaTextoPreDefinido = '';

chat.carregaTextoPreDefinido = function(){
    var form = {acao: 'chatBuscaTextosPreDefinidos'};
    $.post(urlChat, form, function(data){
        chat.listaTextoPreDefinido = data;
    }, 'json');
};

chat.abrirJanela = function(tccid, groupid, groupname, institution){
        chat.idElemento = tccid + '_' + groupid;
        chat.tccid = tccid;
        chat.groupid = groupid;
        var html = '';
        var lista_tpd = '';
        $.each(chat.listaTextoPreDefinido, function(){
            lista_tpd += '<div data-text="' + this.full_text + '">' + this.short_text + '</div>';
        });

        var botPre = (institution === 1)? '<div class="preBot chatAtu" data-id = "' + chat.idElemento +'"><div id = "lpd_' + chat.idElemento +'" class="listaPreDef">' + lista_tpd + '</div></div>' : '' ;
        var inputStyle = (institution === 1)? 'width: 80%;' : '' ;
        html = '<div class="chatBox"><div id="chatLinhas_' + chat.idElemento + '" class="chatLinhas"><div id="chatCarregando_' + chat.idElemento + '" class="chatCarregando">carregando...</div></div><div>' + botPre + '<textarea id="chatI_' + chat.idElemento + '" data-id="' + chat.idElemento + '" data-institution="' + institution + '" style="'+inputStyle+'" DISABLED/></textarea><button class="enviaM" data-tccid="' + tccid + '" data-groupid="' + groupid+ '">Enviar</button></div></div>';
        popUp.abre("chat_"+groupid, 'Comentário - ' + groupname, html,"","","message","650px-500px", "branco", "bola", function(){chat.fecharJanela("chat_"+groupid);});
        
        var form = {acao: 'chatBusca',idTcc: tccid, idGroup: groupid};
        $.post(urlChat, form, function(data){ console.log(data)
            var dados = jQuery.parseJSON(data);
            $("#chatCarregando_" + chat.idElemento).css('display', 'none');
            $.each(dados.resultados, function(){
                var html = chat.montaLinha(this.sendername, this.datesend, this.message, this.minhaMensagem, this.institution);
                $("#chatLinhas_" + chat.idElemento).prepend(html);
            });
            chat.rolaFim();
            
            $("#chatI_" + chat.idElemento).prop('disabled', false).focus();
            chat.timer = setInterval(function(){ chat.atualizar(); }, 5000);
            
        });
};

chat.atualizar = function(forceParaBaixo){
        //vefifico se o elemento está com o scroll para baixo
        var scrollAtual = $("#chatLinhas_" + chat.idElemento).scrollTop() + 10;
        var scrollMenosAltura = $("#chatLinhas_" + chat.idElemento).prop('scrollHeight') -  $("#chatLinhas_" + chat.idElemento).height()
        var scrollBottom = scrollAtual >=  scrollMenosAltura;
        var form = {acao: 'chatAtualiza',idTcc: chat.tccid, idGroup: chat.groupid};
        $.post(urlChat, form, function(data){
            var dados = jQuery.parseJSON(data);
            var novasM = 0;
            $.each(dados, function(){ 
                var html = chat.montaLinha(this.sendername, this.datesend, this.message, this.minhaMensagem, this.institution);
                $("#chatLinhas_" + chat.idElemento).append(html);
                novasM++;
                
            });
            if(novasM > 0){
                if(scrollBottom || forceParaBaixo){ 
                    chat.rolaFim(true); 
                }
            }
        });
};

chat.enviando = 0;
chat.enviaMensagem = function(mensagem){
    
    if(chat.enviando === 0){
        chat.enviando = 1;
        $("#chatI_" + chat.idElemento).prop('disabled', true);
        var form = {acao: 'chatEnviaMensagem',idTcc: chat.tccid, idGroup: chat.groupid, mensagem: mensagem, institution: $("#chatI_" + chat.idElemento).data('institution')};
        $.post(urlChat, form, function(data){
            //caso seja mensagen institucional (do professor), caso caso seja a primeira (desta sessão) para este grupo, verifica permissões
//            if($("#chatI_" + chat.idElemento).data('institution') === 1){
//                
//            }
            chat.atualizar(true);
            chat.enviando = 0;
            $("#chatI_" + chat.idElemento).prop('disabled', false).val('').focus();
        });
    }
};

chat.fecharJanela = function(id){
    popUp.fecha(id);
    if(chat.timer !== undefined && chat.timer !== ''){
        clearInterval(chat.timer);
    }
    $("#cb_"+chat.groupid).fadeOut();
    chat.idElemento = '';
    chat.tccid = 0;
    chat.groupid = 0;
};

chat.montaLinha = function(sender, data, message, minha, inst){
    var extraClass = (minha === true)? 'chatMinhaMensagem' : '';
    var extraClass2 = (inst == 1)? 'chatInst ' : '';
    var html = '<div class="chatLinha ' + extraClass + ' ' + extraClass2 + '"><div>' + sender + ' <span>' + data + '</span></div><div> ' + message + '</div></div>';
    return html;
};

chat.consultaNovas = function(){
    var form = {acao: 'chatConsultaNovas', tcc: $("#idTcc").val()};
    $.post(urlChat, form, function(data){
        
        $.each(data, function(key, value){
            
            if($("#cb_" + key).css('display') === 'none'){
                $("#cb_" + key).html(value).fadeIn(); 
            }
        });
    }, 'json');
};


chat.rolaFim = function(){
    $("#chatI_" + chat.idElemento).val('');
    $("#chatLinhas_" + chat.idElemento).scrollTop($("#chatLinhas_" + chat.idElemento).prop('scrollHeight') + 50);
};

$("body").ready(function(){
    chat.iniciar();
});