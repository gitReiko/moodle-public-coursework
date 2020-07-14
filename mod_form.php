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
        $mform->addElement('checkbox', 'automatictaskobtaining', get_string('automatic_task_obtaining', 'coursework'));
        
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

}











