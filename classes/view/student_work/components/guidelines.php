<?php

namespace Coursework\View\StudentsWork\Components;

use Coursework\Lib\Getters\CommonGetter as cg;

class Guidelines extends Base 
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_guidelines_content';
    }

    protected function get_header_text() : string
    {
        return get_string('guidelines', 'coursework');
    }

    protected function get_content() : string
    {
        $coursework = cg::get_coursework($this->cm->instance);
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
