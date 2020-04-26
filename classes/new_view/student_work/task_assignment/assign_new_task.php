<?php

use coursework_lib as lib;
use view_lib as view;

class AssignNewTask extends AssignCustomTask 
{

    protected function get_page_header() : string
    {
        return '<h3>'.get_string('creating_new_task', 'coursework').'</h3>';
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