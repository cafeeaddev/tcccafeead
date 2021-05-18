<?php

require_once("./class/tcc/logs/L_Logs.php" );
require_once("./class/tcc/notificacao/N_Config.php" );
require_once("./class/tcc/notificacao/N_Curl.php" );
require_once("./class/tcc/notificacao/N_tcc_notification.php" );

    /*
Criado por Cafeead
*/
    class C_Arquivos
    {


        public static function mostrarPasta ($path, $tccid, $groupid, $stage, $tipo = 'e', $idLista = '', $classLista = '', $deleteBot = false, $prof = false)
        {


            $dados = array();
            $dados['html'] = '';
            $i = 0;
            $class = '';
            if(is_dir($path)){
                if ($handle = opendir($path)) {
                    $id = ($idLista !== '')? "id = '$idLista'": '' ;
					if( $stage != null && $stage != 0 && $stage != "" ){

						$class = ($class !== '')? "class = 'arquivos $classLista stage_".$tipo."_$stage'": "class='arquivos stage_".$tipo."_$stage'" ;
					} else {
						$class = ($class !== '')? "class = 'arquivos $classLista'": "class='arquivos '" ;
                    }
					$cabecalho = "<ul $id $class>";

                    while (false !== ($entry = readdir($handle))) {

                        if ($entry != "." && $entry != "..") {
                            $dados['html'] .=$cabecalho;
                            $cabecalho = '';
                            $aData = date("d/m/Y h:i", filemtime($path . '/' .$entry));
                            $classe="";

                            if($tipo == 'e' && $prof == true ){
                            	$urlDowload = "./download.php?acao=correcao&tccid=$tccid&group=$groupid&postagem=$stage&tipo=$tipo&arq=$entry&prof=professor";
                            	$class=" class='downloadProf' ";
                            }else{
                            	$urlDowload = "./download.php?tccid=$tccid&group=$groupid&postagem=$stage&tipo=$tipo&arq=$entry&prof=aluno";
                            }
                            $excBot = ($deleteBot) ? "<span class='ico16 excluir' data-nome='$entry' data-stage='$stage' data-groupid='$groupid' data-tipo='$tipo' style='float:right;'></span>" : '';

                            $dados['html'] .= "<li><table style='width: 100%; margin: 0;padding: 0;'><tr><td><a href='$urlDowload' data-groupid='$groupid' $class >$entry ($aData)</a></td><td style='width: 20px;'>$excBot</td></tr></table></li>";

                            $i++;
                        }
                    }
                    closedir($handle);
                    $dados['html'] .="</ul>";
                }
            }
            $dados['nArquivos'] = $i;
            return $dados;
        }

        public static function apagarArquivo($tccid, $groupid, $stage, $tipo, $nome){
            global $CFG, $DB, $USER;
            $dados = array();
            $log = new Logs($tccid, $USER->id, $groupid, "EXCLUIR" );

            $tipoCompleto = ($tipo === 'c')? 'correcoes' : 'postagens';
            $arquivo = $CFG->dataroot . '/tcccafeead/' . $tccid . '/' . $groupid . '/'. $tipoCompleto . '/' . $stage . '/' . $nome;

            $dados['status'] = unlink($arquivo);
            if($dados['status'] === true){
                $dir = $CFG->dataroot . '/tcccafeead/' . $tccid. '/' . $groupid . '/'. $tipoCompleto . '/' . $stage . '/';
                $dados['html'] = C_Arquivos::mostrarPasta($dir,$tccid, $groupid, $stage, $tipo, '', $tipoCompleto, true);
                $log->log("SUCESSO: EXCLUIR ARQUIVO", $nome, $dir );
            }else{
              $log->log("ERRO: EXCLUIR ARQUIVO", $nome, $dir );
            }

            return json_encode($dados);
        }

        public static function listarArquivosTarefa($tcccafeead, $group){
            global $DB;
            //$assign = $DB->get_record('assign', array('id'=>$tcccafeead->importfromassign));

            $assignCourseModule = $DB->get_record('course_modules', array('instance'=>$tcccafeead->importfromassign,'module'=>1));
            $groupsImport =(is_object($group) && is_object($assignCourseModule) && $group->idnumber != '')?$DB->get_records('groups', array('courseid'=>$assignCourseModule->course,'idnumber'=>$group->idnumber), 'id', '*', 0, 999999): FALSE;
            $lUsers = array();
            if($groupsImport !== false){
                foreach ($groupsImport as $groupImport){
                    $members = $DB->get_records('groups_members', array('groupid'=>$groupImport->id), 'id', '*', 0, 999999);
                    foreach ($members as $member){
                        $lUsers[$member->userid] = '';
                    }
                }
            }
            if($assignCourseModule !== FALSE){
                $assignContext = $DB->get_record('context', array('instanceid'=>$assignCourseModule->id, 'contextlevel'=>'70'));

                $files = $DB->get_records('files', array('contextid'=>$assignContext->id,'component'=>'assignsubmission_file', 'filearea'=>'submission_files'), 'id', '*', 0, 999999);
                $files2 = array();
                foreach ($files as $file){
                    if(isset($lUsers[$file->userid])){
                       $files2[]=$file;
                    }
                }
                return $files2;
            }else{
                return array();
            }
        }
    }
