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
 * The main tcccafeead configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_tcccafeead
 * @copyright  CafeEAD cafeead@cafeead.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 *
 * @package    mod_tcccafeead
 * @copyright  CafeEAD cafeead@cafeead.com.br
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class mod_tcccafeead_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    protected function definition() {
		 global $COURSE, $CFG, $DB, $PAGE;

        $mform =& $this->_form;
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $urlAtual = $protocolo . '' .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $urlAtualArray =  explode('/course', $urlAtual);
        $urlModulo = $urlAtualArray[0].'/mod/tcccafeead';
        $courseid = optional_param('course','', PARAM_INT);
        $sectionid = optional_param('section', '', PARAM_INT);
        $update = optional_param('update', '', PARAM_INT);






        if($courseid != ''){

    			$mform->addElement('hidden','courseid',$courseid);
    			$mform->addElement('hidden','sectionid',$sectionid);

		     }
			 $mform->addElement('hidden','update',$update);
			 $mform->addElement('static', null, '',
            '<script type="text/javascript" src="'.$urlModulo.'/js/jquery-1.11.2.min.js"></script>');
			 $mform->addElement('static', null, '',
            '<script type="text/javascript" src="'.$urlModulo.'/js/inputDate.js"></script>');
			 $mform->addElement('static', null, '',
            '<script type="text/javascript" src="'.$urlModulo.'/js/form.js"></script>');

        //$mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are showed.
        //$mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
       //$mform->addElement('text', 'update', get_string('tcccafeeadname', 'tcccafeead'), array('size' => '64'));
//        if (!empty($CFG->formatstringstriptags)) {
//            $mform->setType('name', PARAM_TEXT);
//        } else {
//            $mform->setType('name', PARAM_CLEAN);
//        }
        //$mform->addRule('name', null, 'required', null, 'client');
        //$mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        //$mform->addHelpButton('name', 'tcccafeeadname', 'tcccafeead');

        // Adding the standard "intro" and "introformat" fields.
        //$mform->addElement('editor', 'introeditor', 'nome', null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$this->context));
        //$this->add_intro_editor();

        // Adding the rest of tcccafeead settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        //$mform->addElement('static', 'label1', 'tcccafeeadsetting1', 'Your tcccafeead fields go here. Replace me!');

        //$mform->addElement('header', 'tcccafeeadfieldset', get_string('tcccafeeadfieldset', 'tcccafeead'));
        //$mform->addElement('static', 'label2', 'tcccafeeadsetting2', 'Your tcccafeead fields go here. Replace me!');

        // Add standard grading elements.
        //$this->standard_grading_coursemodule_elements();

        // Add standard elements, common to all modules.
        //$this->standard_coursemodule_elements();
        $this->standard_coursemodule_elements();
     //echo'<input name="update" type="hidden" value="36145" />';
        // Add standard buttons, common to all modules.
        $this->add_action_buttons();

    }
}
?>
