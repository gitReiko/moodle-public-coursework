<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

class Edit extends Action 
{
    private $defaultTask;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->defaultTask = $this->get_default_task();
    }

    protected function is_task_selected(int $taskId) : bool
    {
        if($taskId == $this->defaultTask->task) return true;
        else return false;
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::EDIT_DEFAULT_TASK
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DEFAULT_TASK_ROW_ID,
            'value' => $this->defaultTask->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_default_task() : \stdClass 
    {
        $id = optional_param(Main::DEFAULT_TASK_ROW_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing default task id.');

        global $DB;
        return $DB->get_record('coursework_default_task_use', array('id' => $id));   
    }

}


