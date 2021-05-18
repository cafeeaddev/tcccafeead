var popUp = new Object();
popUp.numero = 0;
popUp.carregando = '';
popUp.abre = function(id, titulo, conteudo, textoBotao1, textoBotao2, tipo, tamanho, cor, animacao, funcaoBotao1, funcaoBotao2){
    popUp.numero++;
    var id = (id === undefined || id === '')? 'popUp_' + popUp.numero : id ;
    var cor = (cor === undefined || cor === '')? 'normal': cor;
    var classesPop = 'popUp '+ tipo + ' ' + cor + ' ' + tamanho + ' ' + animacao;
    var style = '';
    var tamanhoArray = tamanho.split('-');
    if(tamanhoArray[1] !== undefined){
        style = 'width: ' + tamanhoArray[0] + '; height:' + tamanhoArray[1] + ';';
    }
    var classesFundo = 'popUpFundo ' + cor + ' ' + animacao;
    var xis = ''; var bots = '';
    $("html").css('overflow', 'hidden');
    switch(tipo){
        case 'alert' : 
                bots = '<div><button id="'+ id +'_botao1">' + textoBotao1 + '</button></div>';
            break;
        case 'confirm' : 
                bots = '<div><button id="'+ id +'_botao1">' + textoBotao1+ '</button><button id="'+ id +'_botao2">' + textoBotao2 + '</button></bot>';
            break;
        case 'message' : 
                xis = '<span id="xis_' + id + '" class="popUpXis">x</span>';
            break;
    }
    
    var html = '<div id="fundo_' + id + '"></div><div id="' + id + '" data-ani="' + animacao + '" style="' + style + '"><h1 data-popid="'+id+'">' + titulo + xis +'</h1><div class="popUpD1"><div><p>' + conteudo + '</p></div></div>' + bots + '</div>';
    
    $("body").prepend(html);
    $("#fundo_" + id).addClass(classesFundo);
    $( "#" + id).css('top', mouseY - $(window).scrollTop()).css('left',mouseX - $(window).scrollLeft()).addClass(classesPop);
    $("#" + id + '_botao1').on("click", function(){
        if((funcaoBotao1 !== undefined) && (funcaoBotao1 !== '')){
            funcaoBotao1();
            
        }else{
            popUp.fecha(id);
        }
    });
    $("#" + id + '_botao2').on("click", function(){
        if((funcaoBotao2 !== undefined) && (funcaoBotao2 !== '')){
            funcaoBotao2();
            
        }else{
            popUp.fecha(id);
        }
    });
    if(tipo == 'message'){
        $("#xis_" + id + ", #fundo_" + id).click(function(){
            if((funcaoBotao1 !== undefined) && (funcaoBotao1 !== '')){
                funcaoBotao1();
            }else{
                popUp.fecha(id);
            }
        });
    }
};
popUp.fecha = function(id){
    
    if(id !== undefined){
        if($(".popUp").length === 1){
            $("html").css('overflow', 'auto');
        }
        var animacao = $('#' + id).data('ani'); 
        $("#" + id + ', #fundo_'+id).removeClass(animacao).addClass(animacao + "Out"); 
        $("#fundo_" + id).fadeOut(505, function(){
            $("#fundo_" + id).remove();
            $("#" + $(this).attr("id") + ", #" + id).remove();
        });
    }else{
        $(".popUp").each(function(){
            if($(".popUp").length === 1){
                $("html").css('overflow', 'auto');
            }
            var id= $(this).attr('id');
            var animacao = $('#' + id).data('ani'); 
            $("#" + id + ', #fundo_'+id).removeClass(animacao).addClass(animacao + "Out"); 
            $("#fundo_" + id).fadeOut(505, function(){
                $("#fundo_" + id).remove();
                $("#" + $(this).attr("id") + ", #" + id).remove();
            });
            
        });
    }
    
};
popUp.atualizaPosicao = function(){
    var carregando = $("#" + popUp.carregando);
    $("#" + popUp.carregando + ' h1').css('cursor', 'move');
    var mprx = mouseX - $(window).scrollLeft();
    var mpry = mouseY - $(window).scrollTop();
    carregando.removeClass(carregando.data('ani'));
    carregando.css('display', 'block');
    carregando.css('top', mpry  + (carregando.height() / 2) - 10);
    carregando.css('left', mprx  - (carregando.width() / 4));
};
popUp.iniciar = function(){
    $('body').delegate('.popUp h1', 'mousedown', function(){
        popUp.carregando = $(this).data('popid');
    });
    $('body').delegate('.popUp h1', 'mouseup', function(){
       popUp.carregando = '';
        $(this).css('cursor', 'default');
    });
};
popUp.iniciar();

$("#gerar").on("click", function(){
    var funcao1 = ($("#funcao1").val() === '')? '""' : $("#funcao1").val();
    var funcao2 = ($("#funcao2").val() === '')? '""' : $("#funcao2").val();
    var tamanho = ($("#altura").val() !== '' && $("#largura").val() !== '')?$("#largura").val() + '-' + $("#altura").val() : $("#tamanho").val();
    var string = 'popUp.abre("'+$("#id").val()+'","'+$("#titulo").val()+'","'+$("#conteudo").val()+'","'+$("#botao1").val()+'","'+$("#botao2").val()+'","'+ $("#tipo").val()+'","'+ tamanho +'", "'+$("#cor").val()+'", "'+$("#animacao").val()+'", '+funcao1+','+funcao2+');';
    
    $("#codigo").html(string);
    eval(string);
    
});
$("#altura, #largura").on("change", function(){
    if($("#altura").val() === '' || $("#largura").val() === ''){
        $("#tamanho").attr("disabled", false);
    }else{
         $("#tamanho").attr("disabled", true);
    }
});
// CARREGANDO
var carregando = new Object();

carregando.abre = function(mensagemInicial){
  mensagemInicial = (mensagemInicial !== undefined)? mensagemInicial : 'Carregando...';
  var html = '<div id="carregandoFundo"></div><div id="carregandoNovo"><span></span> <span></span> <span></span> <span></span> <span></span> <span></span> <span></span> <span></span></div><div id="carregandoNovoTexto">' + mensagemInicial + '</div>';
  $("body").append(html);
  $("#carregandoFundo, #carregandoNovo, #carregandoNovoTexto").fadeIn("fast");
};

carregando.mensagem = function(texto){
  texto = (texto === undefined)? '': texto;
  $("#carregandoNovoTexto").html(texto);
};

carregando.fecha = function(){
  $("#carregandoFundo, #carregandoNovo, #carregandoNovoTexto").fadeOut('fast', function(){
      $("#carregandoFundo, #carregandoNovo, #carregandoNovoTexto").remove();
  });
};
//rastreando mouse
$(document).mousemove(function(e) {
    mouseX = e.pageX;
    mouseY = e.pageY;
    mouseXR = $("body").width() - mouseX;
    if(popUp.carregando !== ''){popUp.atualizaPosicao();}
});

