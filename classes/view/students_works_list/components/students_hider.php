<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWorksList\Page as p;

class StudentsHider 
{
    const HIDER_ID = 'students_hider_id';
    const HIDE_STUDENTS = 'hide_students';

    private $d;

    function __construct(MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_students_without_theme_hider() : string 
    {
        $hider = \html_writer::start_tag('p');
        $hider.= $this->get_hider_checkbox();
        $hider.= ' '.get_string('hide_students_without_theme', 'coursework');
        $hider.= \html_writer::end_tag('p');

        return $hider;
    }

    private function get_hider_checkbox() : string 
    {
        $attr = array(
            'id' => self::HIDER_ID,
            'type' => 'checkbox',
            'name' => self::HIDE_STUDENTS,
            'value' => 1,
            'onchange' => 'submit_form(`'.Page::FORM_ID.'`)',
            'autocomplete' => 'off'
        );

        if($this->d->is_hide_students_without_theme())
        {
            $attr = array_merge($attr, array('checked' => 'checked'));
        }

        return \html_writer::empty_tag('input', $attr);
    }






}
