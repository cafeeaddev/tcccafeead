<?php
require_once(dirname(__FILE__)."/autoload.php");
define( "TCCCAFEEAD_DIR", dirname(__FILE__)."/" );
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(TCCCAFEEAD_DIR."class/tcc/logs/L_Logs.php" );
require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_Config.php" );
require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_Curl.php" );
require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_tcc_notification.php" );


$protocolo = array();
$protocolo['userid'] = $USER->id;

$tipo = ($_GET['tipo'] === 'e')? 'postagens' : 'correcoes';
$arquivo = $CFG->dataroot .'/tcccafeead/' . $_GET['tccid'] . '/' . $_GET['group'] . '/' . $tipo . '/' . $_GET['postagem'] . '/' . $_GET['arq'];

$protocolo['tccid'] = $_GET['tccid'];
$protocolo['groupid'] = $_GET['group'];
switch ($_GET['tipo']) {
  case 'postagens':
  case 'e':
  case 'p':
    $protocolo['tipo_protocolo'] = "POSTAGEM";
    break;
  case 'correcoes':
  case 'c':
    $protocolo['tipo_protocolo'] = "CORRECAO";
    break;
  default:
    $protocolo['tipo_protocolo'] = 'PROTOCOLO';
    break;
}

$protocolo['nome_arquivo']    = $_GET['arq'];
$protocolo['caminho_arquivo'] = $CFG->dataroot .'/tcccafeead/' . $_GET['tccid'] . '/' . $_GET['group'] . '/' . $tipo . '/' . $_GET['postagem'] . '/';

$group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['group']), '*');
$membros = $DB->get_records('groups_members', array('groupid'=> $_GET['group']));
$participantes = '';

$isAluno = false;
$isTeacher = false;

if ( isset($_GET['prof']) && $_GET['prof']=='aluno'){
  $isAluno = true;
  $isTeacher = false;
}
if( isset($_GET['prof']) && $_GET['prof']=='professor'){
  $isAluno = false;
  $isTeacher = true;
}

foreach ($membros as $user) {
  $usuario = $DB->get_record('user', array('id' => $user->userid ));
  $idusuario = $usuario->id;
  $username = $usuario->username;
  $participantes .= "{id:$idusuario, username:$username },";
}
$protocolo['participantes'] = $participantes;

//se é um dowload de envio feito por um professor, atualizo o grupo para "em correção"

if($tipo === 'postagens' && $isTeacher ){
    $group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['group']), '*');
    if($group->status === '3' || $group->status === '5' || $group->status === '4'){
        $group->status = 4;
        $DB->update_record_raw('tcccafeead_group', $group);
    }
}

if($tipo === 'correcoes'){
	$group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['group']), '*');
	if($group->status === '4' || $group->status === '5'){
		$group->status = 5;
		$DB->update_record_raw('tcccafeead_group', $group);
	}
}
$protocolo['timecreated'] = time();
$protocolo['acao'] = "DOWNLOAD";
$log = new Logs($_GET['tccid'], $USER->id, $protocolo['groupid'], "DOWNLOAD TCC" );

if(!$arquivo){ // arquivo não existe
    $protocolo['status_acao'] = 'ARQUIVO NÃO EXISTE';
    $DB->insert_record('tcccafeead_protocolo', $protocolo, true);
    $log->log("ERRO[DOWNLOAD]: ARQUIVO NÃO EXISTE", $protocolo['nome_arquivo'], $protocolo['caminho_arquivo'] );
    die('arquivo não existe');
} else {
   $protocolo['status_acao'] = 'ARQUIVO BAIXADO';
   $log->log("SUCESSO[DOWNLOAD]: ARQUIVO BAIXADO", $protocolo['nome_arquivo'], $protocolo['caminho_arquivo'] );
   $DB->insert_record('tcccafeead_protocolo', $protocolo, true);

    $finfo = new finfo(FILEINFO_MIME_TYPE);


    ob_end_clean();
    header("Cache-Control: public");
    header("Content-Description: File Transfer");
    header("Content-Disposition: attachment; filename=" . $_GET['arq']);
    header("Content-Type: ".$finfo->file($arquivo)."");
    header("Content-Transfer-Encoding: binary");

    // read the file from disk
    readfile($arquivo);
    exit();
}
