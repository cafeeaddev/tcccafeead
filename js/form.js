//CRIANDO UMA VARIÁVEL COM A URL A QUAL AS REQUISIÇÕES AJAX DEVEM SER ENVIADAS
var urlTemp = window.location.href.split('/course');
var urlAjax = urlTemp[0] + '/mod/tcccafeead/acoesConfiguracao.php';
var notaPostagem = false;

(function ( $ ) {


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

// contem as funções que criam o visual do formulário
var formulario = new Object();

formulario.montar = function(){
    var form = {acao : 'montarFormulario', update: $("input[name=update]").val(), courseid: $('input[name=courseid]').val()};

    $.post(urlAjax, form, function(data){
        var html = data;
        $('.mform').css('display', 'none').before(html);
        postagem.iniciar();
        nivelDeEnsino.iniciar();
        banca.iniciar();
        stagegrade.iniciar();
        selects.iniciar();
        iniciaCamposNumericos();
    });

};
formulario.enviar = function(acao){

    var form = $('#formTCC input, #formTCC textarea, #formTCC select').serializeObject();
    form.acao = acao;
    console.log(form);
    form.course = $('input[name=courseid]').val();
  	if( form.course == null || form.course == ""){
  		form.course = $('input[name=course]').val();
  	}
      form.section = $('input[name=sectionid]').val();
  	if(form.section == null || form.section == ""){
  			form.section = $('input[name=section]').val();
  	}
    form.update = $("input[name=update]").val();
	  console.log(form);

    //console.log(form);
    popUp.fecha("confirmacao");
    carregando.abre('redirecionando');
    var urlCourse = urlTemp[0] + '/course/view.php?id=' + form.course;
     $.post(urlAjax, form, function(data){
          var retorno = parseInt(data);
          console.log(form );
          //if(isNaN(data)){
            window.location.href = urlCourse;
          //}
      });
};
//formulario.confirmar = function(){
//    if($(".obrigatorio").val() === ''){
//        $(this).addClass('erro');
//    }else{
//        $(this).removeClass('erro');
//    }
//};

formulario.iniciar = function(){
    formulario.montar();


    $("body").delegate("#fEnviar", "click", function(){formulario.enviar($(this).data('acao'));});
    $("body").delegate("#fConfirmar", "click", function(){
        var confirmacao = $(".obrigatorio").confirmaobrigatorio();
        var dates = ((new Date($('#favailablefrom').val()).getTime() > new Date($('#favailableto').val()).getTime()))? 0 : 1;
		var naoTemPostagem = $('#stagegrade').prop('checked');
		var temPostagem = $('#stagegrade2').prop('checked');

		console.log( naoTemPostagem);
		console.log(temPostagem );
        var postDates = 1;
        $(".nFiles").each(function(){
            if($(this).val() === '0' || $(this).val() === ''){
                postDates = 0;
            }
        });
        var postPoDates = 1;
        $(".pIni").each(function(){
            var val1 = $(this).val();
            var val2 = $("#pFim"+$(this).data("i")).val();
            postPoDates = ((new Date(val1).getTime() > new Date(val2).getTime()))? 0 : postPoDates;

        });
        var acao = $(this).data('acao');
        var acaoTexto = ($(this).data('acao') === 'atualizar')? 'Atualizar esta': 'Inserir uma';

        if(confirmacao === 1 &&  dates === 1 && postDates === 1 && postPoDates === 1){
            var texto  =  acaoTexto + ' atividade TCC com<br> as seguintes características:<br><br><span style="display: inline-block; width: 80%; text-align: left;"> - se chamará  <b>'+ $("#fName").val() + '</b>;';

            if($("#banca2").attr("checked") === 'checked'){
                texto += '<br>- possuirá banca de TCC;';
            }else{
                texto += '<br>- <b>NÃO</b> possuirá banca de TCC;';
            }
            var tValue = 0;
            $(".formTypes").each(function(){
                var tempValue = $("#" + $(this).attr('id') + ':checked').val();
                if(tempValue !== undefined){
                    tValue++;
                }
            });
            if(tValue > 0){
                var tItem = (tValue > 1) ? 'itens' : 'item';
                texto += '<br>- possuirá um formulário obrigatório com <b>' + tValue + '</b> ' + tItem + ';';
            }
            var tft = 0;
            if($("#importfrom").val() !== ''){
                texto += '<br>- importará trabalhos do curso <b>' + $("#importfrom option:selected").html() + '</b>;';
            }else{
                texto += '<br>- <b>NÃO</b> importará trabalhos;';
            }
            texto += '<br>- os grupos efetuarão <b>' + $(".postagem").length + '</b> postagens.';
            texto += '</span>';

            popUp.abre("confirmacao","Confirme",texto,"não","sim","confirm","g", "verde", "flipX", '', function(){formulario.enviar(acao);});

        }else{
           var texto = '';

           if($(".postagem").length === 0 && temPostagem  ){
               texto += 'Deve haver ao menos uma postagem.<br>';
           }
           if(confirmacao === 0){
               texto += 'Os campos marcados com "*"<br> não podem ficar sem valor.<br>';
           }
           if(dates === 0){
               texto += 'A data de "disponível até" deve ser <br>superior a "disponível a partir".<br>';
           }
           if(postDates === 0){
               texto += 'O número de arquivos  para cada postagem <br>deve ser superior a zero.<br>';
           }
           if(postPoDates === 0){
               texto += 'A data de "término do envio" das <br> postagens deve ser superior a de início.<br>';
           }
            popUp.abre("erro","Atenção!",texto,"ok","sim","alert","m", "vermelho", "bola");
        }
    });
    $("body").delegate(".obrigatorio", "focusout", function(){
        $(this).confirmaobrigatorio();

    });




    var verificaCarregamento = setInterval(function(){
        if($("#postagens").length > 0){
            $('input[type=date]').calendario();
            clearInterval(verificaCarregamento);
        };
    }, 1000);
};


var selects = new Object();
selects.aberto = '';
selects.abrindo = 0;
selects.abrir = function(id){
    if(selects.abrindo === 0){
        $(".fakeSelect").removeClass("fkSVisivel");
        $(".fakeFocus").removeClass("fakeFocus");
        selects.abrindo = 1;
        selects.aberto = id;
        var width = $("#" + id).width() + 25;
        $("#fk_" + id).css('width', width).addClass("fkSVisivel");
        $("#l_" + id).addClass("fakeFocus");
         selects.abrindo = 0;
    }
};
selects.iniciar = function(){
    $(".form select").each(function(){
        var options = '';
        var idAtual = $(this).attr('id');
        var html = "<ul class='fakeSelect' id='fk_" + idAtual + "' data-id='" + $(this).attr('id') + "'>" + options +"</ul>";
        $(this).parent().append(html);
        $('#' + $(this).attr('id') + " option").not('.none').each(function(){
            $("#fk_" + idAtual).append("<li data-value ='" + $(this).val() + "'>" + $(this).html() + "</li>");

        });


    });
    $(".form select").on("mousedown", function(e){
        e.preventDefault();
        selects.abrir($(this).attr("id"));

    });
    $('.fakeSelect li').click(function(){
        var value = $(this).data("value");
        var id = $(this).parent().data('id');
        $("#" + id).val(value);
        $("#" + id).confirmaobrigatorio();
    });

    $('.fakeSelect li').click(function() {
        $(".fakeSelect").removeClass("fkSVisivel");
        $(".fakeFocus").removeClass("fakeFocus");
    });
};

var banca = new Object();

banca.iniciar = function(){

     if( $("#banca2").attr("checked") == undefined || $("#banca").attr("checked") == true || $("#banca").attr("checked") == "true" ){
        console.log("AQUI");
        $("#cBancaMaximaBanca").hide();
    }

    $("#banca").on("click", function(){
        $("#banca2").attr("checked", false);
        $("#cBancaMaximaBanca").hide();
    });
    $("#banca2").on("click", function(){
        $("#banca").attr("checked", false);
        $("#cBancaMaximaBanca").show();
    });
};

var stagegrade = new Object();

stagegrade.iniciar = function(){
    $("#stagegrade").on("click", function(){
		$("#stagegrade2").attr("checked", false);
		notaPostagem = false;

	});
    $("#stagegrade2").on("click", function(){
		$("#stagegrade").attr("checked", false);
		notaPostagem = true;
	});
};

var postagem = new Object();
postagem.removendoCampo = 0;

postagem.criarCampo = function(vezes){
    var vezes = (vezes === undefined)? 1 : vezes;
    for(var i = 1; i <= vezes; i++){
        postagem.numero++;
        var html  = '<div class="postagem" id="postagem' + postagem.numero + '" data-item="' + postagem.numero + '"><span><input type="text" id="Ipostagem' + postagem.numero + '" name="Ipostagem' + postagem.numero + '" value="Postagem ' + postagem.numero + '"/></span><span><span class="ico16 icoExcluir"></span><span class="ico16 icoConfig"></span></span>';
            if (notaPostagem){
				html += '<div>inicio: <input type="date" id="pInicio' + postagem.numero + '" name="pInicio' + postagem.numero + '"/> término: <input type="date" id="pFim' + postagem.numero + '" name="pFim' + postagem.numero + '"/>arquivos por post <input type = "text" class="nFiles apenasNumeros" id="pNumeroArquivos' + postagem.numero + '" name="pNumeroArquivos' + postagem.numero + '" value="1" maxlength="2"/> nota máxima <input type = "text" class="nMaxGrade apenasNumeros" id="pMaxGrade' + postagem.numero + '" name="pMaxGrade' + postagem.numero + '"  maxlength="3"/></span></div>';
			}else{
				html += '<div>inicio: <input type="date" id="pInicio' + postagem.numero + '" name="pInicio' + postagem.numero + '"/> término: <input type="date" id="pFim' + postagem.numero + '" name="pFim' + postagem.numero + '"/>arquivos por post <input type = "text" class="nFiles apenasNumeros" id="pNumeroArquivos' + postagem.numero + '" name="pNumeroArquivos' + postagem.numero + '" value="1" maxlength="2"/> </span></div>';
			}
		$("#postagens").append(html);
        $('#pInicio' + postagem.numero + ', #pFim' + postagem.numero).calendario();
    }
    $("#erroPostZero").slideUp();
};
postagem.removerCampo = function(numero){
   if(postagem.removendoCampo === 0){
        postagem.removendoCampo = 1;
        $("#postagem" + numero).slideUp("slow", function(){
            $(this).remove();
            postagem.numero--;
            for(var i= numero+1; $("#postagem" + i).length > 0; i++){
                var ianterior = i -1;
                $("#postagem" + i + ' input[type="text"]').attr('name', 'Ipostagem' + ianterior).attr('id', 'Ipostagem'+ianterior);
                $("#pInicio" + i).attr('name', 'pInicio' + ianterior).attr('id', 'pInicio'+ianterior);
                $("#pFim" + i).attr('name', 'pFim' + ianterior).attr('id', 'pFim'+ianterior);
                $("#postagem" + i).attr("data-item", ianterior);
                $("#postagem" + i).attr('id', 'postagem'+ianterior);


            }
            if($(".postagem").length === 0){
                $("#erroPostZero").slideDown();
            }
            postagem.removendoCampo = 0;
        });
   }
};

postagem.abrirDatas = function(numero){
    $("#postagem" + numero + " div").slideToggle("slow");
};

postagem.iniciar = function(){
    postagem.numero = $(".postagem").length;
    $("body").delegate(".postagem .icoExcluir", "click", function(){
        postagem.removerCampo(($(this).parent().parent().data("item")));
    });
    $("body").delegate(".postagem .icoConfig", "click", function(){
        postagem.abrirDatas(($(this).parent().parent().data("item")));
    });
    $("#novaPostagem").on("click", function(){
        postagem.criarCampo();
    });
};
var nivelDeEnsino = new Object();
nivelDeEnsino.iniciar = function(){

};
function apenasNumeros(a) {
  if ((a.keyCode < 49 || a.keyCode > 57) && (a.keyCode < 96 || a.keyCode > 105) && (a.keyCode < 37 || a.keyCode > 40) && (a.keyCode != 8 && a.keyCode != 46 && a.keyCode != 110 && a.keyCode != 9 && a.keyCode != 13)) {
    if ($('.apenasNumeros').is(':focus')) {
      return false
    }
  }
}
function iniciaCamposNumericos() {
  if ($('.apenasNumeros').length > 0) {
    $(document).keydown(apenasNumeros);
  }
}

//FUNÇÕES QUE SERÃO CHAMADAS QUANDO A PÁGINA TERMINA DE CARREGAR
$("body").ready(function(){
    formulario.iniciar();
    console.log( $("#banca2").attr("checked") );



});

}( jQuery ));
