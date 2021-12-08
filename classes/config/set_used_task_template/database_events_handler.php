<?php 

use coursework_lib as lib;

class TaskUsingDBEventsHandler 
{
    private $course;
    private $cm;

    private $usingTaskTemplate;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->usingTaskTemplate = $this->get_using_task_template();
    }

    public function execute() : void 
    {
        $event = optional_param(ConfigurationManager::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case TasksUsingMain::ADD_TASK_USING: 
                $this->add_task_using();
                break;
            case TasksUsingMain::EDIT_TASK_USING: 
                $this->update_task_using();
                break;
        }
    }

    private function get_using_task_template() : stdClass 
    {
        $using = new stdClass;

        $id = $this->get_using_task_id();
        if(!empty($id)) $using->id = $id;

        $using->coursework = $this->get_coursework();
        $using->task = $this->get_task();

        return $using;
    }

    private function get_using_task_id() 
    {
        return optional_param(TASK.ROW.ID, null, PARAM_INT);
    }

    private function get_coursework() 
    {
        if(empty($this->cm->instance))
        {
            throw new Exception('Missing coursework id.');
        }

        return $this->cm->instance;
    }

    private function get_task() : string 
    {
        $task = optional_param(TASK, null, PARAM_INT);
        if(empty($task)) throw new Exception('Missing task template id.');
        return $task;
    }

    private function add_task_using() : void 
    {
        global $DB;
        $DB->insert_record('coursework_tasks_using', $this->usingTaskTemplate, false);
    }

    private function update_task_using() : void 
    {
        if(empty($this->usingTaskTemplate->id)) throw new Exception('Missing using task template id.');

        global $DB;
        $DB->update_record('coursework_tasks_using', $this->usingTaskTemplate);
    }



}

