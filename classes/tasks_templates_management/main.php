<?php

namespace Coursework\View\TasksTemplatesManagement;

require_once '../classes/lib/main_template.php';

require_once 'sections/action.php';
require_once 'sections/add.php';
require_once 'sections/database.php';
require_once 'sections/edit.php';
require_once 'sections/overview.php';

require_once 'tasks/action.php';
require_once 'tasks/add.php';
require_once 'tasks/database.php';
require_once 'tasks/edit.php';
require_once 'tasks/overview.php';

require_once 'lib.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const OVERVIEW = 'overview';
    const ADD_TASK = 'add_task';
    const EDIT_TASK = 'edit_task';
    const SECTIONS_MANAGEMENT = 'sections_management';
    const ADD_SECTION = 'add_section';
    const EDIT_SECTION = 'edit_section';
    const THEMES_MANAGEMENT = 'themes_management';
    const DEADLINE = 'deadline';
    const LIST_POSITION = 'list_position';
    const TASK_ID = 'task_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const TEMPLATE = 'template';
    const SECTION_ID = 'section_id';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);
    }

    protected function execute_database_handler() : void
    {
        if($this->is_database_event_exist())
        {
            $event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);

            switch($event)
            {
                case self::ADD_TASK: 
                case self::EDIT_TASK: 
                    $handler = new Tasks\Database($this->course, $this->cm);
                    $handler->execute();
                    break;

                case self::ADD_SECTION: 
                case self::EDIT_SECTION: 
                    $handler = new Sections\Database($this->course, $this->cm);
                    $handler->execute(); 
                    break;  
            }
        }
    }

    protected function redirect_to_prevent_page_update() : void
    {
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::SECTIONS_MANAGEMENT)
        {
            $this->redirect_to_sections_management_page();
        }
        else 
        {
            $this->redirect_to_overview_page();
        }
    }

    private function redirect_to_overview_page() : void 
    {
        $path = '/mod/coursework/pages/tasks_templates_management.php';
        $params = array(self::ID => $this->cm->id);
        redirect(new \moodle_url($path, $params));
    }

    private function redirect_to_sections_management_page() : void 
    {
        $params = array(
            self::ID => $this->cm->id,
            self::GUI_TYPE => self::SECTIONS_MANAGEMENT,
            self::TASK_ID => Lib::get_task_from_post()->id
        );
        $path = '/mod/coursework/pages/tasks_templates_management.php';
        redirect(new \moodle_url($path, $params));
    }

    protected function get_gui() : string 
    {
        $gui = '';
        $guiType = optional_param(self::GUI_TYPE, null, PARAM_TEXT);

        if($guiType === self::ADD_TASK)
        {
            $gui.= $this->get_add_task_gui();
        }
        else if($guiType === self::EDIT_TASK)
        {
            $gui.= $this->get_edit_task_gui();
        }
        else if($guiType === self::SECTIONS_MANAGEMENT)
        {
            $gui.= $this->get_sections_management_gui();
        }
        else if($guiType === self::ADD_SECTION)
        {
            $gui.= $this->get_add_section_gui();
        }
        else if($guiType === self::EDIT_SECTION)
        {
            $gui.= $this->get_edit_section_gui();
        }
        else
        {
            $gui.= $this->get_overview_gui();
        }

        return $gui;
    }

   
    private function get_overview_gui() : string 
    {
        $overview = new Tasks\Overview($this->course, $this->cm);
        return $overview->get_gui();
    }

    private function get_add_task_gui() : string 
    {
        $add = new Tasks\Add($this->course, $this->cm);
        return $add->get_gui();
    }

    private function get_edit_task_gui() : string 
    {
        $edit = new Tasks\Edit($this->course, $this->cm);
        return $edit->get_gui();
    }

    private function get_sections_management_gui() : string 
    {
        $sectionsOverview = new Sections\Overview($this->course, $this->cm);
        return $sectionsOverview->get_gui();
    }

    private function get_add_section_gui() : string 
    {
        $add = new Sections\Add($this->course, $this->cm);
        return $add->get_gui();
    }

    private function get_edit_section_gui() : string 
    {
        $edit = new Sections\Edit($this->course, $this->cm);
        return $edit->get_gui();
    }


}
