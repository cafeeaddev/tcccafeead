<?php
    //incluindo autoload para poder acarregar classes automaticamente
    include "./autoload.php";
    require_once('../../config.php');
    //executando ações de acordo com o post recebido
    switch($_POST['acao']/*proteger esta variável assim que possível*/){
        case 'montarFormulario':
            $courseid = (isset($_POST['courseid']))? $_POST['courseid'] : '';
			if( $courseid == '' ){
				$courseid = (isset($_GET['course']))? $_GET['course'] : '';
			}

            V_FormularioConfiguracao::montar($_POST['update'], $courseid);
            break;
        case 'inserir':
            $sqlInsert = M_TCC::inserir($_POST);
            echo"$sqlInsert";
            break;
        case 'atualizar':
            $sqlAtu = M_TCC::atualizar($_POST);
            echo "$sqlAtu";
			         break;

        default : echo 'Cadê o Wally?';

    }
?>
