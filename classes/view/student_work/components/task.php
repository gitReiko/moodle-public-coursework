<?php

namespace Coursework\View\StudentsWork\Components;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentTaskGetter;

class Task extends Base 
{

    private $taskSections;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        parent::__construct($course, $cm, $studentId);

        $this->taskSections = $this->get_task_sections();
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

        $head.= \html_writer::tag('td', '');

        $head.= \html_writer::end_tag('tr');
        $head.= \html_writer::end_tag('thead');

        return $head;
    }

    private function get_table_body() : string 
    {
        $body = \html_writer::start_tag('tbody');

        foreach($this->taskSections as $section)
        {
            $body.= \html_writer::start_tag('tr');

            $body.= $this->get_section_name_cell($section);
            $body.= $this->get_section_state_cell($section);
            $body.= $this->get_last_modify_date_cell($section);
            $body.= $this->get_action_cells($section);

            $body.= \html_writer::end_tag('tr');
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_section_name_cell(\stdClass $section) : string 
    {
        $text = $section->name;
        return \html_writer::tag('td', $text);
    }

    private function get_section_state_cell(\stdClass $section) : string 
    {
        $text = cg::get_state_name($section->status);
        return \html_writer::tag('td', $text);
    }

    private function get_last_modify_date_cell(\stdClass $section) : string 
    {
        if($this->is_date_exists($section->statusmodified))
        {
            $text = date('H:i d-m-Y', $section->statusmodified);
        }
        else
        {
            $text = '';
        }

        return \html_writer::tag('td', $text);
    }

    private function is_date_exists($date) : bool 
    {
        if(empty($date))
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    private function get_action_cells(\stdClass $section) : string
    {
        return \html_writer::tag('td', '');
    }




}
