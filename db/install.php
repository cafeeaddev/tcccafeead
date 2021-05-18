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
 * Provides code to be executed during the module installation
 *
 * This file replaces the legacy STATEMENTS section in db/install.xml,
 * lib.php/modulename_install() post installation hook and partially defaults.php.
 *
 * @package    mod_tcccafeead
 * @copyright  CafeEAD cafeead@cafeead.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Post installation procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_tcccafeead_install() {
    global $DB;
    
    $in = $DB->get_record('external_functions', array('name'=>'tcccafeead_inserir'));
    $at = $DB->get_record('external_functions', array('name'=>'tcccafeead_atualizar'));
    $ex = $DB->get_record('external_functions', array('name'=>'tcccafeead_excluir'));
    $columns8 = array("name", "classname", "methodname", "classpath", "component", "capabilities");
    
    $records8 = array();
    if($in === false){ 
        $records8[] = array_combine($columns8, array('tcccafeead_inserir','M_TCC_ws','inserir','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
    }
    if($at === false){
        $records8[] = array_combine($columns8, array('tcccafeead_atualizar','M_TCC_ws','atualizar','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
    }
    if($ex === false){
        $records8[] = array_combine($columns8, array('tcccafeead_excluir','M_TCC_ws','excluir','mod/tcccafeead/class/tcc/model/M_TCC_ws.php','mod_tcccafeead',''));
    }
    foreach ($records8 as $record) {
        $DB->insert_record('external_functions', $record, false);
    }
    
    $columns = array( 'short_text', 'full_text');
    $records = array(
        array_combine($columns, array('Seu trabalho foi recebido...', 'Seu trabalho foi recebido, em breve será corrigido')),
        array_combine($columns, array('Seu trabalho foi avaliado (...) satisfatório', 'Seu trabalho foi avaliado como satisfatório. Tente aprofundar o tema.')),
        array_combine($columns, array('Seu traballho foi avaliado (...) excelente', 'Seu trabalho foi avaliado como excelente. Aguarde a banca de tcc.'))
        
    );
    foreach ($records as $record) {
        $DB->insert_record('tcccafeead_chat_text', $record, false);
    }
    
    $columns2 = array('name', 'input', "axtable");
    $records2 = array(
        array_combine($columns2, array('Título', 'text', 'NULL')),
        array_combine($columns2, array('Resumo', 'textarea', 'NULL')),
		array_combine($columns2, array('Introdução', 'textarea', 'NULL')),
        array_combine($columns2, array('Linha de Pesquisa','select', 'tcccafeead_research_lines')),
        array_combine($columns2, array('Tema', 'text', 'NULL')),
        array_combine($columns2, array('Problema de pesquisa', 'text', 'NULL')),
        array_combine($columns2, array('Hipótese', 'text', 'NULL')),
        array_combine($columns2, array('Justificativa', 'text', 'NULL')),
        array_combine($columns2, array('Objetivo Geral', 'text', 'NULL')),
        array_combine($columns2, array('Objetivos Específicos', 'text', 'NULL')),
        array_combine($columns2, array('Revisão da Literatura', 'text', 'NULL')),
        array_combine($columns2, array('Metodologia', 'text', 'NULL')),
        array_combine($columns2, array('Cronograma', 'text', 'NULL')),
        array_combine($columns2, array('Referências', 'text', 'NULL'))
        
    );

    foreach ($records2 as $record) {
        $DB->insert_record('tcccafeead_form_type', $record, false);
    }

    
    $columns3 = array('name');
    $records3 = array(
        array_combine($columns3, array('Trabalho')),
        array_combine($columns3, array('Apresentação'))
    );
   
    foreach ($records3 as $record) {
        $DB->insert_record('tcccafeead_grade_type', $record, false);
    }
    $columns4 = array('name', 'description');
    $records4 = array(
        array_combine($columns4, array('pendente', NULL)),
        array_combine($columns4, array('aguardando aprovação', NULL)),
        array_combine($columns4, array('aprovado', NULL)),
        array_combine($columns4, array('negado', NULL))
    );
   
    foreach ($records4 as $record) {
        $DB->insert_record('tcccafeead_group_form_status', $record, false);
    }
    
    $columns5 = array('name', 'description', 'classname');
    $records5 = array(
        array_combine($columns5, array('Aprovação', 'Um ou mais itens precisam de aprovação.', 'aguardandoAprovacao')),
        array_combine($columns5, array('Aguardando Postagem', '', 'aguardandoPostagem')),
        array_combine($columns5, array('Enviado', 'Postagem aguardando correção.', 'enviado')),
        array_combine($columns5, array('Em Correção', '', 'emCorrecao')),
        array_combine($columns5, array('Orientado', '', 'orientado')),
        array_combine($columns5, array('Aguardando Banca', '', 'aguardandoBanca')), 
        array_combine($columns5, array('Finalizado', '', 'finalizado'))
        
    );
   
    foreach ($records5 as $record) {
        $DB->insert_record('tcccafeead_group_status', $record, false);
    }
    
    $columns6 = array('name');
    $records6 = array(
        array_combine($columns6, array('Graduação')),
        array_combine($columns6, array('Pós-graduação'))
    );
   
    foreach ($records6 as $record) {
        $DB->insert_record('tcccafeead_level', $record, false);
    }
    
    $columns7 = array('name');
    $records7 = array(
        array_combine($columns7, array('CULTURA,  TECNOLOGIA E APRENDIZAGEM')),
        array_combine($columns7, array('SUSTENTABILIDADE ORGANIZACIONAL'))
    );
   
    foreach ($records7 as $record) {
        $DB->insert_record('tcccafeead_research_lines', $record, false);
    }    
    
}
 
 
 


/**
 * Post installation recovery procedure
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_tcccafeead_install_recovery() {
}