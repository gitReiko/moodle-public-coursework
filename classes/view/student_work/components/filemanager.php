<?php

namespace Coursework\View\StudentsWork\Components;

use Coursework\View\StudentsWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;

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
        $content.= $this->get_change_my_files_button();
        $content.= \html_writer::end_tag('div');
        $content.= $this->get_change_my_files_form();

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

        $fmId = $this->work->teacher.'_'.$this->work->student;
        $filesList = $this->get_files_list('teacher', $fmId);

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
            $fileUrl = \moodle_url::make_pluginfile_url($file->get_contextid(), 'mod_coursework', 
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

    private function get_change_my_files_button() : string 
    {
        $attr = array(
            'class' => 'button',
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
            'name' => \StudentWorkMain::TO_PAGE,
            'value' => \StudentWorkMain::SAVE_FILES
        );
        $form.= \html_writer::empty_tag('input', $attr);


        $form.= \html_writer::end_tag('form');

        return $form;
    }




}
