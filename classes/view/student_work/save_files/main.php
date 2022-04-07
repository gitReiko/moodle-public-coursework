<?php 

namespace Coursework\View\StudentWork\SaveFiles;

require_once 'student_file_manager.php';
require_once 'teacher_file_manager.php';

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\View\Main as view_main;

class Main 
{
    private $course;
    private $cm;
    private $studentId;

    private $student;
    private $coursework;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->student = sg::get_student_with_his_work($cm->instance, $studentId);
        $this->coursework = cg::get_coursework($cm->instance);
    }

    public function get_page() : string 
    {
        $page = '';

        if(locallib::is_user_student($this->student))
        {
            $page.= $this->get_student_files_manager();
        }
        else if(locallib::is_user_teacher($this->student))
        {
            $page.= $this->get_teacher_files_manager();
        }
        else 
        {
            $page.= 'Error. Only students or teachers can manage files.';
        }

        $page.= $this->get_back_to_coursework_button();

        return $page;
    }

    private function get_student_files_manager() : string 
    {
        $context = \context_module::instance($this->cm->id);

        $fileoptions = array(
            'maxbytes' => $this->coursework->maxfilesize,
            'maxfiles' => $this->coursework->maxfilesnumber,
            'subdirs' => 0,
            'context' => $context
        );
        
        $data = new \stdClass();
        
        $data = file_prepare_standard_filemanager(
            $data, 'student', $fileoptions, 
            $context, 'mod_coursework', 
            'student', $this->student->id
        );
        
        $mform = new StudentFileManager(
            null,
            array
            (
                'fileoptions' => $fileoptions,
            )
        );
        
        $mform->set_data($data);

        $manager = '<h4>'.get_string('student_files', 'coursework').'</h4>';
        $manager.= $mform->render();
        return $manager;
    }

    private function get_teacher_files_manager() : string 
    {
        $context = \context_module::instance($this->cm->id);

        $fileoptions = array(
            'maxbytes' => $this->coursework->maxfilesize,
            'maxfiles' => $this->coursework->maxfilesnumber,
            'subdirs' => 0,
            'context' => $context
        );
        
        $data = new \stdClass();
        
        $data = file_prepare_standard_filemanager(
            $data, 'teacher', $fileoptions, 
            $context, 'mod_coursework', 
            'teacher', 
            $this->student->id
        );
        
        $mform = new TeacherFileManager(
            null,
            array
            (
                'fileoptions' => $fileoptions,
                'student' => $this->student,
            )
        );
        
        $mform->set_data($data);

        $manager = '<h4>'.get_string('teacher_files', 'coursework').'</h4>';
        $manager.= $mform->render();
        return $manager;
    }

    private function get_back_to_coursework_button() : string 
    {
        if(locallib::is_user_student($this->student))
        {
            return $this->get_back_to_coursework_student_button();
        }
        else if(locallib::is_user_teacher($this->student))
        {
            return $this->get_back_to_coursework_teacher_button();
        }
    }

    private function get_back_to_coursework_student_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'class' => 'btn btn-primary back_to_coursework',
            'value' => get_string('back_to_coursework_without_save_changes', 'coursework')
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $url = '/mod/coursework/view.php?id='.$this->cm->id;
        $attr = array('href' => $url);
        $btn = \html_writer::tag('a', $btn, $attr);

        $btn = $this->get_button_margin_block($btn);

        return $btn;
    }

    private function get_back_to_coursework_teacher_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'class' => 'btn btn-primary back_to_coursework',
            'value' => get_string('back_to_coursework_without_save_changes', 'coursework')
        );
        $btn = \html_writer::empty_tag('input', $attr);

        $url = '/mod/coursework/view.php?id='.$this->cm->id;
        $url.= '&'.view_main::GUI_EVENT.'='.view_main::USER_WORK;
        $url.= '&'.view_main::STUDENT_ID.'='.$this->studentId;

        $attr = array('href' => $url);
        $btn = \html_writer::tag('a', $btn, $attr);

        $btn = $this->get_button_margin_block($btn);

        return $btn;
    }

    private function get_button_margin_block(string $btn) : string 
    {
        $attr = array('class' => 'col-md-3');
        $div = \html_writer::tag('div', '', $attr);

        $attr = array('class' => 'col-md-9');
        $div.= \html_writer::tag('div', $btn, $attr);

        $attr = array('class' => 'row');
        $div = \html_writer::tag('div', $div, $attr);

        return $div;
    }


}
