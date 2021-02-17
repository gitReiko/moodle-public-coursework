<?php

namespace view\quota_overview;

require_once 'getter.php';

class Main 
{
    private $cm;
    private $d;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
        $this->d = new Getter($cm);
    }

    public function get_page() : string  
    {
        $p = '';
        $p.= $this->get_page_header();
        $p.= $this->get_overview_table();

        return $p;
    }

    private function get_page_header() : string  
    {
        $text = get_string('quota_overview', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_overview_table() : string 
    {
        $attr = array('class' => 'quota-overview');
        $table = \html_writer::start_tag('table', $attr);
        $table.= $this->get_overview_table_header();
        $table.= $this->get_overview_table_body();
        $table.= \html_writer::end_tag('table');

        return $table;
    }

    private function get_overview_table_header() : string  
    {
        $header = \html_writer::start_tag('thead');

        // tr
        $header.= \html_writer::start_tag('tr');

        $attr = array('rowspan' => 2);
        $text = get_string('teacher', 'coursework');
        $header.= \html_writer::tag('td', $text, $attr);

        $attr = array('rowspan' => 2);
        $text = get_string('course', 'coursework');
        $header.= \html_writer::tag('td', $text, $attr);

        $attr = array('colspan' => 3);
        $text = get_string('quota', 'coursework');
        $header.= \html_writer::tag('td', $text, $attr);

        $attr = array('colspan' => 2);
        $text = get_string('student', 'coursework');
        $header.= \html_writer::tag('td', $text, $attr);

        $header.= \html_writer::end_tag('tr');

        // new tr
        $header.= \html_writer::start_tag('tr');

        $text = get_string('planned', 'coursework');
        $header.= \html_writer::tag('td', $text);

        $text = get_string('used', 'coursework');
        $header.= \html_writer::tag('td', $text);

        $text = get_string('available', 'coursework');
        $header.= \html_writer::tag('td', $text);

        $text = get_string('fullname', 'coursework');
        $header.= \html_writer::tag('td', $text);
        
        $text = get_string('theme', 'coursework');
        $header.= \html_writer::tag('td', $text);

        $header.= \html_writer::end_tag('tr');

        $header.= \html_writer::end_tag('thead');

        return $header;
    } 

    private function get_overview_table_body() : string  
    {
        $body = \html_writer::start_tag('tbody');

        $body.= $this->get_students_count_row();
        $body.= $this->get_total_load_row();
        $body.= $this->get_divergence_row();

        foreach($this->d->get_teachers() as $teacher)
        {
            $body.= $this->get_teacher_rows($teacher);
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_students_count_row() : string  
    {
        $rowspan = 0;
        $class = 'tac';

        $row = \html_writer::start_tag('tr');

        $row.= \html_writer::tag('td', get_string('students_count', 'coursework'));
        $row.= \html_writer::tag('td', '');
        $row.= $this->get_row_cell($rowspan, $this->d->get_students_count(), $class);
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');

        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_total_load_row() : string  
    {
        $rowspan = 0;
        $class = 'tac';

        $row = \html_writer::start_tag('tr');

        $row.= \html_writer::tag('td', get_string('load', 'coursework'));
        $row.= \html_writer::tag('td', '');
        $row.= $this->get_row_cell($rowspan, $this->d->get_total_planned_quota(), $class);
        $row.= $this->get_row_cell($rowspan, $this->d->get_total_used_quota(), $class);
        $row.= $this->get_row_cell($rowspan, $this->d->get_total_available_quota(), $class);
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');

        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_divergence_row() : string  
    {
        $rowspan = 0;
        $class = 'tac';
        $divergence = $this->d->get_total_planned_quota() - $this->d->get_students_count();

        if($divergence > 0) $divergence = '+'.$divergence;

        $row = \html_writer::start_tag('tr');

        $row.= \html_writer::tag('td', get_string('divergence', 'coursework'));
        $row.= \html_writer::tag('td', '');
        $row.= $this->get_row_cell($rowspan, $divergence, $class);
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');
        $row.= \html_writer::tag('td', '');

        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_teacher_rows(\stdClass $teacher) : string 
    {
        $rowspan = count($teacher->students);

        $rows = \html_writer::start_tag('tr');

        $rows.= $this->get_row_cell($rowspan, $teacher->fullname);
        $rows.= $this->get_row_cell($rowspan, $teacher->coursename);
        $class = 'tac';
        $rows.= $this->get_row_cell($rowspan, $teacher->total_quota, $class);
        $rows.= $this->get_row_cell($rowspan, $teacher->used_quota, $class);
        $rows.= $this->get_row_cell($rowspan, $teacher->available_quota, $class);
        $rows.= $this->get_first_student_cells($teacher->students);

        $rows.= \html_writer::end_tag('tr');

        $rows.= $this->get_student_rows_from_second($teacher->students);

        return $rows;
    }

    private function get_row_cell($rowspan, $text, $class = '') : string 
    {
        if(empty($rowspan))
        {
            $attr = array('class' => $class);
            return \html_writer::tag('td', $text, $attr);
        }
        else 
        {
            $attr = array('rowspan' => $rowspan, 'class' => $class);
            return \html_writer::tag('td', $text, $attr);
        }
    }

    private function get_first_student_cells($students)
    {
        $cells = '';

        if(empty($students))
        {
            $cells.= \html_writer::tag('td', '');
            $cells.= \html_writer::tag('td', '');
        }
        else 
        {
            $cells = \html_writer::tag('td', reset($students)->fullname);
            $cells.= \html_writer::tag('td', reset($students)->themename);
        }

        return $cells;
    }

    private function get_student_rows_from_second($students) : string  
    {
        $rows = '';

        foreach(array_slice($students, 1) as $student)
        {

            $rows.= \html_writer::start_tag('tr');
            $this->get_row_cell($rowspan, $totalQuota, $class);
            $rows.= \html_writer::tag('td', $student->fullname);
            $rows.= \html_writer::tag('td', $student->themename);
            $rows.= \html_writer::end_tag('tr');
        }

        return $rows;
    }



}

