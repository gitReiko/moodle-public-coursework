<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Enums as enum;

class SectionsRows 
{
    private $student;
    private $moreClass;

    function __construct(\stdClass $student) 
    {
        $this->student = $student;

        $this->moreClass = Main::get_more_details_class($this->student->id);
    }

    public function get() : string 
    {
        $rows = '';

        if(!empty($this->student->sections))
        {
            foreach ($this->student->sections as $section) 
            {
                $rows.= $this->get_section_row($section);
            }
        }

        return $rows;
    }

    private function get_section_row(\stdClass $section) : string 
    {
        $attr = array('class' => $this->moreClass.' hidden');
        $row = \html_writer::start_tag('tr', $attr);
        $row.= Main::get_indent_from_blank_cells();
        $row.= $this->get_section_name_cell($section);
        $row.= $this->get_section_state_cell($section);
        $row.= \html_writer::end_tag('tr');

        return $row;
    }

    private function get_section_name_cell(\stdClass $section) : string 
    {
        $attr = array('colspan' => 3);
        $text = $section->name;
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_section_state_cell(\stdClass $section) : string 
    {
        $attr = array('colspan' => 2);
        $text = cg::get_state_name($section->status);
        return \html_writer::tag('td', $text, $attr);
    }



}
