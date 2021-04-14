<?php

namespace Coursework\View\StudentsWork\Grids;

abstract class BaseGrid 
{
    protected $course;
    protected $cm;
    protected $studentId;

    protected $gridCssClassName;
    protected $hidingСlassName;
    protected $headerText;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->gridCssClassName = $this->get_grid_css_class_name();
        $this->hidingСlassName = $this->get_hiding_class_name();
        $this->headerText = $this->get_header_text();
    }

    public function get_grid() : string 
    {
        $attr = array('class' => $this->gridCssClassName);
        $grids = \html_writer::start_tag('div', $attr);

        $grids.= $this->get_grid_header();
        $grids.= $this->get_grid_content();

        $grids.= \html_writer::end_tag('div');

        return $grids;
    }

    abstract protected function get_grid_css_class_name() : string;

    abstract protected function get_hiding_class_name() : string;

    abstract protected function get_header_text() : string;

    private function get_grid_header() : string 
    {
        $attr = array(
            'class' => 'header',
            'onclick' => 'open_close_by_class(`'.$this->hidingСlassName.'`)'
        );
        $text = $this->headerText.' (';
        $text.= get_string('click_to_show_hide', 'coursework').')';
        return \html_writer::tag('div', $text, $attr);
    }

    abstract protected function get_grid_content() : string;


}
