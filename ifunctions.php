<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

    $in = $DB->get_record('external_functions', array('name'=>'tcccafeead_inserir'));
    $at = $DB->get_record('external_functions', array('name'=>'tcccafeead_atualizar'));
    //$ex = $DB->get_record('external_functions', array('name'=>'tcccafeead_excluir'));
    $columns8 = array("name", "classname", "methodname", "classpath", "component", "capabilities");
    
    $records8 = array();
    $inseridos = 0;
    if($in === false){ 
        $records8[] = array_combine($columns8, array('tcccafeead_inserir','M_TCC_ws','inserir','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
    }
    if($at === false){
        $records8[] = array_combine($columns8, array('tcccafeead_atualizar','M_TCC_ws','atualizar','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
    }
//    if($ex === false){
//        $records8[] = array_combine($columns8, array('tcccafeead_excluir','M_TCC_ws','excluir','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
//    }
    foreach ($records8 as $record) {
        $DB->insert_record('external_functions', $record, false);
        $inseridos++; 
    }

echo"Web services ok<br>";
if($inseridos > 0){
    echo"$inseridos inseridos com sucesso!";
}