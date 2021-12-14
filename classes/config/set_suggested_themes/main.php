<?php

namespace Coursework\Config\SetSuggestedThemes;

require_once '../../classes/classes_lib/add_edit_template.php';
require_once 'add.php';
require_once 'database.php';
require_once 'overview.php';

class Main extends \Coursework\ClassesLib\AddEditTemplate
{
    const OVERVIEW = 'overview';
    const ADD_THEME_USING = 'add_theme_using';
    const DELETE_THEME_USING = 'delete_theme_using';

    const ID = 'id';
    const COURSE_ID = 'course_id';
    const THEMES_USING_ID = 'themes_using_id';

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
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_THEME_USING)
        {
            $gui.= $this->get_add_theme_using_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

    private function get_overview_gui() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_theme_using_gui() : string 
    {
        $add = new Add($this->course, $this->cm);
        return $add->get_gui();
    }

}
