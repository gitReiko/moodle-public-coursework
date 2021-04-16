<?php

namespace Coursework\View\StudentWork\SaveFiles;

require_once("$CFG->libdir/formslib.php");

use Coursework\View\StudentsWork\Locallib as locallib;

class StudentFileManager extends \moodleform 
{
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 

        $fileoptions = $this->_customdata['fileoptions'];

        $mform->addElement('filemanager', 'student_filemanager',
                get_string('my_files', 'coursework'), null, $fileoptions);
        
        $cm = get_coursemodule_from_id('coursework', $PAGE->cm->id);

        $mform->addElement('hidden', 'id', $PAGE->cm->id);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(false, get_string('save_changes', 'coursework'));
    }
}

