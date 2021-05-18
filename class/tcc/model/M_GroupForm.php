<?php 
    /**
    * Classe model
    *
    * @author CafeEAD
    */
    class M_GroupForm
    {

        public static function confirmaItem ($tccId, $groupId, $formTypeId, $comentario)
        {
            global $DB;
            $groupForm = $DB->get_record('tcccafeead_group_form', array('tccid'=>$tccId, 'groupid'=>$groupId, 'formtypeid'=>$formTypeId));
            $groupForm->status = 3;
			$groupForm->comentario = $comentario;
            $groupForm->timelaststatus = time();
            $atu = $DB->update_record_raw('tcccafeead_group_form', $groupForm);
            
            echo $atu;
        }		
		
        public static function cancelaItem ($tccId, $groupId, $formTypeId, $comentario)
        {
            global $DB;
            $groupForm = $DB->get_record('tcccafeead_group_form', array('tccid'=>$tccId, 'groupid'=>$groupId, 'formtypeid'=>$formTypeId));
            $groupForm->status = 2;
            $groupForm->comentario = $comentario;
			$groupForm->timelaststatus = time();
            $atu = $DB->update_record_raw('tcccafeead_group_form', $groupForm);
            
            echo $atu;
        }
		
		public static function negaItem ($tccId, $groupId, $formTypeId, $comentario)
        {
            global $DB;
            $groupForm = $DB->get_record('tcccafeead_group_form', array('tccid'=>$tccId, 'groupid'=>$groupId, 'formtypeid'=>$formTypeId));
            $groupForm->status = 4;
            $groupForm->comentario = $comentario;
			$groupForm->timelaststatus = time();
            $atu = $DB->update_record_raw('tcccafeead_group_form', $groupForm);
            
            echo $atu;
        }
		
		public static function salvaComentario ($tccId, $groupId, $formTypeId, $comentario)
        {
            global $DB;
            $groupForm = $DB->get_record('tcccafeead_group_form', array('tccid'=>$tccId, 'groupid'=>$groupId, 'formtypeid'=>$formTypeId));            
			$groupForm->comentario = $comentario;
            $groupForm->timelaststatus = time();
            $atu = $DB->update_record_raw('tcccafeead_group_form', $groupForm);
            
            echo $atu;
        }
    }
