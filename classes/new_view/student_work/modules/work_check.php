<?php

use coursework_lib as lib;

class WorkCheck extends ViewModule 
{

    protected function get_module_name() : string
    {
        return 'workcheck';
    }

    protected function get_module_header() : string
    {
        return get_string('work_check', 'coursework');
    }

    protected function get_module_body() : string
    {
        $coursework = lib\get_coursework($this->cm->instance);
        return format_module_intro('coursework', $coursework, $this->cm->id);
    }

}

