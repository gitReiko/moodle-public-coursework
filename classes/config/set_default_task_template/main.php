<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

require_once '../../classes/lib/main_template.php';

require_once 'database.php';
require_once 'overview.php';
require_once 'set_default_task.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/config/set_default_task_template.php';

    const OVERVIEW = 'overview';
    const SET_DEFAULT_TASK = 'set_default_task';

    const ID = 'id';
    const DEFAULT_TASK_ID = 'default_task_id';
    const TASK = 'task';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() 
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/config/set_default_task_template.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::SET_DEFAULT_TASK)
        {
            $setDefaultTask = new SetDefaultTask($this->course, $this->cm);
            $gui.=  $setDefaultTask->get_gui();
        }
        else
        {
            $overview = new Overview($this->course, $this->cm);
            $gui.= $overview->get_gui();
        }

        return $gui;
    }

}
