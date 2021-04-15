<?php

require_once("$CFG->libdir/formslib.php");

use Coursework\View\StudentsWork\Locallib as locallib;

class TeacherFileManager extends moodleform 
{
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 

        $fileoptions = $this->_customdata['fileoptions'];

        $mform->addElement('filemanager', 'teacher_filemanager',
                get_string('teacher_files', 'coursework'), null, $fileoptions);
        
        $cm = get_coursemodule_from_id('coursework', $PAGE->cm->id);

        $mform->addElement('hidden', 'id', $PAGE->cm->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'gui_event', 'user_work');
        $mform->setType('gui_event', PARAM_TEXT);
        $mform->addElement('hidden', 'studentid', $this->_customdata['work']->student);
        $mform->setType('studentid', PARAM_INT);

        $this->add_action_buttons(false, get_string('save_changes', 'coursework'));
    }
}