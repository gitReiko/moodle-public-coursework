<?php

namespace Coursework\View\StudentWork\Components\Task;

require 'tbody.php';

use Coursework\View\StudentWork\Components as c;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\StudentTaskGetter;

class Main extends c\Base 
{

    private $student;
    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->taskSections = $this->get_task_sections();
        $this->student = sg::get_student_with_his_work($this->cm->instance, $this->studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_task_content';
    }

    protected function get_header_text() : string
    {
        return get_string('task', 'coursework');
    }

    protected function get_content() : string
    {
        $attr = array('class' => 'workTask');
        $c = \html_writer::start_tag('table', $attr);
        $c.= $this->get_table_head();
        $c.= $this->get_table_body();
        $c.= \html_writer::end_tag('table');
        
        return $c;
    }

    private function get_task_sections() 
    {
        $getter = new StudentTaskGetter(
            $this->cm->instance,
            $this->studentId
        );
        return $getter->get_sections();
    }

    private function get_table_head() : string 
    {
        $head = \html_writer::start_tag('thead');
        $head.= \html_writer::start_tag('tr');

        $text = get_string('name', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('state', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('last_state_change', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $text = get_string('interaction', 'coursework');
        $head.= \html_writer::tag('td', $text);

        $head.= \html_writer::end_tag('tr');
        $head.= \html_writer::end_tag('thead');

        return $head;
    }

    private function get_table_body() : string 
    {
        $tbody = new Tbody($this->cm, $this->taskSections, $this->student);
        return $tbody->get();
    }



}
