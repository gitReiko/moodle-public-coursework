<?php 

namespace Coursework\View\StudentWork;

use Coursework\View\StudentsWork\Locallib as locallib;
use Coursework\View\StudentsWork\Components as c;
use Coursework\View\StudentsWork\Components\Task as task;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\CommonLib as cl; 

class NewWorkCompletion
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
        $this->work = sg::get_students_work($this->cm->instance, $this->studentId);
    }

    public function get_page() : string 
    {
        $str = cg::get_page_header($this->cm);

        $str.= $this->get_info_block();
        $str.= $this->get_guidelines_block();
        $str.= $this->get_chat_block();
        $str.= $this->get_filemanager_block();

        if(cl::is_coursework_use_task($this->cm->instance))
        {
            $str.= $this->get_task_block();
        }

        if($this->is_work_check_neccessary()) 
        {
            $str.= $this->get_work_check_block();
        }

        return $str;
    }

    private function get_info_block() : string 
    {
        $info = new c\Info($this->course, $this->cm, $this->studentId);
        return $info->get_component();
    }

    private function get_guidelines_block() : string 
    {
        $guidelines = new c\Guidelines($this->course, $this->cm, $this->studentId);
        return $guidelines->get_component();
    }

    private function get_chat_block() : string 
    {
        $chat = new c\Chat($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }

    private function get_filemanager_block() : string 
    {
        $chat = new c\Filemanager($this->course, $this->cm, $this->studentId);
        return $chat->get_component();
    }

    private function get_task_block() : string 
    {
        $task = new task\Main($this->course, $this->cm, $this->studentId);
        return $task->get_component();
    }

    private function is_work_check_neccessary() : bool 
    {
        if(locallib::is_user_student($this->work)) 
        {
            if(locallib::is_state_not_ready_or_need_to_fix($this->work->status))
            {
                return true;
            }
            else 
            {
                return false;
            }
        }
        else if(locallib::is_user_teacher($this->work))
        {
            if(locallib::is_state_sent_for_check($this->work->status))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else 
        {
            return false;
        }
    }

    private function get_work_check_block() : string 
    {
        $workCheck = new c\WorkCheck($this->course, $this->cm, $this->studentId);
        return $workCheck->get_component();
    }


}
