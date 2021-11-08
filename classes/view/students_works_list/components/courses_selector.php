<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWorksList\Page as p;
use Coursework\View\StudentsWorksList\MainGetter as mg;

class CoursesSelector 
{
    const COURSE = 'course';

    private $d;

    function __construct(MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_courses_selector() : string 
    {
        $selector = $this->get_selector_start();
        $selector.= $this->get_courses_options();
        $selector.= $this->get_selector_end();

        return $selector;
    }

    private function get_selector_start() : string 
    {
        $attr = array('class' => 'selector');
        $selector = \html_writer::start_tag('p', $attr);
        $selector.= $this->get_selector_label();

        $attr = array(
            'name' => self::COURSE,
            'onchange' => 'submit_form(`'.Page::FORM_ID.'`)',
            'autocomplete' => 'off'
        );
        $selector.= \html_writer::start_tag('select', $attr);

        return $selector;
    }

    private function get_selector_label() : string 
    {
        return get_string('course', 'coursework').' &nbsp;';
    }

    private function get_courses_options() : string 
    {
        $selector = '';

        foreach($this->d->get_courses() as $course)
        {
            $attr = array('value' => $course->id);

            if($this->d->get_selected_course_id() == $course->id)
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $selector.= \html_writer::start_tag('option', $attr);
            $selector.= $course->fullname;
            $selector.= \html_writer::end_tag('option');
        }

        return $selector;
    }

    private function get_selector_end() : string 
    {
        $selector = \html_writer::end_tag('select');

        if($this->d->get_selected_course_id() != mg::ALL_COURSES)
        {
            $selector.= ' '.$this->get_link_to_course();
        }
        
        $selector.= \html_writer::end_tag('p'); 
        return $selector;
    }

    private function get_link_to_course() : string 
    {
        $url = '/course/view.php?id='.$this->d->get_selected_course_id();
        $attr = array('href' => $url, 'target' => '_blank');
        $text = ' &nbsp;'.get_string('go_to_course', 'coursework');
        $text.= ' <i class="fa fa-external-link"></i>';
        return \html_writer::tag('a', $text, $attr);
    }

}