<?php 
    /*
Criado por Cafeead
*/
    class M_Chat
    {
        public static function buscar($tccid, $groupid){
            global $DB;
            global $USER;
            $member = $DB->get_record('groups_members', array('groupid'=>$groupid, 'userid'=>$USER->id));
            //$member = $DB->get_record('tcccafeead_group_enrolment', array('groupid'=>$groupid, 'id_user'=>$USER->id));
            if($member === false){
                $extramember = $DB->get_record('tcccafeead_chat_extra_member', array('groupid'=>$groupid, 'iduser'=>$USER->id));
                if($extramember === false){
                    $em = new stdClass();
                    $em->tccid = $tccid;
                    $em->groupid = $groupid;
                    $em->iduser = $USER->id;
                    
                    $insertExtraUser = $DB->insert_record_raw('tcccafeead_chat_extra_member', $em);
                    $firstMember = $DB->get_records('groups_members', array('groupid'=>$groupid), 'id', '*', 0, 1);
                    
                    if($firstMember !== FALSE){
                        foreach ($firstMember as $fm){
                            $oldChats = $DB->get_records('tcccafeead_chat', array('groupid'=>$groupid, 'receiverid'=>$fm->userid), 'id', '*', 0, 9999999);
                            foreach ($oldChats as $oldChat){
                                $oldChat->id = '';
                                $oldChat->receiverid = $USER->id;
                                $insertChat = $DB->insert_record_raw('tcccafeead_chat', $oldChat);
                            }
                        }
                    }
                }
            }
            $chat = $DB->get_records_select('tcccafeead_chat','tccid = :tccid AND groupid = :groupid AND receiverid = :userid', array('tccid'=>$tccid,'groupid'=>$groupid, 'userid'=>$USER->id), 'id DESC', '*', 0, 100);
            $chat2 = array('nResultados'=>count($chat), 'resultados'=>array());
            foreach($chat as $ch){
                $sender = $DB->get_record('user', array('id'=>$ch->senderid));
                
                if($ch->timeseen === '0'){
                    $ch->timeseen = time();
                    $atualizaChat = $DB->update_record_raw('tcccafeead_chat', $ch);
                }
               
                $ch->datesend = date("d/m/y - h:i:s", $ch->timesend);
                $ch->sendername = $sender->firstname . ' ' . $sender->lastname;
                $ch->minhaMensagem = ($sender->id == $USER->id)? true : false;
                $chat2['resultados'][]=$ch;
            }
            return json_encode($chat2);
        }
        
        public static function buscaNovasMensagens($tccid, $groupid){
            global $DB;
            global $USER;
            
            $chat = $DB->get_records_select('tcccafeead_chat','tccid = :tccid AND groupid = :groupid AND receiverid = :userid AND timeseen = 0', array('tccid'=>$tccid,'groupid'=>$groupid, 'userid'=>$USER->id));
            $chat2 = array();
            foreach($chat as $ch){
                $sender = $DB->get_record('user', array('id'=>$ch->senderid));
                $ch->timeseen = time();
                $atualizaChat = $DB->update_record_raw('tcccafeead_chat', $ch);
                $ch->datesend = date("d/m/y - h:i:s", $ch->timesend);
                $ch->sendername = $sender->firstname . ' ' . $sender->lastname;
                $ch->minhaMensagem = ($sender->id == $USER->id)? true : false;
                $chat2[]=$ch;
            }
            return json_encode($chat2);
        }
        public static function contaNovasMensagens($tccid){
            global $DB;
            global $USER;
            
            $chatsNaoLidos = $DB->get_records('tcccafeead_chat', array('tccid'=>$tccid, 'receiverid'=>$USER->id, 'timeseen'=>0), 'id', '*', 0, 999999);
            $nl = array();
            foreach($chatsNaoLidos as $n){
                if(!isset($nl[$n->groupid])){
                    $nl[$n->groupid] = 1;
                }else{
                    $nl[$n->groupid]++;
                }
            }
            return json_encode($nl);
        }
        
        public static function inserirMensagem($tccid, $groupid, $mensagem, $institution = 0){
            if($mensagem !== ''){
                
                global $DB;
                global $USER;
                //$groupEnrolments = $DB->get_records('tcccafeead_group_enrolment', array('id_tcccafeead_group'=>$groupid), 'id', '*', 0, 999999);
                $alunosEn = $DB->get_records('groups_members', array('groupid'=>$groupid), 'id', '*', 0, 999999);
                $usersExtra = $DB->get_records('tcccafeead_chat_extra_member', array('groupid'=>$groupid, 'tccid'=>$tccid), 'id', '*', 0, 999999);
                $foiParaOSender = false;
                $i = 0;
                foreach($alunosEn as $ge){
                    $chat = new stdClass();
                    $chat->tccid = $tccid;
                    $chat->groupid = $groupid;
                    $chat->receiverid = $ge->userid;
                    $chat->senderid = $USER->id;
                    $chat->timesend = time();
                    $chat->timeseen = 0;
                    $chat->message = $mensagem;
                    $chat->institution = (int)$institution;
                    $insertChat = $DB->insert_record_raw('tcccafeead_chat', $chat);
                    if($USER->id == $ge->userid){
                        $foiParaOSender = true;
                    }
                    $i++;
                }
                foreach($usersExtra as $ue){
                    $chat = new stdClass();
                    $chat->tccid = $tccid;
                    $chat->groupid = $groupid;
                    $chat->receiverid = $ue->iduser;
                    $chat->senderid = $USER->id;
                    $chat->timesend = time();
                    $chat->timeseen = 0;
                    $chat->message = $mensagem;
                    $chat->institution = (int)$institution;
                    $insertChat = $DB->insert_record_raw('tcccafeead_chat', $chat);
                    if($USER->id == $ue->iduser){
                        $foiParaOSender = true;
                    }
                    $i++;
                }
                if($foiParaOSender === false){
                    $chat = new stdClass();
                    $chat->tccid = $tccid;
                    $chat->groupid = $groupid;
                    $chat->receiverid = $USER->id;
                    $chat->senderid = $USER->id;
                    $chat->timesend = time();
                    $chat->timeseen = 0;
                    $chat->message = $mensagem;
                    $chat->institution = (int)$institution;
                    $insertChat = $DB->insert_record_raw('tcccafeead_chat', $chat);
                    
                    $i++;
                }
                if($institution !== 0){
                    $group = $DB->get_record('tcccafeead_group', array('groupid' =>$groupid), '*');
                    if($group->status === '4'){
                        $group->status = 5;
                        $DB->update_record_raw('tcccafeead_group', $group);
                    }
                }
                
                return $i;
            }else{
                return 0;
            }
        }
        
        public static function buscarMensagensPreDefinidas(){
            global $DB;
            $predef = $DB->get_records('tcccafeead_chat_text', array());
            
            return json_encode($predef);
        }
    }
