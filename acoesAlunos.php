<?php
    //incluindo autoload para poder acarregar classes automaticamente
    include "./autoload.php";
    require_once('../../config.php');
    //executando ações de acordo com o post recebido
    switch($_POST['acao']/*proteger esta variável assim que possível*/){
        case 'gravarFormulario':
				C_Group::mudarParaEnviado($_POST['groupid']);
                die(M_Formulario::gravar($_POST));
            break;


        default : echo'<br><b>Cadê o Wally?</b>';

    }
