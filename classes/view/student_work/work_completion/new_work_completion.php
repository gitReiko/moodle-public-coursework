<?php 

namespace Coursework\View\StudentWork;

use Coursework\View\StudentsWork\Grids as g;
use Coursework\Lib\Getters\CommonGetter as cg;

class NewWorkCompletion
{

    private $course;
    private $cm;
    private $studentId;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        $str = $this->get_page_header();

        $str.= $this->get_info_grids();


        return $str;
    }

    private function get_page_header() : string 
    {
        $text = cg::get_coursework_name($this->cm->instance);
        return \html_writer::tag('h2', $text);
    }

    private function get_info_grids() : string 
    {
        $info = new g\InfoGrid($this->course, $this->cm, $this->studentId);
        return $info->get_grid();
    }






}