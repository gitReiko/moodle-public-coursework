<?php

use coursework_lib as lib;

class CustomTaskTemplateDatabaseHandler 
{
    protected $course;
    protected $cm;
    protected $studentId;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $this->get_student_id();
    }

    public function handle()
    {
        $taskId = $this->add_new_task();

        if(empty($taskId)) throw new Exception('Task was not assigned to student (task creation error).');

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
        $studentId = optional_param(STUDENT.ID, null, PARAM_INT);
        if(empty($studentId)) throw new Exception('Misssing student id');
        return $studentId;
    }

    private function get_task() : stdClass 
    {
        $task = new stdClass;
        $task->name = $this->get_task_name();
        $task->description = $this->get_task_description();
        $task->template = 0;
        return $task;
    }

    private function get_task_name() : string 
    {
        $name = get_string('task', 'coursework');
        $user = lib\get_user_from_id($this->studentId);
        $name.= ' '.$user->lastname.' '.$user->firstname;
        return $name;
    }

    private function get_task_description() 
    {
        return optional_param(DESCRIPTION, '', PARAM_TEXT);
    }

    private function add_task_sections(int $taskId)
    {
        global $DB;
        $sections = $this->get_task_sections($taskId);
        
        foreach($sections as $section)
        {
            $sectionId = $DB->insert_record('coursework_tasks_sections', $section, true);

            if(empty($sectionId)) throw new Exception('Section not created.');
        }
    }

    private function get_task_sections(int $taskId) : array 
    {
        $names = optional_param_array(NAME, null, PARAM_TEXT);
        $dates = optional_param_array('completion_date', null, PARAM_TEXT);
        $datesSync = optional_param_array('sync_dates', null, PARAM_TEXT);

        $sections = array();
        $j = 0;
        for($i = 0; $i < count($names); $i++)
        {
            $section = new stdClass;
            $section->name = $names[$i];
            $section->listposition = $i + 1;
            $section->task = $taskId;

            if($datesSync[$i] === '1')
            {
                $section->completiondate = strtotime($dates[$j]);
                $j++;
            }

            $sections[] = $section;
        }

        return $sections;
    }

    private function assign_new_task_to_student(int $taskId)
    {
        global $DB;
        $work = lib\get_student_work($this->cm, $this->studentId);
        $work->task = $taskId;
        $work->receivingtaskdate = time();

        if($DB->update_record('coursework_students', $work)) 
        {
            $this->send_notification_to_student($work);
        }
    }

    private function send_notification_to_student(stdClass $work) : void 
    {
        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'selecttheme';
        $userFrom = lib\get_user($work->teacher); 
        $userTo = lib\get_user($work->student); 
        $headerMessage = get_string('task_assignment_header','coursework');
        $fullMessageHtml = $this->get_student_html_message();

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);

    }

    private function get_student_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('task_assignment_header','coursework', $params).'.';
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }


}
