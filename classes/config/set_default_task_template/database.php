<?php 

namespace Coursework\Config\SetDefaultTaskTemplate;

class Database 
{
    private $course;
    private $cm;

    private $defaultTaskTemplate;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->defaultTaskTemplate = $this->get_default_task_template();
    }

    public function execute() : void 
    {
        $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case Main::ADD_DEFAULT_TASK: 
                $this->add_default_task();
                break;
            case Main::EDIT_DEFAULT_TASK: 
                $this->update_default_task();
                break;
        }
    }

    private function get_default_task_template() : \stdClass 
    {
        $default = new \stdClass;

        $id = $this->get_default_task_id();
        if(!empty($id)) $default->id = $id;

        $default->coursework = $this->get_coursework();
        $default->task = $this->get_task();

        return $default;
    }

    private function get_default_task_id() 
    {
        return optional_param(Main::DEFAULT_TASK_ROW_ID, null, PARAM_INT);
    }

    private function get_coursework() 
    {
        if(empty($this->cm->instance))
        {
            throw new \Exception('Missing coursework id.');
        }

        return $this->cm->instance;
    }

    private function get_task() : string 
    {
        $task = optional_param(Main::TASK, null, PARAM_INT);
        if(empty($task)) throw new \Exception('Missing task template id.');
        return $task;
    }

    private function add_default_task() : void 
    {
        global $DB;
        if($DB->insert_record('coursework_default_task_use', $this->defaultTaskTemplate, false))
        {
            $this->log_default_task_template_setted();
        }
    }

    private function update_default_task() : void 
    {
        if(empty($this->defaultTaskTemplate->id)) throw new \Exception('Missing default task template id.');

        global $DB;
        if($DB->update_record('coursework_default_task_use', $this->defaultTaskTemplate))
        {
            $this->log_default_task_template_setted();
        }
    }

    private function log_default_task_template_setted() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\default_task_template_setted::create($params);
        $event->trigger();
    }

}

