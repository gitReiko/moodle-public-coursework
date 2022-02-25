<?php

namespace Coursework\Lib;

require_once 'getters/common_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;
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

    private function is_teacher_must_give_task() : bool 
    {
        if($this->coursework->usetask == 1)
        {
            if($this->coursework->autotaskissuance == 0)
            {
                if(empty($this->student->task))
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
            'userto' => $this->teacherId, 
            'userfrom' => $this->student->id,
            'readed' => 0
        );
        return $DB->record_exists('coursework_chat', $conditions);
    }

    private function is_teacher_need_to_check_section() : bool 
    {
        if($this->get_count_of_unchecked_sections())
        {
            return true;
        }
        else 
        {
            return false;
        }
    }
    
    private function get_count_of_unchecked_sections()
    {
        global $DB;
        $sql = 'SELECT COUNT(cts.id)  
                FROM {coursework_tasks_sections} AS cts 
                INNER JOIN {coursework_students_statuses} AS css
                ON cts.id = css.instance 
                WHERE css.coursework = ?
                AND css.student = ? 
                AND css.type = ?
                AND css.status = ? 
                ORDER BY cts.listposition';
        $params = array(
            $this->coursework->id, 
            $this->student->id,
            Enums::SECTION,
            Enums::SENT_FOR_CHECK
        );
        return $DB->count_records_sql($sql, $params);
    }

    private function is_student_work_not_checked() : bool 
    {
        if($this->student->status == Enums::SENT_FOR_CHECK)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }


}
