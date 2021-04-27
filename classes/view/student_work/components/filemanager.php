<?php

namespace Coursework\View\StudentWork\Components;

use Coursework\View\StudentWork\SaveFiles\StudentFileManager;
use Coursework\View\StudentWork\SaveFiles\TeacherFileManager;
use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\View\StudentWork\Main as StudentWorkMain;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;

use coursework_lib as lib;
use ViewMain as m;

class Filemanager extends Base 
{
    const FORM_ID = 'change_my_files_form';

    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = sg::get_students_work($cm->instance, $studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_filemanager_content';
    }

    protected function get_header_text() : string
    {
        return get_string('filemanager', 'coursework');
    }

    protected function get_content() : string
    {
        $attr = array('class' => 'workFilemanagerGrids');
        $content = \html_writer::start_tag('div', $attr);
        $content.= $this->get_grids_header();
        $content.= $this->get_student_files();
        $content.= $this->get_teacher_files();

        if(locallib::is_user_student_or_teacher($this->work))
        {
            $content.= $this->get_change_my_files_button();
            $content.= $this->get_change_my_files_form();
        }
        
        $content.= \html_writer::end_tag('div');

        if(locallib::is_user_student($this->work))
        {
            $this->save_student_files();
        }

        if(locallib::is_user_teacher($this->work))
        {
            $this->save_teacher_files();
        }

        return $content;
    }

    private function get_grids_header() : string 
    {
        $attr = array('class' => 'header');
        $text = get_string('files_attached_to_work', 'coursework');
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_student_files() : string 
    {
        if(locallib::is_user_student($this->work))
        {
            $text = get_string('my_files', 'coursework');
        }
        else 
        {
            $text = get_string('student_files', 'coursework');
        }

        $filesList = $this->get_files_list('student', $this->work->student);

        if(empty($filesList))
        {
            $text.= \html_writer::tag('p', get_string('absent', 'coursework'));
        }
        else
        {
            $text.= $filesList;
        }
        
        return \html_writer::tag('div', $text);
    }

    private function get_teacher_files() : string 
    {
        if(locallib::is_user_teacher($this->work))
        {
            $text = get_string('my_files', 'coursework');
        }
        else 
        {
            $text = get_string('teacher_files', 'coursework');
        }

        $filesList = $this->get_files_list(
            'teacher'.$this->work->teacher, 
            $this->work->student
        );

        if(empty($filesList))
        {
            $text.= \html_writer::tag('p', get_string('absent', 'coursework'));
        }
        else
        {
            $text.= $filesList;
        }
        
        return \html_writer::tag('div', $text);
    }

    private function get_files_list($area, $itemid)
    {
        $list = '';
 
        $fs = get_file_storage();
        $context = \context_module::instance($this->cm->id);
        $files = $fs->get_area_files($context->id, 'mod_coursework', $area, $itemid);
        foreach($files as $file) 
        {
            $fileName = $file->get_filename();
            $fileUrl = \moodle_url::make_pluginfile_url(
                $file->get_contextid(), 'mod_coursework', 
                $area, $file->get_itemid(), 
                $file->get_filepath(), $file->get_filename()
            );

            if($fileName != '.')
            {
                $list.= "<p><a href='$fileUrl' target='_blank' >";
                $list.= $file->get_filename();
                $list.= '</a></p>';
            }
        }
        
        return $list;
    }

    private function get_change_my_files_button() : string 
    {
        $attr = array(
            'class' => 'button changeMyFilesButton',
            'onclick' => 'submit_form(`'.self::FORM_ID.'`)'
        );
        $text = get_string('change_my_files', 'coursework');
        return \html_writer::tag('div', $text, $attr);
    }

    private function get_change_my_files_form() : string 
    {
        $attr = array(
            'id' => self::FORM_ID,
            'method' => 'post'
        );
        $form = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => m::ID,
            'value' => $this->cm->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => m::GUI_EVENT,
            'value' => m::USER_WORK
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => m::STUDENT_ID,
            'value' => $this->work->student
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => StudentWorkMain::TO_PAGE,
            'value' => StudentWorkMain::SAVE_FILES
        );
        $form.= \html_writer::empty_tag('input', $attr);


        $form.= \html_writer::end_tag('form');

        return $form;
    }

    private function save_student_files()
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
            $data, 'student', $fileoptions, 
            $context, 'mod_coursework', 
            'student', $this->work->student
        );
        
        $mform = new StudentFileManager(
            null,
            array
            (
                'fileoptions' => $fileoptions,
            )
        );
        
        if($formdata = $mform->get_data()) 
        {
            // Save the file.
            $data = file_postupdate_standard_filemanager(
                $data, 'student', $fileoptions, $context, 
                'mod_coursework', 'student', $this->work->student
            );
            $this->send_notification_to_teacher();
        } 
    }

    private function send_notification_to_teacher() : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'student_upload_file';
        $userFrom = cg::get_user($this->work->student);
        $userTo = cg::get_user($this->work->teacher); 
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

    private function save_teacher_files()
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
            $data, 'teacher', $fileoptions, 
            $context, 'mod_coursework', 
            'teacher'.$this->work->teacher, 
            $this->work->student
        );
        
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
            $data = file_postupdate_standard_filemanager(
                $data, 'teacher', $fileoptions, $context, 'mod_coursework', 
                'teacher'.$this->work->teacher, $this->work->student
            );
            $this->send_notification_to_student();
        }
    }

    private function send_notification_to_student() : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'teacher_upload_file';
        $userFrom = cg::get_user($this->work->teacher); 
        $userTo = cg::get_user($this->work->student); 
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
