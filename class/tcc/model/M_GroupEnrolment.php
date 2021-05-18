<?php 
    /*
Criado por Cafeead
*/
    class M_GroupEnrolment
    {

        
        public static function confereUpload ($idEnrol)
        {
            global $DB;
            $enrol = $DB->get_record('tcccafeead_group_enrolment', array('id'=>$idEnrol));
            $enrol->uploader = 1;
            
            $atu = $DB->update_record_raw('tcccafeead_group_enrolment', $enrol);
            
            echo $atu;
            
        }
        public static function removeUpload ($idEnrol)
        {
            global $DB;
            $enrol = $DB->get_record('tcccafeead_group_enrolment', array('id'=>$idEnrol));
            $enrol->uploader = 0;
            
            $atu = $DB->update_record_raw('tcccafeead_group_enrolment', $enrol);
            
            echo $atu;
            
        }
        
       
    }