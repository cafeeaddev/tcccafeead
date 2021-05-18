<?php
    //incluindo autoload para poder acarregar classes automaticamente
    include "./autoload.php";
    include('../../config.php');
    //executando ações de acordo com o post recebido
    switch($_POST['acao']/*proteger esta variável assim que possível*/){
        case 'confereUpload':
                M_GroupEnrolment::confereUpload($_POST['idEnrol']);
            break;
        case 'removeUpload':
                M_GroupEnrolment::removeUpload($_POST['idEnrol']);
            break;
		 case 'formSalvaComentario':
                M_GroupForm::salvaComentario($_POST['idTcc'], $_POST['idGroup'], $_POST['idFormType'], $_POST['comentario'] );
            break;
        case 'formConfirmaItem':
                M_GroupForm::confirmaItem($_POST['idTcc'], $_POST['idGroup'], $_POST['idFormType'], $_POST['comentario'] );
            break;
        case 'formCancelaItem':
                M_GroupForm::cancelaItem($_POST['idTcc'], $_POST['idGroup'], $_POST['idFormType'], $_POST['comentario']);
            break;
		case 'formNegaItem':
                M_GroupForm::negaItem($_POST['idTcc'], $_POST['idGroup'], $_POST['idFormType'], $_POST['comentario']);
            break;
        case 'chatBusca':
                die(M_Chat::buscar($_POST['idTcc'], $_POST['idGroup']));
            break;
        case 'chatEnviaMensagem':
                die(M_Chat::inserirMensagem($_POST['idTcc'], $_POST['idGroup'], $_POST['mensagem'], $_POST['institution']));
            break;
        case 'chatAtualiza':
                die(M_Chat::buscaNovasMensagens($_POST['idTcc'], $_POST['idGroup']));
            break;
        case 'chatConsultaNovas':
                die(M_Chat::contaNovasMensagens($_POST['tcc']));
            break;
        case 'chatBuscaTextosPreDefinidos':
                die(M_Chat::buscarMensagensPreDefinidas());
            break;
        case 'grupoVerificaStatus' :
                die(C_Group::verificaAtualizaGroupStatusAjax($_POST['idTcc'], $_POST['idGroup']));
            break;
        case 'emCorrecao' :
                die(C_Group::emCorrecao($_POST['groupid']));
            break;
        case 'orientado' :
                die(C_Group::orientado($_POST['groupid']));
            break;
        case 'enviado' :
                die(C_Group::enviado($_POST['groupid']));
            break;
        case 'aguardandoBanca':
        		die(C_Group::aguardandoBanca($_POST['groupid']));
        	break;
        case 'excluirArquivo' :
                die(C_Arquivos::apagarArquivo($_POST['tccid'], $_POST['groupid'], $_POST['stage'], $_POST['tipo'], $_POST['nome']));
            break;
        case 'atualizaStageGrade':
                die(M_Stagegrade::atualizar($_POST));
            break;
        case 'atualizaNota':
                die(M_Grade::atualizar($_POST));
            break;
        case 'fecharNotas':
                die(json_encode(M_Grade::fechar($_POST)));
            break;
        case 'abrirNotas':
                die(json_encode(M_Grade::abrir($_POST)));
            break;
        default : echo'<br><b>Cadê o Wally?'.$_POST['acao'].'</b>';

    }
