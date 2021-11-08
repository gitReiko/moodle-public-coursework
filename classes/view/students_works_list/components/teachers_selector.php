<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWorksList\Page as p;
use Coursework\View\StudentsWorksList\MainGetter as mg;

class TeachersSelector 
{
    const TEACHER = 'teacher';

    private $d;
    private $selectedTeacher;

    function __construct(MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_teachers_selector() : string 
    {
        $selector = $this->get_selector_start();
        $selector.= $this->get_teachers_options();
        $selector.= $this->get_selector_end();

        return $selector;
    }

    private function get_selector_start() : string 
    {
        $attr = array('class' => 'selector');
        $selector = \html_writer::start_tag('p', $attr);
        $selector.= $this->get_selector_label();

        $attr = array(
            'name' => self::TEACHER,
            'onchange' => 'submit_form(`'.Page::FORM_ID.'`)',
            'autocomplete' => 'off'
        );
        $selector.= \html_writer::start_tag('select', $attr);

        return $selector;
    }

    private function get_selector_label() : string 
    {
        return get_string('leader', 'coursework').' &nbsp;';
    }

    private function get_teachers_options() : string 
    {
        $selector = '';

        foreach($this->d->get_teachers() as $teacher)
        {
            $attr = array('value' => $teacher->id);

            if($this->d->get_selected_teacher_id() == $teacher->id)
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
                $this->selectedTeacher = $teacher;
            }

            $selector.= \html_writer::start_tag('option', $attr);
            $selector.= $teacher->lastname.' '.$teacher->firstname;
            $selector.= \html_writer::end_tag('option');
        }

        return $selector;
    }

    private function get_selector_end() : string 
    {
        $selector = \html_writer::end_tag('select');

        if($this->d->get_selected_teacher_id() != mg::ALL_TEACHERS)
        {
            $selector.= ' '.$this->get_selected_teacher_email();
            $selector.= $this->get_selected_teacher_phones();
        }

        $selector.= \html_writer::end_tag('p'); 
        return $selector;
    }

    private function get_selected_teacher_email() : string 
    {
        $email = 'mailto:'.$this->selectedTeacher->email;
        $attr = array('href' => $email);
        $text = ' &nbsp;<i class="fa fa-envelope"></i> ';
        $text.= $this->selectedTeacher->email;
        return \html_writer::tag('a', $text, $attr);
    }

    private function get_selected_teacher_phones() : string 
    {
        $text = '';

        if(!empty($this->selectedTeacher->phone1))
        {
            $text.= ' <i class="fa fa-mobile"></i> ';
            $text.= $this->selectedTeacher->phone1;
        }

        if(!empty($this->selectedTeacher->phone2))
        {
            $text.= ' <i class="fa fa-mobile"></i> ';
            $text.= $this->selectedTeacher->phone2;
        }

        return $text;
    }

}