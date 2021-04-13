<?php

namespace view\back_to_work_state;

require_once 'database.php';
require_once 'page.php';

class Main 
{
    const BACK_TO_WORK_STATE = 'back_to_work_state';
    const STUDENT_ID = 'student_id';
    const COURSEWORK_ID = 'coursework_id';
 
    private $cm;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
    }

    public function get_page() : string  
    {
        if($this->is_neccessary_back_to_work_state())
        {
            $this->change_state_to_work();
        }

        return $this->get_page_();
    }

    private function is_neccessary_back_to_work_state() : bool
    {
        $back = optional_param(self::BACK_TO_WORK_STATE, null, PARAM_TEXT);

        if($back) return true;
        else return false;
    }

    private function get_page_() : string 
    {
        $p = new Page($this->cm);
        return $p->get_page();
    }

    private function change_state_to_work() : void 
    {
        $database = new Database($this->cm);
        $database->change_state_to_work();
    }





}

