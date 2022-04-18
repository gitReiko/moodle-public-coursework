<?php

namespace Coursework\Lib;

require_once 'getters/common_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentTaskGetter;
use Coursework\Lib\Enums;

// Students notification for teacher
class TeacherNotifications 
{
    public $isTeacherMustGiveTask;
    public $isTeacherHasUnreadedMessages;
    public $isTeacherNeedToCheckSection;
    public $isStudentWorkNotChecked;

    private $coursework;
    private $student;
    private $teacherId;

    function __construct(int $courseworkId, \stdClass $student, int $teacherId)
    {
        $this->coursework = cg::get_coursework($courseworkId);
        $this->student = $student;
        $this->teacherId = $teacherId;
        $this->student->sections = $this->get_student_sections();

        $this->isTeacherMustGiveTask = $this->is_teacher_must_give_task();
        $this->isTeacherHasUnreadedMessages = $this->is_teacher_has_unreaded_messages();
        $this->isTeacherNeedToCheckSection = $this->is_teacher_need_to_check_section();
        $this->isStudentWorkNotChecked = $this->is_student_work_not_checked();
    }

    public function is_notifications_exist() : bool 
    {
        if($this->isTeacherMustGiveTask)
        {
            return true;
        }
        else if($this->isTeacherHasUnreadedMessages)
        {
            return true;
        }
        else if($this->isTeacherNeedToCheckSection)
        {
            return true;
        }
        else if($this->isStudentWorkNotChecked)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    public function get_notifications()
    {
        $nfs = array();

        if($this->isTeacherMustGiveTask)
        {
            $nfs[] = get_string('teacher_must_give_task', 'coursework');
        }

        if($this->isTeacherHasUnreadedMessages)
        {
            $nfs[] = get_string('unreaded_messages', 'coursework');
        }

        if($this->isTeacherNeedToCheckSection)
        {
            $nfs[] = get_string('unchecked_section', 'coursework');
        }

        if($this->isStudentWorkNotChecked)
        {
            $nfs[] = get_string('unchecked_work', 'coursework');
        }

        return $nfs;
    }

    private function get_student_sections() 
    {
        if(!empty($this->student->coursework))
        {
            $getter = new StudentTaskGetter(
                $this->student->coursework,
                $this->student->id
            );
            return $getter->get_sections();
        }
    }

    private function is_teacher_must_give_task() : bool 
    {
        if($this->coursework->usetask == 1)
        {
            if($this->coursework->autotaskissuance == 0)
            {
                if(empty($this->student->theme))
                {
                    return false;
                }
                else if(empty($this->student->task))
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
        else 
        {
            return false;
        }
    }

    private function is_teacher_has_unreaded_messages() : bool 
    {
        global $DB;
        $conditions = array(
            'coursework' => $this->coursework->id, 
            'userto' => $this->student->teacher, 
            'userfrom' => $this->student->id,
            'readed' => 0
        );
        return $DB->record_exists('coursework_chat', $conditions);
    }

    private function is_teacher_need_to_check_section() : bool 
    {
        foreach($this->student->sections as $section)
        {
            if($section->latestStatus === Enums::SENT_FOR_CHECK)
            {
                return true;
            }
        }

        return false;
    }

    private function is_student_work_not_checked() : bool 
    {
        if($this->student->latestStatus == Enums::SENT_FOR_CHECK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }


}
