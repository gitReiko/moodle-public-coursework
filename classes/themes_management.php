<?php


class ThemesManagement
{
    private $course;
    private $cm;

    private $courses = array();

    // Constructor functions
    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->db_events_handler();

        $this->init_courses();

    }

    private function init_courses() : void
    {
        global $DB;

        $sql = 'SELECT DISTINCT ct.course, c.id, c.fullname
                FROM {coursework_tutors} AS ct, {course} AS c
                WHERE ct.course = c.id AND ct.coursework = ?
                ORDER BY c.fullname';
        $conditions = array($this->cm->instance);

        $courses = $DB->get_records_sql($sql, $conditions);

        foreach($courses as $course)
        {
            if(isset($course->course)) $this->courses[] = $course;
        }
    }

    // DB functions
    private function db_events_handler() : void
    {
        $event = optional_param(DB, 0 , PARAM_TEXT);

        if(isset($event))
        {
            if($event === ADD.THEME) $this->db_add_theme();
            else if($event === EDIT.THEME) $this->db_update_theme();
            else if($event === DEL.THEME) $this->db_delete_theme();
        }
    }

    private function db_add_theme() : void
    {
        global $DB;

        $name = optional_param(THEME.NAME, 0 , PARAM_TEXT);
        $course = optional_param(THEME.COURSE, 0 , PARAM_INT);

        $theme = new stdClass;
        $theme->name = $name;
        $theme->coursework = $this->cm->instance;
        $theme->course = $course;

        $DB->insert_record('coursework_themes', $theme, false);
    }

    private function db_update_theme() : void
    {
        global $DB;

        $id = optional_param(THEME.ID, 0 , PARAM_INT);
        $name = optional_param(THEME.NAME, 0 , PARAM_TEXT);

        $theme = new stdClass;
        $theme->id = $id;
        $theme->name = $name;

        $DB->update_record('coursework_themes', $theme);
    }

    private function db_delete_theme() : void
    {
        global $DB;
        $id = optional_param(THEME.ID, 0 , PARAM_INT);
        $DB->delete_records('coursework_themes', array('id'=>$id));
    }

    private function db_get_course_themes(int $course) : array
    {
        $themes = array();

        global $DB;
        $conditions = array('coursework'=>$this->cm->instance,'course'=>$course);
        $temp = $DB->get_records('coursework_themes', $conditions, 'name', 'id, name');

        foreach($temp as $value)
        {
            if(isset($value->name)) $themes[] = $value;
        }

        return $themes;
    }

    // GUI functions
    public function display() : string
    {
        $str = $this->gui_module_header();
        $str.= $this->gui_courses_list();


        return $str;
    }

    private function gui_module_header() : string
    {
        return '<h3>'.get_string('coursework_themes_management', 'coursework').'</h3>';
    }


    private function gui_courses_list() : string
    {
        $str = '<ol class="theme_creation">';
        foreach($this->courses as $course)
        {
            $str.= '<li><h4>'.$course->fullname.'</h4>';
            $str.= $this->gui_course_themes_list($course->id).'</li>';
        }
        $str.= '</ol>';

        return $str;
    }

    private function gui_course_themes_list(int $course) : string
    {
        $themes = $this->db_get_course_themes($course);

        $str = '<ol>';

        foreach ($themes as $theme)
        {
            $str.= '<li>';
            $str.= $theme->name;
            $str.= $this->gui_edit_theme($theme);
            $str.= $this->gui_delete_theme($theme);
            $str.= '</li>';
        }

        $str.= $this->gui_add_new_theme($course);
        $str.= '</ol>';

        return $str;
    }

    private function gui_delete_theme(stdClass $theme) : string
    {
        $str = '<form style="display:inline;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB.'" value="'.DEL.THEME.'">';
        $str.= '<input type="hidden" name="'.THEME.ID.'" value="'.$theme->id.'">';
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.THEMES_MANAGEMENT.'">';
        $str.= '<button>'.get_string('delete', 'coursework').'</button>';
        $str.= '</form>';
        return $str;
    }

    private function gui_edit_theme(stdClass $theme) : string
    {
        return '<button onclick="edit_theme('.$this->cm->id.','.$theme->id.',`'.$theme->name.'`)">'.get_string('edit', 'coursework').'</button>';
    }

    private function gui_add_new_theme(int $course) : string
    {
        $str = '<li>';
        $str.= '<form>';
        $str.= '<input type="text" maxlength="255" name="'.THEME.NAME.'" style="width: 450px;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB.'" value="'.ADD.THEME.'">';
        $str.= '<input type="hidden" name="'.THEME.COURSE.'" value="'.$course.'">';
        $str.= '<input type="hidden" name="'.ECM_MODULE.'" value="'.THEMES_MANAGEMENT.'">';
        $str.= '<button>'.get_string('add_new_theme', 'coursework').'</button>';
        $str.= '</form>';
        $str.= '</li>';
        return $str;
    }











}
