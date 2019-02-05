<?php

require_once 'themes_management_database_event_handler.php';

class ThemesManagement
{
    private $course;
    private $cm;

    private $courseworkCourses;

    public function display() : string
    {
        $str = $this->get_themes_management_header();
        $str.= $this->get_courses_html_list();
        return $str;
    }

    // Constructor functions
    function __construct($course, $cm)
    {
        // Init necessary for database processing params
        $this->course = $course;
        $this->cm = $cm;

        // Process database events
        $this->handle_database_events();

        // Init other params
        $this->courseworkCourses = $this->get_coursework_courses();
    }

    private function handle_database_events() : void
    {
        $event = optional_param(DB_EVENT, 0 , PARAM_TEXT);

        if(isset($event))
        {
            $handler = new ThemesManagementDatabaseEventHandler($this->course, $this->cm);
            $handler->execute($event);
        }
    }

    private function get_coursework_courses() : array 
    {
        $courses = cw_get_coursework_courses($this->cm->instance);

        foreach($courses as $course)
        {
            $course->themes = $this->get_course_themes($course->id);
        }
        return $courses;
    }

    private function get_course_themes(int $course) : array
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance,'course'=>$course);
        $themes = array();
        $themes = $DB->get_records('coursework_themes', $conditions, 'name', 'id, name');
        return $themes;
    }

    private function get_themes_management_header() : string
    {
        return '<h3>'.get_string('coursework_themes_management', 'coursework').'</h3>';
    }

    private function get_courses_html_list() : string
    {
        $str = '<ol class="theme_creation">';
        foreach($this->courseworkCourses as $course)
        {
            $str.= '<li><h4>'.$course->fullname.'</h4>';
            $str.= $this->get_courses_themes_html_list($course).'</li>';
        }
        $str.= '</ol>';

        return $str;
    }

    private function get_courses_themes_html_list(stdClass $course) : string
    {
        $str = '<ol>';

        foreach ($course->themes as $theme)
        {
            $str.= '<li>';
            $str.= $theme->name;
            $str.= $this->get_edit_theme_html_button($theme);
            $str.= $this->get_delete_theme_button($theme);
            $str.= '</li>';
        }

        $str.= $this->get_add_new_theme_button($course->id);
        $str.= '</ol>';

        return $str;
    }

    private function get_edit_theme_html_button(stdClass $theme) : string
    {
        return '<button onclick="edit_theme('.$this->cm->id.','.$theme->id.',`'.$theme->name.'`)">'.get_string('edit', 'coursework').'</button>';
    }

    private function get_delete_theme_button(stdClass $theme) : string
    {
        $str = '<form style="display:inline;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.DEL.THEME.'">';
        $str.= '<input type="hidden" name="'.THEME.ID.'" value="'.$theme->id.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_MANAGEMENT.'">';
        $str.= '<button>'.get_string('delete', 'coursework').'</button>';
        $str.= '</form>';
        return $str;
    }

    private function get_add_new_theme_button(int $course) : string
    {
        $str = '<li>';
        $str.= '<form>';
        $str.= '<input type="text" maxlength="255" name="'.THEME.NAME.'" style="width: 450px;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ADD.THEME.'">';
        $str.= '<input type="hidden" name="'.THEME.COURSE.'" value="'.$course.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_MANAGEMENT.'">';
        $str.= '<button>'.get_string('add_new_theme', 'coursework').'</button>';
        $str.= '</form>';
        $str.= '</li>';
        return $str;
    }


}


