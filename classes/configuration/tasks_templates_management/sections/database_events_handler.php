<?php 

use coursework_lib as lib;

class TasksSectionsDBEventsHandler 
{
    private $course;
    private $cm;

    private $section;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->section = $this->get_section();
    }

    public function execute() : void 
    {
        $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case TasksManagement::ADD_SECTION: 
                $this->add_task_section();
                break;
            case TasksManagement::EDIT_SECTION: 
                echo 'edit';
                //$this->update_task_section();
                break;
        }
    }

    private function get_section() : stdClass 
    {
        $task = new stdClass;

        //$id = $this->get_task_id();
        //if(!empty($id)) $task->id = $id;

        $task->name = $this->get_task_name();
        $task->description = $this->get_task_description();
        $task->listposition = $this->get_list_position();
        $task->task = $this->get_task_id();
        $task->completiondate = $this->get_completion_date();

        return $task;
    }

    /*
    private function get_task_id() 
    {
        return optional_param(TASK.ID, null, PARAM_INT);
    }
    */

    private function get_task_name() : string 
    {
        $name = optional_param(NAME, null, PARAM_TEXT);
        if(empty($name)) throw new Exception('Missing task section name.');
        return $name;
    }

    private function get_task_description() 
    {
        return optional_param(DESCRIPTION, '', PARAM_TEXT);
    }

    private function get_list_position() : int
    {
        return optional_param(TasksManagement::LIST_POSITION, 1, PARAM_INT);
    }

    private function get_task_id() : int 
    {
        $taskId = optional_param(TASK.ID, null, PARAM_INT);
        if(empty($taskId)) throw new Exception('Missing task id.');
        return $taskId;
    }

    private function get_completion_date()
    {
        $date = optional_param(TasksManagement::COMPLETION_DATE, 0, PARAM_TEXT);
        if(!empty($date)) $date = strtotime($date);
        return $date;
    }

    private function add_task_section() : void 
    {
        global $DB;
        $DB->insert_record('coursework_tasks_sections', $this->section, false);
    }

    private function update_task_section() : void 
    {
        if(empty($this->section->id)) throw new Exception('Missing task section id.');

        global $DB;
        $DB->update_record('coursework_tasks_sections', $this->section);
    }



}

