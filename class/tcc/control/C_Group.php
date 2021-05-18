<?php

    /*
Criado por Cafeead
*/
    class C_Group
    {


        public static function verificaAtualizaGroupStatus ($tcccafeead, $group, $postagens, $membros = array( ) )
        {
            global $DB;
            global $CFG;
            global $USER;

            //buscando  informações extra do grupo
            $tccgroup = $DB->get_record('tcccafeead_group', array('groupid'=>$group->groupid),'*');


			      $postagensTCC = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tcccafeead->id) );

            if($tccgroup == FALSE){
               $tccgroup = self::criaTccGroup($group);
            }
            $status = 0;
            $formulariosPostados = true;
            $formulariosAprovados = true;
            $isTeacher = false;

            foreach($membros['professors'] as $profEn){
                if( $profEn->userid == $USER->id  ){
                  $isTeacher = true;
                }
            }


            //se o status do grupo for igual a 7 (finalizado) não é necessário averiguar mais nada
            if($tccgroup->status !== '7'){
                //buscando formulários obrigatórios do tcc

                $formulariosObrigatorios = $DB->get_records_list('tcccafeead_form', 'tccid', array($tcccafeead->id), 'id', '*', 0, 999999);
                $where = "tccid = :tccid and groupid = :groupid";
                $groupForms2 = $DB->get_records_select('tcccafeead_group_form', $where, array('tccid'=>$tcccafeead->id, 'groupid'=>$group->groupid), 'formtypeid', '*', 0, 999999);
                $groupForms = array();
                foreach($groupForms2 as $gf){
                    $groupForms[$gf->formtypeid] = $gf;
                }
                
                $nItensPostados = 0;
                foreach($formulariosObrigatorios as $indice=>$formularioObrigatorio){
                    if($indice !== 0){
                        if(isset($groupForms[$formularioObrigatorio->formtypeid])){
                            if($groupForms[$formularioObrigatorio->formtypeid]->status !== '3'){
                                $formulariosAprovados = false;
                            }
                            $nItensPostados++;
                        }else{

                            $formulariosPostados = false;
                        }
                    }
                }


                //se o numero de formulários submetidos pelo grupo é menor do que o exigido, portando o status é PENDENTE
                if( $nItensPostados < count($formulariosObrigatorios) ){
                    $status = 0;
                }
                //se o número de formulários submetidos é igual ao exigido, portanto o status é APROVAÇÃO
                else{
                    $status = 1;
                    //se todos formulário já foram postado e aprovados o status é AGUARDANDO POSTAGEM
                    if($formulariosPostados && $formulariosAprovados ){

						$status = 2;

						if ( count($postagensTCC) == 0 && $tcccafeead->banca == 0 ){
							$status = 4;
						} else if ( count($postagensTCC) > 0 && $tcccafeead->banca == 0 ){
							$status = 2;
						} else if (count($postagensTCC) > 0 && $tcccafeead->banca == 1  ){
							$status = 2;
						}
						else{
							$status = 6;
						}

                        //se já existem postagens e/ou correções verifico as datas de envio para saber se está em "CORREÇÃO" ou "ORIENTADO"
                        $i = 1;
                        $ultimoEnvioDeTrabalho = 0;
                        $ultimoEnvioDeCorrecao = 0;
                        $ultimoArquivo = "";
                        $ultimoCaminho = "";
                        $ultimoI = "";
                        foreach($postagens as $postagem):
                            $caminhoEnvios = $CFG->dataroot .'/tcccafeead/' . $tcccafeead->id . '/' . $group->groupid . '/postagens/' . $i;
                            $caminhoCorrecoes = $CFG->dataroot .'/tcccafeead/' . $tcccafeead->id . '/' . $group->groupid . '/correcoes/' . $i;


                            if(is_dir($caminhoEnvios)){
                                if ($handle = opendir($caminhoEnvios)) {
                                    while (false !== ($entry = readdir($handle))) {
                                        if ($entry != "." && $entry != "..") {
                                            $aTime = filemtime($caminhoEnvios . '/' .$entry);
                                            $ultimoEnvioDeTrabalho = ($aTime > $ultimoEnvioDeTrabalho)? $aTime : $ultimoEnvioDeTrabalho;
                                            $ultimoArquivo = $entry;
                                            $ultimoCaminho = $caminhoEnvios;
                                            $ultimoI = $i;
                                        }
                                    }
                                    closedir($handle);
                                }
                            }

                            if(is_dir($caminhoCorrecoes)){
                                if ($handle = opendir($caminhoCorrecoes)) {
                                    while (false !== ($entry = readdir($handle))) {
                                        if ($entry != "." && $entry != "..") {
                                            $aTime = filemtime($caminhoCorrecoes . '/' .$entry);
                                            $ultimoEnvioDeCorrecao = ($aTime > $ultimoEnvioDeCorrecao)? $aTime : $ultimoEnvioDeCorrecao;
                                        }
                                    }
                                    closedir($handle);
                                }
                            }

                        $i++;
                        endforeach;

                        $varDownload = false;

                        $downSql = "SELECT * FROM {tcccafeead_logs_sistema} WHERE userid=".$USER->id." AND tipo_log = 'DOWNLOAD TCC' AND tccid=".$tcccafeead->id." AND groupid=".$group->groupid." AND nome_arquivo ='".$ultimoArquivo."'";

                        $downloads=$DB->get_records_sql($downSql);

                        if( count($downloads) > 0 && $isTeacher ){
                          $varDownload = true;
                        }



                        //se já houve um envio
                        if($ultimoEnvioDeTrabalho > 0){


                            //se o último envio de trabalho foi posterior a envio de correção
                            if($ultimoEnvioDeTrabalho > $ultimoEnvioDeCorrecao){

                                //CODIGO CORRETO - DESCOMENTAR DEPOIS DE CORRIGIR OS STATUS
                                if($tccgroup->status == 4){

                                  if( $varDownload){
                                    $status = 4;
                                  } else {
                                    $status = 3;
                                  }
                                }else if( $tccgroup->status == 6 ){
                                    $status = 6;
                                }else if( $tccgroup->status == 5 ){
                                    $status = 3;
                                } else {
                                  if( $varDownload ){
                                    $status = 4;
                                  } else {
                                    $status = 3;
                                  }

                                }

                                if( isset( $_GET['corrigir_status'] ) && $_GET['corrigir_status'] == 'true'  ){
                                  if( $tccgroup->status == 4 || $tccgroup->status == 5 ){
                            		$status = 3;
                                  }
                                }
                            }
                            //se o último envio foi de correção, portanto "ORIENTADO"
                            else{
                                //se o status não for "ORIENTADO" portanto "EM CORREÇÃO"
                            	if($tccgroup->status == 3){
                            		$status = 5;
                            	}else if( $tccgroup->status == 6 ){
                            		$status = 6;
                            	}else if( $tccgroup->status == 5 ){
                            		$status = 5;
                            	}else{
                            		$status = 5;
                            	}

                            	if( isset( $_GET['corrigir_status'] ) && $_GET['corrigir_status'] == 'true'  ){
                            		if( $tccgroup->status == 4 || $tccgroup->status == 4 ){
                            			$status = 5;
                            		}
                            	}

                            }



                        }
                    }



                }
            } else{
            	$status = 7;
            }
            $tccgroup->status = $status;
            $DB->update_record_raw('tcccafeead_group', $tccgroup);

            return array('group'=>$group,'tccgroup'=>$tccgroup);
        }

        public static function verificaAtualizaGroupStatusAjax ($tcccafeeadid, $groupid)
        {
            global $DB;
            $tcccafeead = $alunosEn = $DB->get_record('tcccafeead', array('id'=>$tcccafeeadid));
            $group = $alunosEn = $DB->get_record('tcccafeead_group', array('groupid'=>$groupid));
            $postagens = $DB->get_records_list('tcccafeead_stage', 'tccid', array($tcccafeeadid), 'id', '*', 0, 999999);
            $groupAtualizado = self::verificaAtualizaGroupStatus($tcccafeead, $group, $postagens);
            $tccgroup = $groupAtualizado['tccgroup'];
            echo $tccgroup->status;

        }
        public static function emCorrecao($groupid){
            global $DB;
            $group = $DB->get_record('tcccafeead_group', array('groupid'=>$groupid), '*');
             if($group->status === '3' || $group->status === '5'){
                 $group->status = 4;
                 $DB->update_record_raw('tcccafeead_group', $group);
             }
             echo $group->status;
        }
        public static function orientado($groupid){
            global $DB;
            $group = $DB->get_record('tcccafeead_group', array('groupid'=>$groupid), '*');

             if($group->status !== '6' || $group->status !== '7'){
                 $group->status = 5;
                 $DB->update_record_raw('tcccafeead_group', $group);
             }
             echo $group->status;
        }
        public static function enviado($groupid){
            global $DB;
            $group = $DB->get_record('tcccafeead_group', array('groupid'=>$groupid), '*');
             if($group->status != '6' || $group->status != '7'){
                 $group->status = 3;
                 $DB->update_record_raw('tcccafeead_group', $group);
             }
             echo $group->status;
        }
		public static function mudarParaEnviado($groupid){
            global $DB;
            $group = $DB->get_record('tcccafeead_group', array('groupid'=>$groupid), '*');
             if($group->status != '6' || $group->status != '7'){
                 $group->status = 3;
                 $DB->update_record_raw('tcccafeead_group', $group);
             }
        }
        public static function criaTccGroup($group){
            global $DB;
            $tccgroup = new stdClass();
            $tccgroup->groupid = $group->id;
            $tccgroup->active = 1;
            $tccgroup->status = 0;
            $insertTccGroup = $DB->insert_record_raw('tcccafeead_group', $tccgroup);
            if($insertTccGroup !== false){
                $tccgroup->id = $insertTccGroup;
                return $tccgroup;
            }
        }
        public static function getMembers($group, $context){
            global $DB;

            $usersEn = $DB->get_records('groups_members', array('groupid'=>$group->id), 'id', '*', 0, 999999);
            $members = ['professors'=>[],'students'=>[]];
            $students = get_role_users(5, $context);
            $lteacher = get_role_users(4, $context);
            $editingTeacher = get_role_users(3, $context);
            foreach ($usersEn as $user){
            	$student = false;
            	$teacher = false;
            	foreach($students as $st){
		    if($st->id === $user->userid){
		        $student = true;
		    }
		}
		foreach($lteacher as $st){
		    if($st->id === $user->userid){
		        $teacher = true;
		    }
		}
		foreach($editingTeacher as $st){
		    if($st->id === $user->userid){
		        $teacher = true;
		    }
		}
                //$sub = has_capability('mod/assign:submit', $context, $user->userid);
                $grad = has_capability('moodle/grade:edit', $context, $user->userid);
                if($student === true){
                    $members['students'][$user->userid] = $user;
                }
                if($teacher === true){
                    $members['professors'][$user->userid] = $user;
                }
            }
            return $members;
        }
    }
