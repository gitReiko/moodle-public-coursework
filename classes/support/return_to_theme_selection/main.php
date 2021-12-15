<?php

namespace Coursework\Support\ReturnToThemeSelection;

//require_once 'database.php';
//require_once 'page.php';

class Main 
{
    const RETURN_TO_THEME_SELECTION = 'return_to_theme_selection';

    private $cm;
    private $course;

    function __construct(\stdClass $cm, \stdClass $course) 
    {
        $this->cm = $cm;
        $this->course = $course;
    }

    public function get_page() : string  
    {
        /*
        if($this->is_neccessary_back_to_work_state())
        {
            $this->change_state_to_work();
        }
        */

        //return $this->get_page_();

        echo 'dvsvsvsddvs';
    }

    private function is_neccessary_back_to_work_state() : bool
    {
        $back = optional_param(self::RETURN_TO_THEME_SELECTION, null, PARAM_TEXT);

        if($back) return true;
        else return false;
    }

    /*
    private function get_page_() : string 
    {
        $p = new Page($this->cm);
        return $p->get_page();
    }
    */

    /*
    private function change_state_to_work() : void 
    {
        $database = new Database($this->cm, $this->course);
        $database->change_state_to_work();
    }
    */


}

