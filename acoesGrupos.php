<?php
    //incluindo autoload para poder acarregar classes automaticamente
    include "./autoload.php";
    require_once('../../config.php');
    //executando ações de acordo com o post recebido
    switch($_POST['acao']/*proteger esta variável assim que possível*/){
        case 'buscaGruposDoTcc':

            $groupsCourse = $DB->get_records('tcccafeead_group_tcc', array('tccid'=>$_POST['tcc']), 'id', '*', 0, 999999);
            $groups = array();
            foreach($groupsCourse as $gc){
                $group = $DB->get_record('tcccafeead_group', array('id'=>$gc->groupid));
                $groups[$group->id] = $group;
            }

            die(json_encode($groups));
            break;
            case 'buscaGrupos':
            $mGroups = $DB->get_records('tcccafeead_group', array(), 'id', '*', 0, 999999);
            $groupsCourse = $DB->get_records('tcccafeead_group_tcc', array('tccid'=>$_POST['tcc']), 'id', '*', 0, 999999);
            $cGroups = array();
            $groups = array();
            foreach($groupsCourse as $gc){
                $group = $DB->get_record('tcccafeead_group', array('id'=>$gc->groupid));
                $cGroups[$group->id] = 1;
            }
            foreach($mGroups as $mg){
                if(!isset($cGroups[$mg->id])){
                    $groups[$mg->id] = $mg;
                }
            }

            die(json_encode($groups));
            break;


        default : echo'Cadê o Wally?';

    }
