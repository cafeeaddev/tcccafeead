<?php
    require_once(dirname(__FILE__)."/autoload.php");
    define( "TCCCAFEEAD_DIR", dirname(__FILE__)."/" );
    require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
    require_once(TCCCAFEEAD_DIR."class/tcc/logs/L_Logs.php" );
    require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_Config.php" );
    require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_Curl.php" );
    require_once(TCCCAFEEAD_DIR."class/tcc/notificacao/N_tcc_notification.php" );

    $dados = array();
    $dados['status'] = 0;
    $dados['mensagem_erro'] = "";

  	$finfo = new finfo(FILEINFO_MIME_TYPE);

    $protocolo = array();

    $protocolo['userid'] = $USER->id;

    $protocolo['nome_arquivo']    = "ARQUIVO NÃO ENVIADO";
    $protocolo['caminho_arquivo'] = "ARQUIVO NÃO ENVIADO";

    $novoNome = "ARQUIVO NÃO ENVIADO";


    if($_GET['tccid'] != '' && $_GET['groupid'] != '' && $_GET['tipo'] != '' && $_GET['stage'] != ''){

      $log = new Logs($_GET['tccid'], $USER->id, $_GET['groupid'], "UPLOAD TCC" );

    	$tipo = ($_GET['tipo'] === 'e')? 'postagens' : 'correcoes';
      $protocolo['tccid'] = $_GET['tccid'];
      $protocolo['groupid'] = $_GET['groupid'];
      switch ($_GET['tipo']) {
        case 'postagens':
        case 'e':
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

    	$uploaddir = $CFG->dataroot . '/tcccafeead/' . $_GET['tccid']. '/' . $_GET['groupid']. '/'. $_GET['tipo'] . '/' . $_GET['stage'] . '/';

        if(is_dir($uploaddir) === false){
            mkdir($uploaddir, 0777, true);
        }

        foreach($_FILES as $arquivo){

			if ( false === $ext = array_search( $finfo->file($arquivo['tmp_name']), array(
        'jpg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'pdf' => 'application/pdf',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'doc'  => 'application/msword',
        'xls'   => 'application/excel',
        'pdf'   => 'application/pdf'

			), 	true)

				) {
				$log->log("ERRO[ENVIO]: FORMATO INCORRETO ", $arquivo['tmp_name'] , $uploaddir );
        $dados['status'] = 2;
				continue;
			}

        	$arquivo['name'] = trim(str_replace(' ', '_',$arquivo['name']));
        	/*$arquivo['name'] = trim(ereg_replace("[^a-zA-Z0-9_]", "", strtr($arquivo['name'], "�����0�0�0�9���������0�0�0�1�����0�4�0�9�0�8�0�1�0�0�0�7�0�8�0�1�0�7�0�8�0�9�0�3�0�5�0�5 ", "aaaaeeiooouucAAAAEEIOOOUUC_")));*/
            $repeticao = file_exists($uploaddir . '/' . basename($arquivo['name']));

            if($repeticao === false){
                $novoNome = basename($arquivo['name']);
            }else{
                for($iNome = 1; $repeticao === true; $iNome++){
                    $novoNome =  basename($arquivo['name']) . " (cópia $iNome)";
                    $repeticao = file_exists($uploaddir . '/' . $novoNome);
                }
            }

            // replace non letter or digits by -
            $novoNome = preg_replace('#[^\\pL\d.]+#u', '-', $novoNome);

            // trim
            $novoNome = trim($novoNome, '-');

            // transliterate
            if (function_exists('iconv'))
            {
              $novoNome = iconv('utf-8', 'us-ascii//TRANSLIT', $novoNome);
            }

            // lowercase
            //$novoNome = strtolower($novoNome);

            // remove unwanted characters
            $novoNome = preg_replace('#[^-\w.]+#', '', $novoNome);

            $novoNome = $_GET['stage']."_".$novoNome;

            $protocolo['nome_arquivo']    = $novoNome;
            $protocolo['caminho_arquivo'] = $uploaddir;

            if (move_uploaded_file($arquivo['tmp_name'], $uploaddir . $novoNome)) {
                $dados['status'] = 1;
                $log->log("SUCESSO[ENVIO]: UPLOAD FÍSICO", $novoNome, $uploaddir );
            } else {
                $dados['status'] = 0;
                $log->log("ERRO[ENVIO]: UPLOAD FÍSICO - NÃO FOI POSSÍVEL MOVER O ARQUIVO.", $novoNome, $uploaddir );
            }
        }
        if($dados['status'] === 1){
            $dirPasta = C_Arquivos::mostrarPasta($uploaddir,$_GET['tccid'],$_GET['groupid'], $_GET['stage'], $_GET['tipo'][0], '', $_GET['tipo'], true);
            $dados['html'] = $dirPasta['html'];
            $dados['nArquivos'] = $dirPasta['nArquivos'];
        }
        $group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['groupid']), '*');
        $membros = $DB->get_records('groups_members', array('groupid'=> $_GET['groupid']));
        $participantes = '';
        $matriculasProtocolo = "";
        foreach ($membros as $user) {
          $usuario = $DB->get_record('user', array('id' => $user->userid ));
          $idusuario = $usuario->id;
          $username = $usuario->username;
          $participantes .= "{id:$idusuario, username:$username },";
          $matriculasProtocolo .= $username. ",";
        }

        $matriculasProtocolo = rtrim($matriculasProtocolo, ",");

        $protocolo['participantes'] = $participantes;
        if($tipo === 'postagens'){
        	$group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['groupid']), '*');
        	if($group->status === '3' || $group->status === '5' || $group->status === '4'){
        		$group->status = 4;
        		$DB->update_record_raw('tcccafeead_group', $group);
        	}
        }

        if($tipo === 'correcoes'){
        	$group = $DB->get_record('tcccafeead_group', array('groupid' => $_GET['groupid']), '*');
        	if($group->status === '4' || $group->status === '5'){
        		$group->status = 5;
        		$DB->update_record_raw('tcccafeead_group', $group);
        	}
        }
        $protocolo['timecreated'] = time();
        $protocolo['acao'] = "UPLOAD";
        $protocolo['status_acao'] = $dados['status'];

        $id = $DB->insert_record('tcccafeead_protocolo', $protocolo, true);
        if( $id != null &&  $id > 0 ){
            $log->log("SUCESSO[REGISTRO]: REGISTRO DO PROTOCOLO EFETUADO", $novoNome, $uploaddir );
        } else {
           $log->log("ERRO[REGISTRO]: REGISTRO DO PROTOCOLO NÃO FOI EFETUADO.", $novoNome, $uploaddir );
        }


        $numeroPro = sprintf("%08d", $id);
        $protocolo['id'] = $id;
        $arquivoPro = $protocolo['nome_arquivo'];
        $caminhoPro = $protocolo['caminho_arquivo'];
        $statusPro = $protocolo['status_acao'];
        $dataPro =  date("d/m/Y H:i", $protocolo['timecreated']);
        $tcc = $DB->get_record('tcccafeead', array('id'=>$_GET['tccid']));


        switch ($statusPro) {
            case 2:
            $situacaoPro = "FORMATO DE ARQUIVO INVÁLIDO";
            break;
            case 1:
            $situacaoPro = "ARQUIVO ENVIADO";
            break;
            case 0:
            $situacaoPro = "ERRO NO ENVIO DO ARQUIVO ";
            break;
          default:
            $situacaoPro = "ARQUIVO NÃO ENVIADO";
            break;
        }
        $bloco  = "<tr>";
        $bloco .="<td class=''>$numeroPro</td>";
        $bloco .="<td class=''>$arquivoPro</td>";
        $bloco .="<td class=''>$dataPro</td>";
        $bloco .="<td class=''>$situacaoPro</td>";
        $bloco .= "</td>";
        $bloco .="</tr>";

        $protocolo['html'] = $bloco;

        $response = json_decode(
            notificar( "TCC : Nova postagem - ".$dataPro,
                        "PROTOCOLO: " . $numeroPro. " - " . $tcc->name ,
                        $arquivoPro." ENVIADO COM SUCESSO!" ,
                        "activity_history",
                        "",
                        "[".$matriculasProtocolo."]" )
         );
        $mensagem = "";
        if( !empty( $response->errors )  ){
          $protocolo['status_api'] = 422;
          $mensagem = "ERROS: ";
          foreach ($response->errors as $erro) {
            $mensagem .= "{$erro}, ";
          }
          $log->log("ERRO[NOTIFICACAO]: ERRO NO ENVIO DA NOTIFICAÇÃO [".$mensagem."]" );
        } else {
          $protocolo['status_api'] = 200;

          $mensagem = ( isset($response->message) ) ? $response->message : "NENHUMA RESPOSTA";
          $log->log("SUCESSO[NOTIFICACAO]: ENVIO DA NOTIFICAÇÃO EXECUTADO" );
        }
        $protocolo['mensagem_api'] = $mensagem;
        $DB->update_record('tcccafeead_protocolo', $protocolo);

        $dados['protocolo'] = $protocolo;
    }
    echo json_encode($dados);
