<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class CustomTaskAssignment 
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $this->get_student_id();
    }

    public function handle()
    {
        $taskId = $this->add_new_task();

        if(empty($taskId)) throw new \Exception('Task was not assigned to student (task creation error).');

        $this->add_task_sections($taskId);
        $this->assign_new_task_to_student($taskId);
    }

    private function add_new_task()
    {
        global $DB;
        $task = $this->get_task();
        return $DB->insert_record('coursework_tasks', $task, true);
    }

    private function get_student_id() : int 
    {
        $studentId = optional_param(MainDB::STUDENT_ID, null, PARAM_INT);
        if(empty($studentId)) throw new \Exception('Misssing student id');
        return $studentId;
    }

    private function get_task() : \stdClass 
    {
        $task = new \stdClass;
        $task->name = $this->get_task_name();
        $task->description = $this->get_task_description();
        $task->template = 0;
        return $task;
    }

    private function get_task_name() : string 
    {
        $name = get_string('task', 'coursework');
        $user = cg::get_user($this->studentId);
        $name.= ' '.$user->lastname.' '.$user->firstname;
        return $name;
    }

    private function get_task_description() 
    {
        return optional_param(MainDB::DESCRIPTION, '', PARAM_TEXT);
    }

    private function add_task_sections(int $taskId)
    {
        global $DB;
        $sections = $this->get_task_sections($taskId);
        
        foreach($sections as $section)
        {
            $sectionId = $DB->insert_record('coursework_tasks_sections', $section, true);

            if(empty($sectionId)) throw new \Exception('Section not created.');
        }
    }

    private function get_task_sections(int $taskId) : array 
    {
        $names = optional_param_array(MainDB::NAME, null, PARAM_TEXT);
        $dates = optional_param_array('completion_date', null, PARAM_TEXT);
        $datesSync = optional_param_array('sync_dates', null, PARAM_TEXT);

        $sections = array();
        $j = 0;
        for($i = 0; $i < count($names); $i++)
        {
            $section = new \stdClass;
            $section->name = $names[$i];
            $section->listposition = $i + 1;
            $section->task = $taskId;

            if($datesSync[$i] === '1')
            {
                $section->deadline = strtotime($dates[$j]);
                $j++;
            }

            $sections[] = $section;
        }

        return $sections;
    }

    private function assign_new_task_to_student(int $taskId)
    {
        global $DB;
        $work = sg::get_student_work($this->cm->instance, $this->studentId);
        $work->task = $taskId;

        if($DB->update_record('coursework_students', $work)) 
        {
            $this->add_student_task_receipt_status($work);
            $this->send_notification_to_student($work);
            $this->log_event();
        }
    }

    private function add_student_task_receipt_status(\stdClass $work)
    {
        $state = new \stdClass;
        $state->coursework = $work->coursework;
        $state->student = $work->student;
        $state->type = Enums::COURSEWORK;
        $state->instance = $work->coursework;
        $state->status = Enums::TASK_RECEIPT;
        $state->changetime = time();

        if(!$DB->insert_record('coursework_students_statuses', $state)) 
        {
            throw new \Exception('Coursework student task receipt state not created.');
        }
    }

    private function send_notification_to_student(\stdClass $work) : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $userFrom = cg::get_user($work->teacher); 
        $userTo = cg::get_user($work->student); 
        $messageName = 'taskassignment';
        $messageText = get_string('task_assignment_header','coursework');

        $notification = new Notification(
            $cm,
            $course,
            $userFrom,
            $userTo,
            $messageName,
            $messageText
        );

        $notification->send();
    }

    private function log_event() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentId, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\teacher_assign_new_task_to_student::create($params);
        $event->trigger();
    }
    

}
