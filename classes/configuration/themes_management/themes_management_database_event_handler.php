<?php

class ThemesManagementDatabaseEventHandler
{

    private $course;
    private $cm;

    public function execute(string $event) : void 
    {
        if($event === ADD.THEME) $this->add_theme();
        else if($event === UPDATE.THEME) $this->update_theme();
        else if($event === DEL.THEME) $this->delete_theme();
    }

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    private function add_theme() : void
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

    private function update_theme() : void
    {
        global $DB;

        $id = optional_param(THEME.ID, 0 , PARAM_INT);
        $name = optional_param(THEME.NAME, 0 , PARAM_TEXT);

        $theme = new stdClass;
        $theme->id = $id;
        $theme->name = $name;

        $DB->update_record('coursework_themes', $theme);
    }

    private function delete_theme() : void
    {
        $themeID = optional_param(THEME.ID, 0 , PARAM_INT);

        if($this->is_theme_already_used($themeID))
        {
            $this->replace_deleted_theme_with_own_theme_for_all_students($themeID);
        }

        global $DB;
        $DB->delete_records('coursework_themes', array('id'=>$themeID));
    }

    private function is_theme_already_used(int $themeID) : bool 
    {
        global $DB;

        if($DB->count_records('coursework_students', array('theme'=>$themeID))) return true;
        else return false;
    }

    private function replace_deleted_theme_with_own_theme_for_all_students(int $themeID) : void
    {
        global $DB;
        $students = $DB->get_records('coursework_students', array('theme'=>$themeID));
        $theme = $DB->get_record('coursework_themes', array('id'=>$themeID));

        foreach($students as $student)
        {
            $temp = new stdClass;
            $temp->id = $student->id;
            $temp->theme = null;
            $temp->owntheme = $theme->name;

            $DB->update_record('coursework_students', $temp);
        }
    }


}


