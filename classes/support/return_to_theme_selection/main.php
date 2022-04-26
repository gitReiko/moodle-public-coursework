<?php

namespace Coursework\Support\ReturnToThemeSelection;

require_once '../../classes/lib/main_template.php';
require_once '../../classes/lib/students_mass_actions.php';
require_once 'database.php';
require_once 'overview.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/support/return_to_theme_selection.php';

    const RETURN_TO_THEME_SELECTION = 'return_to_theme_selection';

    protected $cm;
    protected $course;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() 
    {
        $handler = new Database($this->course, $this->cm);
        return $handler->execute();
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/support/return_to_theme_selection.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
    {
        return $this->get_overview_gui();
    }

    private function get_overview_gui() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }


}

