<?php

namespace Coursework\View\StudentWork\TaskAssignment;

class AssignNewTask extends AssignCustomTask 
{

    protected function get_page_header() : string
    {
        $attr = array('style' => 'font-size: large');
        $text = get_string('creating_new_task', 'coursework');
        return \html_writer::tag('p', $text, $attr);
    }

    protected function get_description_value() : string
    {
        return '';
    }

    protected function get_tbody_sections() : string
    {
        return '';
    }



}