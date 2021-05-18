(function ( $ ) {
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
$.fn.confirmaobrigatorio = function()
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

$.fn.calendario = function() { 
    var mesExtenso = ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];
    var diasDaSemanaAbreviaturas = ['dom','seg', 'ter', 'qua', 'qui', 'sex','sab'];
    var lista = this;
    var nInstancias = 0;
    var montaCabecalho = function(id, ano, mes){
        var html = '<tbody id="mgCalendarioCabecalho_' + id + '">';
        html += '<tr><td>&#9668;</td><td colspan="5" id="mgCalendarioAno_' + id + '" >' + ano + '</td><td>&#9658;</td></tr>';
        html += '<tr><td>&#9668;</td><td colspan="5" id="mgCalendarioMes_' + id + '" >' + mesExtenso[mes] + '</td><td>&#9658;</td></tr>';
        html += '</tbody>';
        html += '<tbody><tr>';
        $.each(diasDaSemanaAbreviaturas, function(){
            html += '<td>' + this + '</td>';
        });
        html += '</tr></tbody>';
        
        return html;
    };
    var montaMes = function(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado){
        var dp = new Date(anoExibir, mesExibir-1, 1);
        var du = new Date(anoExibir, mesExibir, 0);
        var da = new Date(anoExibir, mesExibir-1, 0);
        var mesSel = (anoExibir === anoSelecionado && mesExibir === mesSelecionado)? true : false;
        var pMes = (mesExibir < 10)? '0' + mesExibir : mesExibir;
        var pDate = anoExibir + '-' + pMes;
        
        var ultimoDiaMesAnterior = da.getDate();
        var primeiroDiaDeSemana = dp.getDay();
        var ultimoDia = du.getDate();
        var html = '';
        html += '<tr>';
        var i = 0;
        var col = 0;
        var primeiroMostrado = ultimoDiaMesAnterior - primeiroDiaDeSemana + 1;
        for(i = 0; i < primeiroDiaDeSemana; i++){
            html += '<td class="mgVazio">'+primeiroMostrado+'</td>';
            primeiroMostrado++;
            if(i === 6){
                html += '</tr>';
                i = 0;
            }
        }
        var diaMes = 1;
        while(diaMes <= ultimoDia){
            if(i === 0){
                html += '<tr>';
               
            }
            var extraClass = (mesSel && diaMes === diaSelecionado) ? 'mgDiaSelecionado' : '' ;
           
            var mDia = (diaMes < 10)? '0'+diaMes : diaMes;
            var mDate = pDate + '-' + mDia;
            html += '<td class="mgDia ' + extraClass + '" data-date="' + mDate + '">' + mDia + '</td>';
            if(i === 6){
                html += '</tr>';
                i = 0;
                col++;
            }else{
                i++;
            }
            diaMes++;
        }
        var iComp = 1;
        while(col <= 5){
            if(i === 0){
                html += '<tr>';
                
            }
            var mDia = (iComp < 10)? '0'+iComp : iComp;
            
            html += '<td class="mgVazio">' + mDia + '</td>';
            if(i === 6){
                html += '</tr>';
                i = 0;
                col++;
            }else{
                i++;
            }
            iComp++;
        }
        html += '';
        
        return html;
    };
    var abre = function(el){
	el2 = el;
        nInstancias++;
        var id = (el.id === undefined || el.id === '')? nInstancias : el.id;
        var d = new Date(),diaAtual = d.getDate(), mesAtual = d.getMonth() + 1, anoAtual = d.getFullYear();
        var diaSelecionado = '', mesSelecionado = '', anoSelecionado = '';
        
        if(el.value !== ''){
            var daa = el.value.split('-');
            var diaSelecionado = Number(daa[2]), mesSelecionado = Number(daa[1]), anoSelecionado = Number(daa[0]);
            var mesExibir = mesSelecionado, anoExibir = anoSelecionado;
        }else{
            var mesExibir = mesAtual, anoExibir = anoAtual;
        }
        var html = '';
        html += '<div id = "mgCalendarioBack_' + id + '" class="mgCalendarioBack"></div>';
        html += '<div id = "mgCalendario_' + id + '" class="mgCalendario"><table>';
        html += montaCabecalho(id, anoExibir, mesExibir);
        html += '<tbody id="mgCalendarioMesEx_' + id + '">';
        html += montaMes(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado);
        html += '</tbody>';
        html += '</table>';
        html += '<div id="mgCalendarioBots_' + id + '">';
        html += '<input type="button" value="ANULAR"/><input type="button" value="FECHAR"/>';
        html += '</div>';
        html += '</div>';
        $('body').append( html );
        
        $("#mgCalendarioBack_" + id).on("click", function(){
            fecha(id);
        });
        
        $("#mgCalendarioCabecalho_" + id + ' tr:nth-child(1) td:nth-child(1)').on('click', function(){
            anoExibir--;
            mudaAno(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado, 'menor');
        });
        $("#mgCalendarioCabecalho_" + id + ' tr:nth-child(1) td:nth-child(3)').on('click', function(){
            anoExibir++;
            mudaAno(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado, 'maior');
        });
        $("#mgCalendarioCabecalho_" + id + ' tr:nth-child(2) td:nth-child(1)').on('click', function(){
            if(mesExibir === 1){
                mesExibir = 12;
                anoExibir--;
            }else{
                mesExibir--;
            }
            mudaAno(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado, 'menor');
        });
        $("#mgCalendarioCabecalho_" + id + ' tr:nth-child(2) td:nth-child(3)').on('click', function(){
            if(mesExibir === 12){
                mesExibir = 1;
                anoExibir++;
            }else{
                mesExibir++;
            }
            mudaAno(id, anoExibir, mesExibir, anoSelecionado, mesSelecionado, diaSelecionado, 'maior');
        });
        $('body').delegate(".mgDia", "click", function(){
            el2.value =  $(this).data('date');
            fecha(id);
        });
        $('body').delegate("#mgCalendarioBots_" + id + " input:nth-child(1)", "click", function(){
            el.value =  '';
            fecha(id);
        });
        $('body').delegate("#mgCalendarioBots_" + id + " input:nth-child(2)", "click", function(){
            fecha(id);
        });
        
    };
    var fecha = function(id){
        $("#mgCalendarioBack_" + id + ', '+ "#mgCalendario_" + id).fadeOut("fast", function(){
            $("#mgCalendarioBack_" + id + ', '+ "#mgCalendario_" + id).remove();
        });    
    };
    var mudaAno = function(id, ano, mes, anoSelecionado, mesSelecionado, diaSelecionado, sentido){
        var sentido1 = (sentido === 'maior')? '-=50' : '+=50';
        var topInter = (sentido === 'maior')? '50px' : '-50px';
        var elemento = $( "#mgCalendario_" + id + ' tbody:nth-child(1) tr:nth-child(1) td:nth-child(2)' );
        elemento.animate({opacity: 0,left: sentido1}, 100, function() {
                elemento.html(ano);
                elemento.css('left', topInter);
                elemento.animate({opacity: 1,left: 0}, 100);
          });
        
        mudaMes(id, ano, mes, anoSelecionado, mesSelecionado, diaSelecionado);
    };
    var mudaMes = function(id, ano, mes, anoSelecionado, mesSelecionado, diaSelecionado){
        $("#mgCalendario_" + id + ' tbody:nth-child(1) tr:nth-child(2) td:nth-child(2)').html(mesExtenso[mes]);
        var html = montaMes(id, ano, mes, anoSelecionado, mesSelecionado, diaSelecionado);
        $("#mgCalendarioMesEx_" + id).html(html);
    };
    $('body').ready(function(){
        $.each(lista, function(){
            var el = this;
            $(this).on("mouseup", function(){abre(el);});
            if($(this).attr('name') !== undefined){
                var label = $('label[for='+ $(this).attr('name') +']');
                label.on("mouseup", function(){abre(el);});
            }
        });

    });
};

}( jQuery ));
