<?php

class TaskUsingAdd extends TaskUsingAction 
{

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function is_task_selected(int $courseId) : bool
    {
        return false;
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        return '<input type="hidden" name="'.ConfigurationManager::DATABASE_EVENT.'" value="'.TasksUsingMain::ADD_TASK_USING.'"/>';
    }

}


