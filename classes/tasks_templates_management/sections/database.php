<?php

namespace Coursework\View\TasksTemplatesManagement\Sections;

use Coursework\View\TasksTemplatesManagement\Main;

class Database 
{
    private $course;
    private $cm;

    private $section;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->section = $this->get_section();
    }

    public function execute() : void 
    {
        $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case Main::ADD_SECTION: 
                $this->add_task_section();
                break;
            case Main::EDIT_SECTION: 
                $this->update_task_section();
                break;
        }
    }

    private function get_section() : \stdClass 
    {
        $section = new \stdClass;

        $id = $this->get_section_id();
        if(!empty($id)) $section->id = $id;

        $section->name = $this->get_section_name();
        $section->description = $this->get_section_description();
        $section->listposition = $this->get_list_position();
        $section->task = $this->get_task_id();
        $section->deadline = $this->get_deadline();

        return $section;
    }

    private function get_section_id() 
    {
        return optional_param(Main::SECTION_ID, null, PARAM_INT);
    }

    private function get_section_name() : string 
    {
        $name = optional_param(Main::NAME, null, PARAM_TEXT);
        if(empty($name)) throw new \Exception('Missing task section name.');
        return $name;
    }

    private function get_section_description() 
    {
        return optional_param(Main::DESCRIPTION, '', PARAM_TEXT);
    }

    private function get_list_position() : int
    {
        return optional_param(Main::LIST_POSITION, 1, PARAM_INT);
    }

    private function get_task_id() : int 
    {
        $taskId = optional_param(Main::TASK_ID, null, PARAM_INT);
        if(empty($taskId)) throw new \Exception('Missing task id.');
        return $taskId;
    }

    private function get_deadline()
    {
        $date = optional_param(Main::DEADLINE, 0, PARAM_TEXT);
        if(!empty($date)) $date = strtotime($date);
        return $date;
    }

    private function add_task_section() : void 
    {
        global $DB;
        if($DB->insert_record('coursework_tasks_sections', $this->section, false))
        {
            $this->log_task_section_added();
        }
    }

    private function update_task_section() : void 
    {
        if(empty($this->section->id)) throw new \Exception('Missing task section id.');

        global $DB;
        if($DB->update_record('coursework_tasks_sections', $this->section))
        {
            $this->log_task_section_changed();
        }
    }

    private function log_task_section_added() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\task_section_added::create($params);
        $event->trigger();
    }

    private function log_task_section_changed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\task_section_changed::create($params);
        $event->trigger();
    }



}

