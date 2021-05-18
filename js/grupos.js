var urlTemp = window.location.href.split('/view.php');
var urlAjax = urlTemp[0] + '/acoesGrupos.php';

var grupos = new Object();

grupos.iniciar = function(){ 
    grupos.buscaGruposDoTcc();
    grupos.buscaGrupos();
    $("body").delegate("#tccGroups div, #groups div","click", function(){
        $(this).toggleClass("selecionado");
    });
};
grupos.buscaGruposDoTcc = function(){
    var form = {acao: 'buscaGruposDoTcc', tcc: $("#idTcc").val()};
    $.post(urlAjax, form, function(data){
        $("#tccGroups").html('');
        $.each(data, function(){
            var html = '<div>' + this.name + '</div>';
            $("#tccGroups").append(html);
        });
    },'json');
    
};
grupos.buscaGrupos = function(){
    var form = {acao: 'buscaGrupos', tcc: $("#idTcc").val()};
    $.post(urlAjax, form, function(data){
        $("#groups").html('');
        $.each(data, function(){
            var html = '<div data-id="'+ this.id +'">' + this.name + '</div>';
            $("#groups").append(html);
        });
    },'json');
    
};

$("body").ready(function(){
    grupos.iniciar();
});