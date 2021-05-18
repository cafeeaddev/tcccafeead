<?php

class M_ModeloBaseIterator implements Iterator, Countable {

    private $var = array();
    public $paginador = array();

    public function __construct(array $array) {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function count() {
        return count($this->var);
    }

    public function rewind() {
        reset($this->var);
    }

    public function current() {
        $var = current($this->var);
        return $var;
    }

    public function key() {
        $var = key($this->var);
        return $var;
    }

    public function next() {
        $var = next($this->var);
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }

    public function exibirPaginacao($padrao = 'normal') {
        $totalLinks = 7;
        $linksCadaLado = 3;

        $primeiraPagina = 1;
        $ultimaPagina = $this->paginador['paginaTotais'];
        $paginaAtual = $this->paginador['paginaAtual'];
        $totalRegistros = $this->paginador['totalRegistro'];
        $link = $this->paginador['link'];
        $paginaAnterior = ($paginaAtual == 0) ? 1 : $paginaAtual - 1;
        $proximaPagina = ($paginaAtual == $ultimaPagina) ? $ultimaPagina : $paginaAtual + 1;

        $primeiroLink = ($paginaAtual - $linksCadaLado < $primeiraPagina) ? $primeiraPagina : $paginaAtual - $linksCadaLado;

        $ultimoLink = ($paginaAtual + $linksCadaLado > $ultimaPagina) ? $ultimaPagina : $paginaAtual + $linksCadaLado;

        while ($primeiroLink + $ultimoLink <= $totalLinks) {
            $teste = $ultimoLink + 1;
            if ($teste > $ultimaPagina) {
                break;
            }
            $ultimoLink++;
        }

        while ($ultimoLink - $primeiroLink < $totalLinks - 1) {
            $teste = $primeiroLink - 1;
            if ($teste < $primeiraPagina) {
                break;
            }
            $primeiroLink--;
        }

        $html = '<style>';
        $html .= '.barraPaginarLiSelecionado{';
        $html .= '   background-color:gray;';
        $html .= '}';
        $html .= '</style>';
        $html .= "<div class='barraPaginar' style='width: 100%;text-align:center;padding:0;'>";
        $html .= "<ul class='barraPaginarUl' style='margin:0;padding:0;'>";
        $html .= "<a class='barraPaginarA' data-pagina='$primeiraPagina' href='$link&_pag=$primeiraPagina' title='Primeira Página ($primeiraPagina)'><li class='barraPaginarLi' id='barraPaginarLi_Primeira' style='display: inline-block;border:1px solid gray;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'><<</li></a>";
        $html .= "<a class='barraPaginarA' data-pagina='$paginaAnterior' href='$link&_pag=$paginaAnterior' title='Página Anterior ($paginaAnterior)'><li class='barraPaginarLi' id='barraPaginarLi_Anterior' style='display: inline-block;border:1px solid gray;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'><</li></a>";
        $html .= ($primeiroLink > $primeiraPagina) ? "<li class='barraPaginarLi' id='barraPaginarLi_AnteriorReticencias' style='display: inline-block;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'>...</li>" : "";
        for ($i = $primeiroLink; $i <= $ultimoLink; $i++) {
            $selecionado = ($i == $paginaAtual) ? "barraPaginarLiSelecionado" : '';
            $html .= "<a class='barraPaginarA' data-pagina='$i' href='$link&_pag=$i' title='Página $i'><li class='barraPaginarLi $selecionado' id='barraPaginarLi_$i' style='display: inline-block;border:1px solid gray;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'>";
            $html .= "$i";
            $html .= "</li></a>";
        }
        $html .= ($ultimoLink < $ultimaPagina) ? "<li class='barraPaginarLi' id='barraPaginarLi_ProximaReticencias' style='display: inline-block;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'>...</li>" : "";
        $html .= "<a class='barraPaginarA' data-pagina='$proximaPagina' href='$link&_pag=$proximaPagina' title='Próxima Página ($proximaPagina)'><li class='barraPaginarLi' id='barraPaginarLi_Proxima' style='display: inline-block;border:1px solid gray;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'>></li></a>";
        $html .= "<a class='barraPaginarA' data-pagina='$ultimaPagina' href='$link&_pag=$ultimaPagina' title='Última Página ($ultimaPagina)'><li class='barraPaginarLi' id='barraPaginarLi_Ultima' style='display: inline-block;border:1px solid gray;min-width: 20px;font-size: 12px;text-align: center;margin-left:5px;'>>></li></a>";

        if ($padrao == 'ajax') {
            $html .= "<script>";
            $html .= "$(document).ready(function () {";
            $html .= "$('body').delegate('.barraPaginarA', 'click', function (event) {";
            $html .= "event.preventDefault();";
            $html .= "var link = $(this).attr('href') + ' #conteudoAjax';";
            $html .= "var pagina = $(this).data('pagina');";
            $html .= "carregandoAbre('buscando dados...');";
            $html .= "$('#conteudo').load(link, {}, function () {";
            $html .= "$('.barraPaginarLi').removeClass('barraPaginarLiSelecionado');";
            $html .= "$('#barraPaginarLi_'+pagina).addClass('barraPaginarLiSelecionado');";
            $html .= "carregandoFecha();";
            $html .= "});";
            $html .= "});";
            $html .= "});";
            $html .= "</script>";
        }
        return $html;
    }

}
