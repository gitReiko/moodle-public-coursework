<?php

namespace Coursework\View\StudentWork\Components;

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\StudentsGetter as sg;

class Navigation extends Base 
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->work = sg::get_students_work($cm->instance, $studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_navigation_content';
    }

    protected function get_header_text() : string
    {
        return get_string('navigation', 'coursework');
    }

    protected function get_content() : string
    {
        if(empty($this->work))
        {
            return $this->get_back_to_course_button();
        }
        else if(locallib::is_user_student($this->work))
        {
            return $this->get_back_to_course_button();
        }
        else 
        {
            return $this->get_back_to_works_list_button();
        }
    }

    private function get_back_to_works_list_button() : string 
    {
        $text = get_string('back_to_works_list', 'coursework');
        $btn = \html_writer::tag('button', $text);

        $attr = array('href' => '/mod/coursework/view.php?id='.$this->cm->id);
        return \html_writer::tag('a', $btn, $attr);
    }

    private function get_back_to_course_button() : string 
    {
        $text = get_string('back_to_course', 'coursework');
        $btn = \html_writer::tag('button', $text);

        $attr = array('href' => '/course/view.php?id='.$this->cm->course);
        return \html_writer::tag('a', $btn, $attr);
    }



}
