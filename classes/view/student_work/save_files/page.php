<?php 

namespace Coursework\View\StudentWork\SaveFiles;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\View\StudentsWork\Locallib as locallib;

class Page 
{
    private $course;
    private $cm;
    private $studentId;

    private $work;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->work = sg::get_students_work($cm->instance, $studentId);
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);

        if(locallib::is_user_student($this->work))
        {
            $page.= 'student';
        }
        else if(locallib::is_user_teacher($this->work))
        {
            $page.= 'teacher';
        }
        else 
        {
            $page.= 'error';
        }

        return $page;
    }


}
