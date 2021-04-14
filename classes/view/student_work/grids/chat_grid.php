<?php

namespace Coursework\View\StudentsWork\Grids;

use Coursework\Lib\Getters\CommonGetter as cg;

class ChatGrid extends BaseGrid
{
    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);
    }

    protected function get_grid_css_class_name() : string
    {
        return 'chatGrid yellowGrid';
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_chat_content';
    }

    protected function get_header_text() : string
    {
        return get_string('chat', 'coursework');
    }

    protected function get_grid_content() : string
    {
        $attr = array('class' => $this->hidingСlassName);

        $text = 'sgsdg';
        
        return \html_writer::tag('div', $text, $attr);
    }




}
