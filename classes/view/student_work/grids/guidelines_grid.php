<?php

namespace Coursework\View\StudentsWork\Grids;

use Coursework\Lib\Getters\CommonGetter as cg;

class GuidelinesGrid extends BaseGrid
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);
    }

    protected function get_grid_css_class_name() : string
    {
        return 'guidelinesGrid greyGrid';
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_guidelines_content';
    }

    protected function get_header_text() : string
    {
        return get_string('guidelines', 'coursework');
    }

    protected function get_grid_content() : string
    {
        $coursework = cg::get_coursework($this->cm->instance);
        $guidelines = format_module_intro('coursework', $coursework, $this->cm->id);

        $attr = array('class' => $this->hidingСlassName);
        if(empty($guidelines))
        {
            $text = get_string('absent', 'coursework');
        }
        else 
        {
            $text = $guidelines;
        }
        
        return \html_writer::tag('div', $text, $attr);
    }




}
