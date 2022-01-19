<?php 

namespace Coursework\Config\SetDefaultTaskTemplate;

use Coursework\Lib\Getters\CommonGetter as cg;

class Database 
{
    private $course;
    private $cm;

    private $coursework;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->coursework = cg::get_coursework($this->cm->instance);
        $this->coursework->defaulttask = $this->get_task();
    }

    public function execute() : void 
    {
        global $DB;
        if($DB->update_record('coursework', $this->coursework))
        {
            $this->log_default_task_template_setted();
        }
    }

    private function get_task() : string 
    {
        $task = optional_param(Main::TASK, null, PARAM_INT);
        if(empty($task)) throw new \Exception('Missing task template id.');
        return $task;
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

