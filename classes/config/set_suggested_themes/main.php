<?php

namespace Coursework\Config\SetSuggestedThemes;

require_once '../../classes/lib/main_template.php';
require_once 'action.php';
require_once 'add.php';
require_once 'database.php';
require_once 'edit.php';
require_once 'lib.php';
require_once 'overview.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const OVERVIEW = 'overview';
    const ADD_THEME_USING = 'add_theme_using';
    const CHANGE_USING_THEMES = 'change_using_themes';
    const DELETE_THEME_USING = 'delete_theme_using';

    const ID = 'id';
    const COURSE_ID = 'course_id';
    const COLLECTION_ID = 'collection_id';
    const THEMES_USING_ID = 'themes_using_id';
    const COUNT_OF_SAME_THEMES = 'count_of_same_themes';

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
        return '/mod/coursework/pages/config/set_suggested_themes.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_THEME_USING)
        {
            $gui.= $this->get_add_theme_using_gui();
        }
        else if($guiType === self::CHANGE_USING_THEMES)
        {
            $gui.= $this->get_change_theme_using_gui();
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

    private function get_change_theme_using_gui() : string 
    {
        $change = new Edit($this->course, $this->cm);
        return $change->get_gui();
    }

}
