<?php

namespace Coursework\Support\ReturnToThemeSelection;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once '../../classes/classes_lib/students_mass_actions.php';
require_once 'database.php';
require_once 'overview.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
    const RETURN_TO_THEME_SELECTION = 'return_to_theme_selection';

    protected $cm;
    protected $course;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function handle_database_event() : void
    {
        if($this->is_database_event_exist())
        {
            $handler = new Database($this->course, $this->cm);
            $handler->execute();
        }
    }

    protected function get_gui() : string 
    {
        return $this->get_overview_gui();
    }

    private function get_overview_gui() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }


}

