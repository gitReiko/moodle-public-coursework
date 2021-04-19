<?php 

namespace Coursework\View\ManageOldFilesArea;


use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\TeachersGetter as tg;

class Getter 
{

    private $cm;
    private $teachers;
    private $selectedTeacherId;

    function __construct(\stdClass $cm)
    {
        $this->cm = $cm;
        $this->teachers = tg::get_coursework_teachers($this->cm->instance);
        $this->selectedTeacherId = $this->init_selected_teacher_id();
    }

    public function get_cm() : \stdClass 
    {
        return $this->cm;
    }

    public function get_teachers() 
    {
        return $this->teachers;
    }

    public function get_selected_teacher_id() 
    {
        return $this->selectedTeacherId;
    }

    private function init_selected_teacher_id()
    {
        $teacherId = optional_param(Main::SELECTED_TEACHER_ID, null, PARAM_INT);

        if(empty($teacherId))
        {
            return reset($this->teachers)->id;
        }
        else 
        {
            return $teacherId;
        }
    }

    private function get_dashboard() : string 
    {
        $dashboard = new Dashboard (
            $this->cm, 
            $this->teachers, 
            $this->selectedTeacherId
        );
        return $dashboard->get();
    }

}
