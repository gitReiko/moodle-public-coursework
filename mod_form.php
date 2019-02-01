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

        $this->standard_intro_elements(get_string('intro', 'coursework'));
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

}











