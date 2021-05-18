<?php 
    /**
    * Classe model
    *
    * @author CafeEAD
    */
    class M_Stagegrade
    {
        
        public static function atualizar($post){
            global $DB;                    
            //verificando se já existe nota
            $notaStage = $DB->get_record('tcccafeead_stage_grade', array('groupid'=>$post['group'], 'tccid'=>$post['tcc'], 'stageid'=>$post['stage']));
            //não existe
            if($notaStage === FALSE){
                $ns = new stdClass();
                $ns->tccid = $post['tcc'];
                $ns->stageid = $post['stage'];
                $ns->groupid = $post['group'];
                $ns->value = $post['valor'];
                $ns->timecreated = time();
                $ns->timemodified = time();
                //inserindo no banco de dados
                $pers = $DB->insert_record_raw('tcccafeead_stage_grade', $ns);
            }
            //existe
            else{
                $notaStage->value = $post['valor'];
                $notaStage->timemodified = time();
                //atualizando no banco de dados
                $pers = $DB->update_record('tcccafeead_stage_grade', $notaStage);
            }
            return '1';

        }

    }
