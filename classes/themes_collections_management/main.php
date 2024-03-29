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
    const MODULE_URL = '/mod/coursework/pages/themes_collections_management.php';

    const OVERVIEW = 'overview';
    const ADD_COLLECTION = 'add_collection';
    const EDIT_COLLECTION = 'edit_collection';
    const THEMES_MANAGEMENT = 'themes_management';
    const ADD_THEME = 'add_theme';
    const EDIT_THEME = 'edit_theme';
    const DELETE_THEME = 'delete_theme';

    const COLLECTION_ID = 'collection_id';
    const THEME_ID = 'theme_id';
    const NAME = 'name';
    const CONTENT = 'content';
    const COURSE = 'course';
    const DESCRIPTION = 'description';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() 
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

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/themes_collections_management.php';
    }

    protected function get_redirect_params() : array
    {
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::THEMES_MANAGEMENT)
        {
            return array(
                self::ID => $this->cm->id,
                self::GUI_TYPE => self::THEMES_MANAGEMENT,
                self::COLLECTION_ID => $this->get_collection_id()
            );
        }
        else 
        {
            return array(self::ID => $this->cm->id);
        }
    }

    private function get_collection_id() 
    {
        $id = optional_param(self::COLLECTION_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing collection id.');
        return $id;
    }

    protected function get_content() : string 
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
