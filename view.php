<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Prints a particular instance of tcccafeead
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_tcccafeead
 * @copyright  CafeEAD cafeead@cafeead.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define( "TCCCAFEEAD_DIR", dirname(__FILE__)."/" );
define( "TCCCAFEEAD_MOODLE_ROOT", dirname(dirname(dirname(__FILE__))));
// Replace tcccafeead with the name of your module and remove this line.
require_once(dirname(__FILE__)."/autoload.php");
require_once(TCCCAFEEAD_MOODLE_ROOT.'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // Course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // ... tcccafeead instance ID - it should be named as the first character of the module.

if ($id) {
    $courseModule         = get_coursemodule_from_id('tcccafeead', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $courseModule->course), '*', MUST_EXIST);
    $tcccafeead  = $DB->get_record('tcccafeead', array('id' => $courseModule->instance), '*', MUST_EXIST);
} else if ($n) {
    $tcccafeead  = $DB->get_record('tcccafeead', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $tcccafeead->course), '*', MUST_EXIST);
    $courseModule         = get_coursemodule_from_instance('tcccafeead', $tcccafeead->id, $course->id, false, MUST_EXIST);
} else {
    error('Você deve especificar um course_module ID ou uma ID de instância');
}

require_login($course, true, $courseModule);
$pagina = optional_param('p', '', PARAM_TEXT);
$pageNumber = optional_param('page', '', PARAM_INT);
$pageEnd = 50;
$pageStart = 0;

if($pageNumber == 0 || $pageNumber == ''){
	$pageStart = 0;
	$pageEnd = $pageStart + 50;
}else{
	$pageStart = $pageNumber * 50;
	$pageEnd = $pageStart + 50;
}

//$event = \mod_tcccafeead\event\course_module_viewed::create(array(
//    'objectid' => $PAGE->cm->instance,
//    'context' => $PAGE->context,
//));
//$event->add_record_snapshot('course', $PAGE->course);
//$event->add_record_snapshot($PAGE->cm->modname, $tcccafeead);
//$event->trigger();

// Print the page header.

$PAGE->set_url('/mod/tcccafeead/view.php', array('id' => $courseModule->id));
$PAGE->set_title(format_string($tcccafeead->name));
$PAGE->set_heading(format_string($course->fullname));

/*
 * Other things you may want to set - remove if not needed.
 * $PAGE->set_cacheable(false);
 * $PAGE->set_focuscontrol('some-html-id');
 * $PAGE->add_body_class('tcccafeead-'.$somevar);
 */

$systemcontext = context_system::instance();
$coursecontext = context_course::instance($course->id);
//$coursecontext = get_context_instance(CONTEXT_COURSE, $course->id);
$permissoes = 0; //contador de permissões

/////verificando as permissões do usuário no curso

$admins = get_admins();
$isadmin = false;
foreach($admins as $admin) {
    if ($USER->id == $admin->id) {
        $isadmin = true;
        break;
    }
}

if($isadmin === true){
	$permissoes++;
}


$pConfig = false;
$manager = get_role_users(1, $coursecontext);
$courseCreator = get_role_users(1, $coursecontext);
//Se ele pode alterar configurações do curso -
if(has_capability('moodle/course:view', $coursecontext)) {
    $pConfig = true;
    $permissoes++;

}

$isManager = false;
foreach($manager as $st){
	if($USER->id === $st->id){
		$isManager = true;
	}
}

if($isManager === true){
	$permissoes++;
}


$editingTeachers = get_role_users(4, $coursecontext);
$teachers = get_role_users(3, $coursecontext);
$students = get_role_users(5, $coursecontext);
$coordinators = get_role_users(1, $systemcontext);
$couserCoodinators = get_role_users(1, $coursecontext);

$isCoordinator = false;
foreach($coordinators as $st){
	if($USER->id === $st->id){
		$isCoordinator = true;
	}
}

foreach($couserCoodinators as $st){
	if($USER->id === $st->id){
		$isCoordinator = true;
	}
}

if($isCoordinator === true){
	$permissoes++;
}

$isStudent = false;
foreach($students as $st){
	if($USER->id === $st->id){
		$isStudent = true;
	}
}
if($isStudent === true){
	$permissoes++;
}
$isTeacher = false;
foreach($editingTeachers as $tea){
    if($tea->id === $USER->id){
        $isTeacher = true;
    }
}
foreach($teachers as $tea){
    if($tea->id === $USER->id){
        $isTeacher = true;
    }
}
if($isTeacher === true){
	$permissoes++;
}

$nomeGrupo = isset($_GET['nomeGrupo'])? $_GET['nomeGrupo']:'';
$nomeAluno = isset($_GET['nomeAluno'])? $_GET['nomeAluno']:'';
$nomeProfessor = isset($_GET['nomeProfessor'])? $_GET['nomeProfessor']:'';
$statusGrupo = isset($_GET['status'])? $_GET['status']:'';

if($isadmin === true || $isCoordinator === true){
	$arrayBuscaGrupos = array($course->id);
}else{
	$arrayBuscaGrupos = array($course->id, $USER->id);
}
$queryBusca = "";


if( $nomeGrupo != "" ){
	$queryBusca .= " AND g.name LIKE ? ";
	$arrayBuscaGrupos[] = "%$nomeGrupo%";
}



if( $nomeAluno != "" ){

	$nomes = explode(" ", $nomeAluno);

	$nome = isset( $nomes[0] )? $nomes[0]:'';

	$sobrenome = isset( $nomes[1] )? $nomes[1]:$nome;

	$idAlunos = $DB->get_records_sql('SELECT id FROM {user} WHERE firstname LIKE ? or lastname LIKE ?  ' , array( "%$nome%" , "%$sobrenome%" ) );

  if ( count($idAlunos) > 0){
    $queryBuscaAluno = " AND gm.userid IN ( ";
  	foreach($idAlunos as $idAluno){
  		$queryBuscaAluno .= $idAluno->id.",";
  	}
  	$queryBuscaAluno = rtrim( $queryBuscaAluno, ",")." ) ";


  }else{
    $queryBuscaAluno = " AND gm.userid IN ( 0 )";
  }
  $queryBusca .= $queryBuscaAluno;
}

if( $nomeProfessor != "" ){

	$nomes = explode(" ", $nomeProfessor);

	$nome = isset( $nomes[0] )? $nomes[0]:'';

	$sobrenome = isset( $nomes[1] )? $nomes[1]:$nome;

	$idProfs = $DB->get_records_sql('SELECT id FROM {user} WHERE firstname LIKE ? or lastname LIKE ?  ' , array( "%$nome%" , "%$sobrenome%" ) );

  if ( count($idProfs) > 0){

    $queryBuscaProf = " AND gm.userid IN ( ";
  	foreach($idProfs as $idProf){
  		$queryBuscaProf .= $idProf->id.",";
  	}
  	$queryBuscaProf = rtrim( $queryBuscaProf, ",")." ) ";

  }else{
      $queryBuscaProf = " AND gm.userid IN ( 0 )";
  }

  $queryBusca .= $queryBuscaProf;

}




//Se ele pode enviar tarefas e pertence a grupo
if($isadmin === true || $isCoordinator === true){
	$grupoSelect = "SELECT DISTINCT g.* FROM {groups} g JOIN {groups_members} gm ON g.id = gm.groupid WHERE g.courseid = ? $queryBusca GROUP BY g.id order by g.id  ";
	$totalSql = "SELECT DISTINCT COUNT( DISTINCT g.id) total FROM mdl_groups g JOIN mdl_groups_members gm ON g.id = gm.groupid WHERE g.courseid = ? order by g.id;";
}else{
	$totalSql = "SELECT DISTINCT COUNT( DISTINCT g.id) total FROM mdl_groups g JOIN mdl_groups_members gm ON g.id = gm.groupid WHERE g.courseid = ? AND gm.userid = ? order by g.id;";
	$grupoSelect = "SELECT DISTINCT g.* FROM {groups} g JOIN {groups_members} gm ON g.id = gm.groupid WHERE g.courseid = ? AND gm.userid = ? GROUP BY g.id order by g.id  ";

}

$groups = $DB->get_records_sql($grupoSelect, $arrayBuscaGrupos, $pageStart, 50 );

$total = $DB->get_record_sql($totalSql, array($course->id, $USER->id) );

$totalDaPagina = count($groups);
$totalGeral = $total->total;

$totalPaginas = $totalGeral / 50;


if(count($groups)<= 0){
	$gm = false;
}

if($isStudent && count($groups) > 0) {
    $pEnviarTrabalho = true;
    //$permissoes++;
}else{
    $pEnviarTrabalho = false;
}
$groupStatiQ = $DB->get_records_select('tcccafeead_group_status', '', array(), 'id', '*', 0, 999999);
//$groupStati[] = "{id: 0 , nome:'Pendente'}";
foreach($groupStatiQ as $gsQ){

	if( $gsQ->id == 6 && $tcccafeead->levelid == 2){
		$gsQ->name = "Aguardando Certificação";
	}
    $groupStati[$gsQ->id] = $gsQ;
}
$groupStati2 = json_encode($groupStati);
$hash = hash('sha256', 'view.php Alterado em 18/09/2018 15:00');

// Output starts here.
echo $OUTPUT->header();

// Conditions to show the intro can change to look for own settings or whatever.
//incluíndo JAVASCRIPT E CSS específico do bloco
?>
<link rel="stylesheet" type="text/css" href="./css/estilo.css" charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="./materialG/css/materialG.css" charset="utf-8"/>
<link rel="stylesheet" type="text/css" href="./materialG/css/inputDate.css" charset="utf-8">
<link rel="stylesheet" type="text/css" href="./css/chat.css" charset="utf-8"/>
<script type="text/javascript" src="./js/jquery-1.12.2.min.js"></script>
<script type="text/javascript" src="./js/form.js"></script>
<script type="text/javascript" src="./js/chat.js"></script>
<script type="text/javascript" src="./js/arquivos.js"></script>
<script type="text/javascript" src="./materialG/js/materialG.js"></script>
<script type="text/javascript" src="./materialG/js/inputDate.js"></script>
    <div class='conteudo'>
	<!-- HASH DA VIEW: <?=$hash;?> -->
    <input type="hidden" value="<?=$tcccafeead->id?>" id="idTcc"/>
    <div id="groupStati" style="display: none;"/><?=$groupStati2?></div>
    <h1><?=$tcccafeead->name?></h1>

    <?php
    if ($tcccafeead->intro && $courseModule->showdescription) {
        echo "<div class='intro'>" . $tcccafeead->intro . '</div>';
    }
    ?>
</div>
<?php
//Caso o usuário tenha acesso a mais de uma página mostro o menu

if($permissoes > 1){?>
    <link rel="stylesheet" type="text/css" href="./css/menu.css" charset="utf-8"/>
    <script type="text/javascript" src="./js/menu.js"></script>
    <div class='conteudo'>
        <?php include "./conteudos/menu.php";?>
    </div>
    <?php

}
$mostrar = '';

//caso o usuário tenha acesso a uma única página ou já tenha clicado no menu
if($permissoes === 1 || $pagina != ''){


    if(($pConfig || $isCoordinator) && ($permissoes === 1 || $pagina === 'coordenacao')){
        $mostrar = 'coordenacao';
    }

    if($isTeacher && ($permissoes === 1 || $pagina === 'correcao')){
        $mostrar = 'correcao';
    }

    if($isStudent && ($permissoes === 1 || $pagina === 'envio')){
        $mostrar = 'envio';
    }
}else{
    if($pagina === ''){
        if($pConfig || $isCoordinator){
            $mostrar = 'coordenacao';
        }elseif($isTeacher){
            $mostrar = 'correcao';
        }elseif($isStudent){
            $mostrar = 'envio';
        }
    }
}


switch($mostrar){
        case 'coordenacao' :
            echo'<link rel="stylesheet" type="text/css" href="./css/listaDeGrupos.css" charset="utf-8"/>
            <script type="text/javascript" src="./js/listaDeGrupos.js"></script>
			<script type="text/javascript" src="./js/jspdf.min.js"></script>
			<script type="text/javascript" src="./js/html2canvas.min.js"></script>
            <div class="conteudo">';
                include "./conteudos/coordenacao.php";
            echo"</div>";

            break;
        case 'correcao' :
             echo'<link rel="stylesheet" type="text/css" href="./css/listaDeGrupos.css" charset="utf-8"/>
            <script type="text/javascript" src="./js/listaDeGrupos.js"></script>
			<script type="text/javascript" src="./js/jspdf.min.js"></script>
			<script type="text/javascript" src="./js/html2canvas.min.js"></script>
            <div class="conteudo">';
                include "./conteudos/listaDeGrupos.php";
            echo"</div>";
        break;
        case 'envio' :
             echo'<link rel="stylesheet" type="text/css" href="./css/aluno.css" charset="utf-8"/>
            <script type="text/javascript" src="./js/aluno.js"></script>
            <div class="conteudo">';
                include "./conteudos/aluno.php";
            echo"</div>";
        break;
    }
// Finish the page.
echo $OUTPUT->footer();
