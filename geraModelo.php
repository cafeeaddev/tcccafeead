<?php
include "../../config.php";

$sql = "SELECT concat(information_schema.COLUMNS.ORDINAL_POSITION, "
        . "information_schema.COLUMNS.TABLE_NAME), "
        . "information_schema.COLUMNS.TABLE_NAME AS 'tabela', "
        . "information_schema.COLUMNS.COLUMN_NAME AS 'coluna', "
        . "information_schema.COLUMNS.DATA_TYPE AS 'type',  "
        . "information_schema.COLUMNS.CHARACTER_MAXIMUM_LENGTH AS 'length',  "
        . "information_schema.COLUMNS.COLUMN_KEY AS 'key',  "
        . "information_schema.COLUMNS.EXTRA AS 'autoincrement',  "
        . "information_schema.COLUMNS.IS_NULLABLE AS 'isnull'  "
        . "FROM information_schema.COLUMNS where TABLE_SCHEMA = 'moodle' and TABLE_NAME LIKE 'mdl_tcccafeead%'";
$colunas= $DB->get_records_sql($sql);
$colunas2 = array_values($colunas);
$espaco = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
$tabela = '';
$tabela2 = '';
$listaTabelas = array();
$primaria = '';
$previousT = '';
$nextT = '';

foreach($colunas2 as $key=>$coluna){
    if($tabela2 !== $coluna->tabela){
        
           $colunaA = explode("mdl_", $coluna->tabela);
           
           $listaTabelas[] = $colunaA[1];
       
        $tabela2 = $coluna->tabela;
    }
}


function encerraTabela($espaco, $primaria){
    
    echo "$espaco &lt;/FIELDS&gt;<br>";
    echo "$espaco &lt;KEYS&gt;<br>";
    echo $espaco . $espaco. '&lt;KEY  NAME="primary" TYPE="primary" FIELDS="' . $primaria->coluna . '" /&gt;<br>';
    echo "$espaco &lt;/KEYS&gt;<br>";
    echo '&lt;/TABLE&gt;<br>';
}

ECHO "&lt;TABLES&gt;<br>";

$nTabs = 0;
foreach($colunas2 as $key=>$coluna){
    if($tabela !== $coluna->tabela){
        if($tabela !== ''){
            
            encerraTabela($espaco, $primaria);
            $primaria = '';
            $nTabs++;
        }
        $tabelaA = explode("mdl_",$coluna->tabela);
        $tabelaN = $tabelaA[1];
       
        $previousT = (isset($listaTabelas[$nTabs - 1]))? 'PREVIOUS="'.$listaTabelas[$nTabs -1] .'"' : '';
        
        $nextT = (isset($listaTabelas[$nTabs + 1]))? 'NEXT="'.$listaTabelas[$nTabs + 1] .'"' : '';
        echo '&lt;TABLE NAME="'. $tabelaN .'" ' . $previousT .' ' . $nextT .' COMMENT="" &gt;<br>';
        echo $espaco . '&lt;FIELDS&gt;<br>';
        
        $tabela = $coluna->tabela;
    }
    $isnull = ($coluna->isnull === 'YES')? 'false': 'true';
    $length = ($coluna->length != 0)? $coluna->length : 10 ;
    if($coluna->key === 'PRI'){
        $primaria = $coluna;
    }
    $sequence = ($coluna->autoincrement === 'auto_increment')? 'SEQUENCE="true"' : 'SEQUENCE="false"';
    if(isset($colunas2[$key - 1])){
        $previousC = $colunas2[$key -1];
        if($tabela === $previousC->tabela){
            $previous = 'PREVIOUS="'. $previousC->coluna .'"';
        }else{
            $previous = '';
        }
    }else{
        $previous = '';
    }
    if(isset($colunas2[$key + 1])){
        $nextC = $colunas2[$key + 1];
        if($tabela === $nextC->tabela){
            $next = 'NEXT="'. $nextC->coluna .'"';
        }
        else{
            $next="";
        }
    }else{
        $next = '';
    }
    echo"$espaco $espaco";
    echo '&lt;FIELD NAME="' . $coluna->coluna . '"  TYPE="'. $coluna->type .'" LENGTH="'. $length .'" NOTNULL="' . $isnull . '" ' . $sequence . ' '. $previous .' '. $next .' COMMENT="" /&gt;<br>';
   
}
encerraTabela($espaco, $primaria);
ECHO "&lt;/TABLES&gt;";
