<?php 
    /*
Criado por Cafeead
*/
    class M_Formulario
    {
        public static function gravar($post){
           
            global $DB;
            $where = "tccid = :tccid and groupid = :groupid and formtypeid = :formtypeid";
            $formulariosObrigatorios = $DB->get_records_list('tcccafeead_form', 'tccid', array($post['tccid']), 'id', '*', 0, 999999);
            foreach($formulariosObrigatorios as $formulario){
                $groupForm = $DB->get_record_select('tcccafeead_group_form', $where, array('tccid'=>$post['tccid'], 'groupid'=>$post['groupid'], 'formtypeid'=>$formulario->formtypeid));
                //$formStatus =($groupForm !== FALSE)? $DB->get_record('tcccafeead_group_form_status', array('id'=>$groupForm->status)):0;
                //$tipo = $formulariosObrigatoriosTipos[$formulario->formtypeid];
                $groupFormStatus =($groupForm !== FALSE)? $groupForm->status:0;
                if($groupFormStatus === '3'){
                    
                }else{
                    
                    if($groupForm === FALSE){
                       
                        $novoGroupForm = new stdClass();
                        
                        $novoGroupForm->tccid = $post['tccid'];
                        $novoGroupForm->groupid = $post['groupid'];
                        $novoGroupForm->formtypeid = $formulario->formtypeid;
                        $novoGroupForm->status = 2;
                        $novoGroupForm->content = $post['f_'.$formulario->id];
                        $novoGroupForm->timesubmission = time();
                        $novoGroupForm->timelaststatus = time();
                        $DB->insert_record_raw('tcccafeead_group_form', $novoGroupForm);
                    }else{
                        
                        $groupForm->content = $post['f_'.$formulario->id];
                        $DB->update_record_raw('tcccafeead_group_form', $groupForm);
                    }
                }
                
            }
            echo '1';
        }
        
    }
