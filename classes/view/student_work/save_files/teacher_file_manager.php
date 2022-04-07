<?php

namespace Coursework\View\StudentWork\SaveFiles;

require_once("$CFG->libdir/formslib.php");

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\Main as MainView;

class TeacherFileManager extends \moodleform 
{
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 

        $fileoptions = $this->_customdata['fileoptions'];

        $mform->addElement('filemanager', 'teacher_filemanager',
                get_string('my_files', 'coursework'), null, $fileoptions);
        
        $cm = get_coursemodule_from_id('coursework', $PAGE->cm->id);

        $mform->addElement('hidden', 'id', $PAGE->cm->id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', MainView::GUI_EVENT, 'user_work');
        $mform->setType(MainView::GUI_EVENT, PARAM_TEXT);
        $mform->addElement('hidden', MainDB::STUDENT_ID, $this->_customdata['student']->id);
        $mform->setType(MainDB::STUDENT_ID, PARAM_INT);

        $this->add_action_buttons(false, get_string('save_changes', 'coursework'));
    }
}