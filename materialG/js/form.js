//CRIANDO UMA VARIÁVEL COM A URL A QUAL AS REQUISIÇÕES AJAX DEVEM SER ENVIADAS
var urlTemp = window.location.href.split('/course');
var urlAjax = urlTemp[0] + '/mod/tcccafeead/acoesConfiguracao.php';

// contem as funções que criam o visual do formulário
var formulario = new Object();

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};
$.fn.confirmaObrigatorio = function()
{
    var status = 1;
    $.each(this, function() {
        if($(this).hasClass('obrigatorio')){
            if($(this).val() === ''){
                $(this).addClass('erro');
                status = 0;
            }else{
                $(this).removeClass('erro');
            }
        }
    });
    
    return status;
    
};


formulario.enviar = function(acao){
    
    var form = $('#formTCC input, #formTCC textarea, #formTCC select').serializeObject();
    form.acao = acao;
    
    form.course = $("#courseid").val();
    form.section = $("#sectionid").val();
    form.update = $("#update").val();
    
    console.log(form);
    var urlCourse = urlTemp[0] + '/course/view.php?id=' + $('#courseid').val();
    $.post(urlAjax, form, function(data){ alert(data);
        if(data === '1'){
           window.location.href = urlCourse;
        }
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
    
    $("body").delegate("#fEnviar", "click", function(){formulario.enviar($(this).data('acao'));});
    $("body").delegate("#fConfirmar", "click", function(){
        var confirmacao = $(".obrigatorio").confirmaObrigatorio();
        var acao = $(this).data('acao');
        var acaoTexto = ($(this).data('acao') === 'atualizar')? 'Atualizar esta': 'Inserir uma';
        if(confirmacao === 1){
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
           if($(".postagem").length === 0){
               texto += 'Deve haver ao menos uma postagem.<br>';
           }
           if(confirmacao === 0){
               texto += 'Os campos marcados com "*"<br> não podem ficar sem valor.<br>';
           }
            
            popUp.abre("erro","Atenção!",texto,"ok","sim","alert","m", "vermelho", "bola");
        }
    });
    $("body").delegate(".obrigatorio", "focusout", function(){$(this).confirmaObrigatorio();});
    
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
        if($("#fk_"+idAtual).length === 0){
            var html = "<ul class='fakeSelect' id='fk_" + idAtual + "' data-id='" + $(this).attr('id') + "'>" + options +"</ul>";
            $(this).parent().append(html);
            $('#' + $(this).attr('id') + " option").not('.none').each(function(){
                $("#fk_" + idAtual).append("<li data-value ='" + $(this).val() + "'>" + $(this).html() + "</li>");

            });
        }
        
    });
    $(".form select").on("mousedown", function(e){
        e.preventDefault();
        selects.abrir($(this).attr("id"));
        
    });
    $('.fakeSelect li').click(function(){
        var value = $(this).data("value");
        var id = $(this).parent().data('id');
        $("#" + id).val(value);
        $("#" + id).confirmaObrigatorio();
    });
    
    $('html').click(function() {
        $(".fakeSelect").removeClass("fkSVisivel");
        $(".fakeFocus").removeClass("fakeFocus");
    });    
};



//FUNÇÕES QUE SERÃO CHAMADAS QUANDO A PÁGINA TERMINA DE CARREGAR
$("body").ready(function(){
    formulario.iniciar();
    selects.iniciar();
});