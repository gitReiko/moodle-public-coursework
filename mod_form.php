<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}
 
require_once($CFG->dirroot.'/course/moodleform_mod.php');
 
class mod_coursework_mod_form extends moodleform_mod {
 
    function definition() {
        global $CFG, $DB, $OUTPUT, $COURSE;
 
        $mform =& $this->_form;

        // Name.
        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));

        // Intro editor
        $this->standard_intro_elements(get_string('guidelines', 'coursework'));

        // Because this code above standard_intro_elements broke them.
        if (!empty($CFG->formatstringstriptags)) 
        {
            $mform->setType('name', PARAM_TEXT);
        } 
        else 
        {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        

        // Section header title according to language file.
        $mform->addElement('header', 'task_template', get_string('task_template', 'coursework'));
        $mform->setExpanded('task_template', false);
        $mform->addElement('checkbox', 'usetask', get_string('use_task', 'coursework'));
        $mform->addElement('checkbox', 'autotaskissuance', get_string('automatic_task_obtaining', 'coursework'));

        // File sizes
        $mform->addElement('header', 'coursework_files', get_string('coursework_files', 'coursework'));
        $mform->setExpanded('coursework_files', false);

        $sizes = get_max_upload_sizes($CFG->maxbytes, $COURSE->maxbytes, 0, 0);
        $mform->addElement('select', 'maxfilesize', get_string('max_files_size', 'coursework'), $sizes);
        $mform->addHelpButton('maxfilesize', 'max_files_size', 'coursework');

        $number = array
        (
            1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5,
            6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10
        );
        $mform->addElement('select', 'maxfilesnumber', get_string('max_files_number', 'coursework'), $number);
        $mform->setDefault('maxfilesnumber', 2);
        $mform->addHelpButton('maxfilesnumber', 'max_files_number', 'coursework');
        
        // Default
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();

    }

}











