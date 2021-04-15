<?php

namespace Coursework\View\StudentsWork\Components;

abstract class Base 
{
    protected $course;
    protected $cm;
    protected $studentId;

    protected $hidingСlassName;
    protected $headerText;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->hidingСlassName = $this->get_hiding_class_name();
        $this->headerText = $this->get_header_text();
    }

    public function get_component() : string 
    {
        $content = $this->get_content();

        $component = $this->get_header();
        $component.= $this->put_content_in_hiding_block($content);

        return $component;
    }

    abstract protected function get_hiding_class_name() : string;

    abstract protected function get_header_text() : string;

    private function get_header() : string 
    {
        $attr = array(
            'class' => 'workComponentHeader',
            'onclick' => 'open_close_by_class(`'.$this->hidingСlassName.'`)'
        );
        $text = $this->headerText.' (';
        $text.= get_string('click_to_show_hide', 'coursework').')';
        return \html_writer::tag('p', $text, $attr);
    }

    abstract protected function get_content() : string;

    private function put_content_in_hiding_block($content) : string 
    {
        $attr = array('class' => $this->hidingСlassName.' workContainer');
        $hiding = \html_writer::start_tag('div', $attr);
        $hiding.= $this->get_content();
        $hiding.= \html_writer::end_tag('div');

        return $hiding;
    }


}
