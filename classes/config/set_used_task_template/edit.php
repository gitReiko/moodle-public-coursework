<?php

namespace Coursework\Config\SetUsedTaskTemplate;

class Edit extends Action 
{
    private $usedTask;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->usedTask = $this->get_used_task();
    }

    protected function is_task_selected(int $taskId) : bool
    {
        if($taskId == $this->usedTask->task) return true;
        else return false;
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::EDIT_TASK_USING
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::TASK_ROW_ID,
            'value' => $this->usedTask->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_used_task() : stdClass 
    {
        $id = optional_param(TASK.ROW.ID, null, PARAM_INT);
        if(empty($id)) throw new Exception('Missing using task id.');

        global $DB;
        return $DB->get_record('coursework_tasks_using', array('id' => $id));   
    }

}


