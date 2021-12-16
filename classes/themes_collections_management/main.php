<?php

namespace Coursework\View\ThemesCollectionsManagement;

require_once '../classes/lib/main_template.php';
require_once 'collections/action.php';
require_once 'collections/add.php';
require_once 'collections/database.php';
require_once 'collections/edit.php';
require_once 'themes/database.php';
require_once 'themes/management.php';
require_once 'overview.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const OVERVIEW = 'overview';
    const ADD_COLLECTION = 'add_collection';
    const EDIT_COLLECTION = 'edit_collection';
    const THEMES_MANAGEMENT = 'themes_management';
    const ADD_THEME = 'add_theme';
    const EDIT_THEME = 'edit_theme';
    const DELETE_THEME = 'delete_theme';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() : void
    {
        $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

        switch($event)
        {
            case self::ADD_COLLECTION: 
            case self::EDIT_COLLECTION: 
                $handler = new Collections\Database($this->course, $this->cm);
                $handler->execute(); 
                break;

            case self::ADD_THEME: 
            case self::EDIT_THEME:
            case self::DELETE_THEME:   
                $handler = new Themes\Database($this->course, $this->cm);
                $handler->execute(); 
                break;  
        }
    }

    protected function redirect_to_prevent_page_update() : void
    {
        $path = '/mod/coursework/pages/themes_collections_management.php';
        $params = array('id'=>$this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_COLLECTION)
        {
            $gui.= $this->get_add_collection();
        }
        else if($guiType === self::EDIT_COLLECTION)
        {
            $gui.= $this->get_edit_collection();
        }
        else if($guiType === self::THEMES_MANAGEMENT)
        {
            $gui.= $this->get_themes_management();
        }
        else
        {
            $gui.= $this->get_overview();
        }

        return $gui;
    }

    private function get_overview() : string 
    {
        $overview = new Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_collection() : string 
    {
        $addCollection = new Collections\Add($this->course, $this->cm);
        return $addCollection->get_gui();
    }

    private function get_edit_collection() : string 
    {
        $editCollection = new Collections\Edit($this->course, $this->cm);
        return $editCollection->get_gui();
    }

    private function get_themes_management() : string 
    {
        $editCollection = new Themes\Management($this->course, $this->cm);
        return $editCollection->get_gui();
    }


}
