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
        $this->teachers = $this->init_teachers();
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

    private function init_teachers()
    {
        global $PAGE;

        if(has_capability('mod/coursework:manage_global_old_files_area', $PAGE->cm->context))
        {
            return tg::get_coursework_teachers($this->cm->instance);
        }
        else 
        {
            return array($this->get_user());
        }
    }

    private function get_user() 
    {
        global $USER;
        return cg::get_user($USER->id);
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
