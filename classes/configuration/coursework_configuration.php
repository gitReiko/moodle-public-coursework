<?php

require_once 'classes/configuration/students_mass_actions_gui_templates.php';
require_once 'classes/configuration/configuration_manager.php';
require_once 'classes/configuration/themes_collections_management/collections_management.php';
require_once 'classes/configuration/leader_change/leader_change.php';
require_once 'classes/configuration/leaders_setting/leaders_setting.php';
require_once 'classes/configuration/students_distribution/students_distribution.php';
require_once 'classes/configuration/remove_distribution/remove_distribution.php';

/**
 * Coursework configuration starts from this class. 
 * 
 * Checks user rights for coursework configuration. 
 * Returns coursework configuration gui.
 * Generates interface for switching between configuration modules.
 * 
 * @param stdClass $course - record of course Moodle database table
 * @param stdClass $cm - record of course_modules Moodle database table
 * @param string $module - type of coursework configuration module. Modules types are described in the enums.php file
 * @return string - gui of coursework configuration
 * @author Denis Makouski (Reiko)
 */
class CourseworkConfiguration
{
    private $course;
    private $cm;
    private $module;

    public function display() : void
    {
        $str = '';
        if($this->is_user_has_right_configurate_coursework())
        {
            $str.= $this->get_coursework_configuration_header();
            $str.= $this->get_begin_of_frame_switching_modules();
            $str.= $this->get_configuration_module();
            $str.= $this->get_end_of_frame_switching_modules();
        }
        else
        {
            $str.= $this->get_no_access_message();
        }
        echo $str;
    }

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->module = $this->get_coursework_configuration_module();
    }

    private function get_coursework_configuration_module() : string
    {
        $module = optional_param(CONFIG_MODULE, 0 , PARAM_TEXT);

        if($module) return $module;
        else return LEADERS_SETTING;
    }

    private function is_user_has_right_configurate_coursework() : bool 
    {
        global $PAGE;
        if(has_capability('mod/coursework:enrollmembers', $PAGE->cm->context)) return true;
        else return false;
    }

    private function get_coursework_configuration_header() : string
    {
        return '<h2>'.get_string('coursework_configuration', 'coursework').'</h2>';
    }

    private function get_begin_of_frame_switching_modules() : string
    {
        $str = '<div class="coursework-configuration">';
        $str.= $this->get_panel_switching_modules();
        $str.= $this->get_html_forms_for_switching_modules_panel();
        $str.= '<div style="padding: 10px;">';
        return $str;
    }

    private function get_panel_switching_modules() : string 
    {
        $str = '<ol class="coursework-configuration">';
        foreach(CONFIG_MODULES as $module)
        {
            if($this->module === $module) $str.= '<li class="selected">';
            else $str.= '<li onclick="change_bookmark(`'.$module.'`)">';
            $str.= get_string($module, 'coursework').'</li>';
        }
        $str.= '</ol>';
        return $str;
    }

    private function get_html_forms_for_switching_modules_panel() : string
    {
        $str = '';
        foreach(CONFIG_MODULES as $module)
        {
            $str.= '<form id="'.$module.'">';
            $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.$module.'"/>';
            $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'"/>';
            $str.= '</form>'; 
        }
        return $str;
    }

    private function get_configuration_module() : string 
    {
        $str = '';
        if($this->module === LEADERS_SETTING)
        {
            $leadersSetting = new LeadersSetting($this->course, $this->cm);
            $str .= $leadersSetting->execute();
        }
        else if($this->module === STUDENTS_DISTRIBUTION)
        {
            $studentsDistribution = new StudentsDistribution($this->course, $this->cm);
            $str .= $studentsDistribution->execute();
        }
        else if($this->module === REMOVE_DISTRIBUTION)
        {
            $removeDistribution = new RemoveDistribution($this->course, $this->cm);
            $str .= $removeDistribution->execute();
        }
        else if($this->module === LEADER_CHANGE)
        {
            $removeDistribution = new LeaderChange($this->course, $this->cm);
            $str .= $removeDistribution->execute();
        }
        else if($this->module === THEMES_COLLECTIONS_MANAGEMENT)
        {
            $removeDistribution = new CollectionsManagement($this->course, $this->cm);
            $str .= $removeDistribution->execute();
        }  
        return $str;
    }

    private function get_end_of_frame_switching_modules() : string { return '</div></div>'; }

    private function get_no_access_message() : string
    {
        return '<h2 class="darkred">'.get_string('no_permission', 'coursework').'</h2>';
    }

}
