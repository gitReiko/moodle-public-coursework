<?php

require_once("$CFG->libdir/formslib.php");

use coursework_lib as lib;

class StudentFileManager extends moodleform 
{
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 

        $fileoptions = $this->_customdata['fileoptions'];

        $mform->addElement('filemanager', 'students_filemanager',
                get_string('student_files', 'coursework'), null, $fileoptions);
        
        $cm = get_coursemodule_from_id('coursework', $PAGE->cm->id);
        if(lib\is_user_student($cm, $USER->id))
        {
            $mform->addElement('hidden', 'id', $PAGE->cm->id);
            $mform->setType('id', PARAM_INT);
    
            $this->add_action_buttons(false, get_string('save', 'coursework'));
        }
    }
}

class TeacherFileManager extends moodleform 
{
    //Add elements to form
    public function definition() {
        global $CFG, $PAGE, $USER;
 
        $mform = $this->_form; // Don't forget the underscore! 

        $fileoptions = $this->_customdata['fileoptions'];

        $mform->addElement('filemanager', 'teachers_filemanager',
                get_string('teacher_files', 'coursework'), null, $fileoptions);
        
        $cm = get_coursemodule_from_id('coursework', $PAGE->cm->id);
        if(lib\is_user_teacher($cm, $USER->id))
        {
            $mform->addElement('hidden', 'id', $PAGE->cm->id);
            $mform->setType('id', PARAM_INT);
            $mform->addElement('hidden', 'gui_event', 'user_work');
            $mform->setType('gui_event', PARAM_TEXT);
            $mform->addElement('hidden', 'studentid', $this->_customdata['work']->student);
            $mform->setType('studentid', PARAM_INT);
    
            $this->add_action_buttons(false, get_string('save', 'coursework'));
        }
    }
}

class FileManager extends ViewModule 
{
    private $work;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->work = $this->get_student_work();
    }

    protected function get_module_name() : string
    {
        return 'filemanager';
    }

    protected function get_module_header() : string
    {
        return get_string('filemanager', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = $this->get_student_files();
        $body.= $this->get_teachers_files();
        return $body;
    }

    private function get_student_work() : stdClass 
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$this->studentId);
        return $DB->get_record('coursework_students', $where);
    }

    private function get_student_files() : string 
    {
        global $USER;
        if(lib\is_user_student($this->cm, $USER->id))
        {
            return $this->get_students_files_manager();
        }
        else 
        {
            return $this->get_student_files_list();
        }
    }

    private function get_students_files_manager() : string 
    {
        $fileoptions = array(
            'maxbytes' => 0,
            'maxfiles' => '3',
            'subdirs' => 0,
            'context' => context_module::instance($this->cm->id)
        );
        
        $data = new stdClass();
        
        $data = file_prepare_standard_filemanager($data, 'students',
                $fileoptions, context_module::instance($this->cm->id), 'mod_coursework', 'students', $this->work->student); // 0 is the item id.
        
        $mform = new StudentFileManager(
            null,
            array
            (
                'fileoptions' => $fileoptions,
            )
        );
        
        if ($formdata = $mform->get_data()) 
        {
            // Save the file.
            $data = file_postupdate_standard_filemanager($data, 'students',
            $fileoptions, context_module::instance($this->cm->id), 'mod_coursework', 'students', $this->work->student);
            $this->send_notification_to_teacher();
        } 
        else 
        {
            // Display the form.
            $mform->set_data($data);
        }

        $manager = '<h4>'.get_string('student_files', 'coursework').'</h4>';
        $manager.= $mform->render();
        return $manager;
    }

    private function get_student_files_list() : string 
    {
        $list = '<h4>'.get_string('student_files', 'coursework').'</h4>';

        $files = $this->get_files_list('students', $this->work->student);
        if(empty($files))
        {
            $list.= '<p>'.get_string('absent', 'coursework').'</p>';
        }
        else
        {
            $list.= $files;
        }

        return $list;
    }

    private function get_teachers_files() : string 
    {
        global $USER;
        if(lib\is_user_teacher($this->cm, $USER->id))
        {
            return $this->get_teacher_files_manager();
        }
        else 
        {
            return $this->get_teacher_files_list();
        }
    }

    private function get_teacher_files_manager() : string 
    {
        $fileoptions = array(
            'maxbytes' => 0,
            'maxfiles' => '3',
            'subdirs' => 0,
            'context' => context_module::instance($this->cm->id)
        );
        
        $data = new stdClass();
        
        $data = file_prepare_standard_filemanager($data, 'teachers',
                $fileoptions, context_module::instance($this->cm->id), 'mod_coursework', 'teachers', $this->work->teacher);
        
        $mform = new TeacherFileManager(
            null,
            array
            (
                'fileoptions' => $fileoptions,
                'work' => $this->work,
            )
        );
        
        if($formdata = $mform->get_data()) 
        {
            // Save the file.
            $data = file_postupdate_standard_filemanager($data, 'teachers',
            $fileoptions, context_module::instance($this->cm->id), 'mod_coursework', 'teachers', $this->work->teacher);
            $this->send_notification_to_student();
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

    private function get_teacher_files_list() : string 
    {
        $list = '<h4>'.get_string('teacher_files', 'coursework').'</h4>';

        $files = $this->get_files_list('teachers', $this->work->teacher);
        if(empty($files))
        {
            $list.= '<p>'.get_string('absent', 'coursework').'</p>';
        }
        else
        {
            $list.= $files;
        }

        return $list;
    }

    private function get_files_list($area, $itemid)
    {
        $list = '';
 
        $fs = get_file_storage();
        $context = context_module::instance($this->cm->id);
        $files = $fs->get_area_files($context->id, 'mod_coursework', $area, $itemid);
        foreach($files as $file) 
        {
            $fileName = $file->get_filename();
            $fileUrl = moodle_url::make_pluginfile_url($file->get_contextid(), 'mod_coursework', 
                                                        $area, $file->get_itemid(), 
                                                        $file->get_filepath(), $file->get_filename());

            if($fileName != '.')
            {
                $list.= "<p><a href='$fileUrl' target='_blank' >";
                $list.= $file->get_filename();
                $list.= '</a></p>';
            }
        }
        
        return $list;
    }

    private function send_notification_to_teacher() : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'student_upload_file';
        $userFrom = lib\get_user($this->work->student); 
        $userTo = lib\get_user($this->work->teacher); 
        $headerMessage = get_string('student_upload_file_header','coursework'); // Закончил здесь
        $fullMessageHtml = $this->get_student_html_message();

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);

    }

    private function get_student_html_message() : string
    {
        $message = get_string('student_upload_file_header','coursework');
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    private function send_notification_to_student() : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'teacher_upload_file';
        $userFrom = lib\get_user($this->work->teacher); 
        $userTo = lib\get_user($this->work->student); 
        $headerMessage = get_string('teacher_upload_file_header','coursework');
        $fullMessageHtml = $this->get_teacher_html_message();

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);

    }

    private function get_teacher_html_message() : string
    {
        $message = get_string('teacher_upload_file_header','coursework');
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

}




