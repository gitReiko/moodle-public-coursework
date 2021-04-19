<?php 

namespace Coursework\View\ManageOldFilesArea;

require_once("$CFG->libdir/formslib.php");

class TeacherFileManager extends \moodleform 
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
        $mform->addElement('hidden', Main::SELECTED_TEACHER_ID, $this->_customdata['selectedTeacherId']);
        $mform->setType(Main::SELECTED_TEACHER_ID, PARAM_INT);

        $this->add_action_buttons(false, get_string('save', 'coursework'));
    }
}

class FileManager 
{
    private $cm;
    private $selectedTeacherId;

    function __construct(\stdClass $cm, $selectedTeacherId)
    {
        $this->cm = $cm;
        $this->selectedTeacherId = $selectedTeacherId;
    }

    public function get() : string 
    {
        $context = \context_module::instance($this->cm->id);

        $fileoptions = array(
            'maxbytes' => 0,
            'maxfiles' => '3',
            'subdirs' => 0,
            'context' => $context
        );
        
        $data = new \stdClass();
        
        $data = file_prepare_standard_filemanager(
            $data, 
            'teacher', 
            $fileoptions, 
            $context, 
            'mod_coursework', 
            'teacher', 
            $this->selectedTeacherId
        );
        
        $mform = new TeacherFileManager(
            null, 
            array(
                'fileoptions' => $fileoptions,
                'selectedTeacherId' => $this->selectedTeacherId
            )
        );
        
        if($formdata = $mform->get_data()) 
        {
            // Save the file.
            $data = file_postupdate_standard_filemanager(
                $data, 
                'teacher',
                $fileoptions, 
                $context, 
                'mod_coursework', 'teacher', 
                $this->selectedTeacherId
            );
        } 
        else 
        {
            // Display the form.
            $mform->set_data($data);
        }

        $manager = '<h4>'.get_string('teacher_files', 'coursework').'</h4>';
        $manager.= $mform->render();
        return $manager;
    }


}
