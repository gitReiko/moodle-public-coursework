<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

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

        foreach ($this->student->sections as $section) 
        {
            $rows.= $this->get_section_row($section);
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

        switch($section->status)
        {
            case enum::NOT_READY:
                $text = get_string('work_not_ready', 'coursework');
                break;
            case enum::READY:
                $text = get_string('work_ready', 'coursework');
                break;
            case enum::NEED_TO_FIX:
                $text = get_string('work_need_to_fix', 'coursework');
                break;
            case enum::SENT_TO_CHECK:
                $text = get_string('work_sent_to_check', 'coursework');
                break;
        }

        return \html_writer::tag('td', $text, $attr);
    }



}
