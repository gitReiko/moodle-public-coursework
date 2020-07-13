<?php

use coursework_lib as lib;

class Guidelines extends ViewModule 
{

    protected function get_module_name() : string
    {
        return 'guidelines';
    }

    protected function get_module_header() : string
    {
        return get_string('guidelines', 'coursework');
    }

    protected function get_module_body() : string
    {
        $coursework = lib\get_coursework($this->cm->instance);
        $guidelines = format_module_intro('coursework', $coursework, $this->cm->id);

        if(empty($guidelines))
        {
            return get_string('absent', 'coursework');
        }
        else 
        {
            return $guidelines;
        }
    }

}

