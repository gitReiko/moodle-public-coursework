<?php 

namespace Coursework\View\TasksTemplatesManagement;

class Lib
{

    public static function get_task_from_post() : \stdClass 
    {
        $id = optional_param(Main::TASK_ID, null, PARAM_INT);
    
        if(empty($id)) throw new \Exception('Missing task template id.');
    
        global $DB;
        return $DB->get_record('coursework_tasks', array('id' => $id));
    }

}

