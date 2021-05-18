<?php 
//chamando a uri atual do navegador
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

$uriAtual = $protocolo . $_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']) . '/view.php?id=' .$courseModule->id ;
//verificando quantos botões devem aparecer e configurando o css de acordo
switch($permissoes):
    case 2: 
        $l = 'l33';
        break;
    case 3: 
        $l = 'l33';
        break;
endswitch;
?>
<div class="grid" id="menuPrincipal">
    <?php
    //CRIANDO BOTÕES DE ACORDO COM AS PERMISSÕES DO USUÁRIO
    //deve ver cofigurações
    if($pConfig){
        echo"<div class='$l h2'><a class='botaoQuadrado' href='$uriAtual&p=coordenacao' id='bConfigurar'><p>Coordenar</p></a></div>";
    }
    ?>
    <?php
    if(isset($pTeacher)){
        echo"<div class='$l '><a class='botaoQuadrado' href='$uriAtual&p=correcao' id='bCorrigir'><p>Corrigir</p></a></div>";
    }
    ?><?php
    if($pEnviarTrabalho){
        echo"<div class='$l'><a class='botaoQuadrado' href='$uriAtual&p=envio' id='bEnviar'><p>Enviar</p></a></div>";
    }
    ?>
</div>