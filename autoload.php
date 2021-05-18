<?php
//crio a função de autoload
function tcc_autoload($class_name) {
        //verificando se trata-se uma classe model, view ou control
        $name_array = explode("_", $class_name);

        switch ($name_array[0]) {
            case 'M' : $mvc = 'model';              
                break;
            case 'V' : $mvc = 'view';
                break;
            case 'C' : $mvc = 'control';
                break;
            case 'L': $mvc = 'logs';
                break;
            case 'N': $mvc = 'notificacao';
                break;
            default : $mvc = '';
                break;
        }
        //verificando se a classe pertence ao tcc ou mimetiza algum objeto do moodle
        //$pasta = (substr($class_name, 2, 3) == 'MDL')? 'classMoodle' : 'class' ;
        switch(substr($class_name, 2, 3)){
            case 'MDL' : $pasta = 'moodle';     break; //classes relativas as tabelas padrão do moodle
            case 'ILY' : $pasta = 'integracao'; break; //classes relatias as tabelas de integração moodle lyceum
            default    : $pasta = 'tcc';        break; //classes relativas ao sistema de tcc em si
        }
       if($mvc !== ''){
        include "./class/$pasta/". "$mvc/" . $class_name . '.php';
       }

}
//registro a função de autoload
spl_autoload_register('tcc_autoload');
?>
