<?php 

use coursework_lib as lib;

class TasksDBEventsHandler 
{
    private $course;
    private $cm;

    private $task;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->task = $this->get_task();
    }

    public function execute() : void 
    {
        $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case TasksManagement::ADD_TASK: 
                $this->add_task();
                break;
            case TasksManagement::EDIT_TASK: 
                $this->update_task();
                break;
        }
    }

    private function get_task() : stdClass 
    {
        $task = new stdClass;

        $id = $this->get_task_id();
        if(!empty($id)) $task->id = $id;

        $task->name = $this->get_task_name();
        $task->description = $this->get_task_description();
        $task->template = $this->get_task_template();

        return $task;
    }

    private function get_task_id() 
    {
        return optional_param(TASK.ID, null, PARAM_INT);
    }

    private function get_task_name() : string 
    {
        $name = optional_param(NAME, null, PARAM_TEXT);
        if(empty($name)) throw new Exception('Missing task template name.');
        return $name;
    }

    private function get_task_description() 
    {
        return optional_param(DESCRIPTION, '', PARAM_TEXT);
    }

    private function get_task_template() : int 
    {
        return optional_param(TEMPLATE, 0, PARAM_INT);
    }

    private function add_task() : void 
    {
        global $DB;
        $DB->insert_record('coursework_tasks', $this->task, false);
    }

    private function update_task() : void 
    {
        if(empty($this->task->id)) throw new Exception('Missing task id.');

        global $DB;
        $DB->update_record('coursework_tasks', $this->task);
    }



}

