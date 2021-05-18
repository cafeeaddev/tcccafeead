<?php

require_once("./class/tcc/logs/L_Logs.php" );
require_once("./class/tcc/notificacao/N_Config.php" );
require_once("./class/tcc/notificacao/N_Curl.php" );
require_once("./class/tcc/notificacao/N_tcc_notification.php" );
    /*
Criado por Cafeead
*/
    class M_Grade
    {

        public static function atualizar($post){
            global $DB, $USER;
             try {
            //verificando se já existe nota
            $atualizarNota = true;

            $log = new Logs($post['tcc'], $USER->id, $post['group'], "ATUALIZAR NOTA" );

            if( $post['type'] == "3" || $post['type'] == 3 ){
            	$atualizarNota = false;

				$tcc = $DB->get_record('tcccafeead', array('id'=>$post['tcc']));



				if( $tcc->banca == 0){
					$type = 1;
				}else{
					$type = 2;
				}

            }else{
            	$type = $post['type'];
            }

            $nota = $DB->get_record('tcccafeead_grade', array('groupid'=>$post['group'], 'tccid'=>$post['tcc'], 'type'=>$type));


            //não existe
            if($nota === FALSE){
                $n = new stdClass();
                $n->tccid = $post['tcc'];
                $n->groupid = $post['group'];
                $n->type = $type;
                if( $atualizarNota  ){
                	$n->value = $post['valor'];
                }
                $n->timecreated = time();
                $n->timemodified = time();
                if( isset($post['tema']  ) ){
                	$n->tema = $post['tema'];
                }
                if( isset($post['data'] ) && $post['data'] != "NaN" && $post['data'] != "" ){
                	
                	$n->data = $post['data'];
                }
                //inserindo no banco de dados
                $pers = $DB->insert_record_raw('tcccafeead_grade', $n);
            }
            //existe
            else{
            	if( $atualizarNota  ){
            		$nota->value = $post['valor'];
            	}
                $nota->timemodified = time();
            	if( isset($post['tema']  ) ){
                	$nota->tema = $post['tema'];
                }
                if( isset($post['data']  ) && $post['data'] != "NaN" && $post['data'] != "" ){
                	$nota->data = $post['data'];
                }
                //atualizando no banco de dados
                $pers = $DB->update_record('tcccafeead_grade', $nota);
            }

            $tccgroup = $DB->get_record('tcccafeead_group', array('groupid'=>$post['group']),'*');

            if( $post['type'] == '1' ){

				if( $post['banca'] == 0 && $post['postagem'] == 0 ){
					$tccgroup->status = 7;
				}else{
					$tccgroup->status = 6;
				}

            	$DB->update_record_raw('tcccafeead_group', $tccgroup);

            }
              $log->log("SUCESSO: NOTA ATUALIZADA", $nome, $dir );
            	echo $tccgroup->status;

             }catch (Exception $ex){
               $log->log("ERRO: AO ATUALIZAR NOTA", $nome, $dir );
             	echo $ex;
             }



        }

        public static function abrir($post){
            global $DB;
            global $USER;
            $dados = array('status'=>'ok');
          //$log = new Logs($post['tcc'], $USER->id, $post['group'], "ABRIR NOTA" );

            //$alunos = $DB->get_records('tcccafeead_group_enrolment', array('id_tcccafeead_group'=>$post['group'], 'active'=>1));
            $group = $DB->get_record('groups', array('id'=>$post['group']));
            $tccgroup = $DB->get_record('tcccafeead_group', array('groupid'=>$post['group']));

               //instancio grupo para mudar status
                $tccgroup->status = 4;
                $pers = $DB->update_record('tcccafeead_group', $tccgroup);

            //$log->log("SUCESSO: NOTA ABERTA", $nome, $dir );
            return $dados;


        }

        public static function fechar($post){
            global $DB;
            global $USER;
            $dados = array('status'=>'ok');
            $log = new Logs($post['tcc'], $USER->id, $post['group'], "FECHAR NOTA" );
            //verificando se existe gradeItem
            $gradeItem = $DB->get_record('grade_items', array('itemtype'=>'mod', 'itemmodule'=>'tcccafeead', 'iteminstance'=>$post['tcc']));
            if($gradeItem === false){
                $tcc = $DB->get_record('tcccafeead', array('id'=>$post['tcc']));
                $idGI = M_TCC::criaGradeItem($tcc->course, $tcc->id, $tcc->name, $DB);
                $gradeItem = $DB->get_record('grade_items', array('id'=>$idGI));

            }
            //verificando se existem alunos

            //$alunos = $DB->get_records('tcccafeead_group_enrolment', array('id_tcccafeead_group'=>$post['group'], 'active'=>1));
            $group = $DB->get_record('groups', array('id'=>$post['group']));
            $tccgroup = $DB->get_record('tcccafeead_group', array('groupid'=>$post['group']));
            $tcc = $DB->get_record('tcccafeead', array('id'=>$post['tcc']));
            $coursecontext = context_course::instance($tcc->course);
            $members = C_Group::getMembers($group, $coursecontext);

            //criando alertas de erro
            if($gradeItem === FALSE){
                $dados['status'] = 'erro';
                $dados['mensagem'] = 'não há "gradeItem" disponível, avise o administrador do sistema.';
            }
            if(count($members['students']) === 0){
                $dados['status'] = 'erro';
                $dados['mensagem'] = 'Não há alunos neste grupo';
            }
            if($post['valor'] === '' || $post['valor'] === '--'){
                $dados['status'] = 'erro';
                $dados['mensagem'] = 'Não há nota disponível!';
            }
            if($dados['status'] === 'ok'){
                foreach($members['students'] as $aluno){
                    //verificando se já existe nota
                    $gradeGrade = $DB->get_record('grade_grades', array('itemid'=>$gradeItem->id, 'userid'=>$aluno->userid));
                    //não existe
                    if($gradeGrade === false){
                        $gg = new stdClass();
                        $gg->itemid = $gradeItem->id;
                        $gg->userid = $aluno->userid;
                        $gg->rawgrademax = '100.00000';
                        $gg->rawgrademin = '0.00000';
                        $gg->finalgrade= $post['valor'];
                        $gg->usermodified = $USER->id;
                        $gg->timecreated = time();

                        //inserindo no banco de dados
                        $pers = $DB->insert_record_raw('grade_grades', $gg);
                    }
                    //existe
                    else{
                        $gradeGrade->finalgrade= $post['valor'];
                        $gradeGrade->timemodified = time();
                        //atualizando no banco de dados
                        $pers = $DB->update_record('grade_grades', $gradeGrade);
                    }

                }
                //instancio grupo para mudar status
                $tccgroup->status = 7;
                $log->log("SUCESSO: FECHAR ABERTA", $nome, $dir );
                $pers = $DB->update_record('tcccafeead_group', $tccgroup);
            }

            return $dados;


        }

    }
