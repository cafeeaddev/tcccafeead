<?php 
    /*
Criado por Cafeead
*/
    class C_Group_Enrolment
    {

        
        public static function verificaEnrol ($user, $group)
        {
            global $DB;
            $groupmember = $DB->get_record('tcccafeead_group_enrolment', array('groupid'=>$group->id, 'id_user'=>$user->id),'*');
            
            if($groupmember === false){
                $groupmember = new stdClass();
                $groupmember->id_user = $user->id;
                $groupmember->groupid = $group->id;
                $groupmember->active = 1;
                $groupmember->uploader = 1;
                $insert = $DB->insert_record_raw('tcccafeead_group_enrolment', $groupmember);
                if($insert !== false){
                    $groupmember->id = $insert;
                }
            }
            return $groupmember;
        }
    }