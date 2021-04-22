<?php

use Coursework\View\StudentsWork\Locallib as locallib;

class ThemesGetter 
{
    private $course;
    private $cm;
    private $themesCourses;

    private $availableThemes;

    function __construct(stdClass $course, stdClass $cm, array $themesCourses)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->themesCourses = $themesCourses;

        $this->init_themes();
    }

    public function get_available_themes()
    {
        return $this->availableThemes;
    }

    private function init_themes() 
    {
        $themes = array();
        foreach($this->themesCourses as $course)
        {
            $collection = new stdClass;
            $collection->course = $course->id;
            $collection->themes = $this->get_course_available_themes($course->id);

            $themes[] = $collection;
        }

        $this->availableThemes = $themes;
    }

    private function get_course_available_themes(int $courseId) 
    {
        $collectionId = $this->get_course_collection_id($courseId);
        $themes = $this->get_course_collection_themes($collectionId);
        $themes = $this->filter_used_themes($themes);
        return $themes;
    }

    private function get_course_collection_id(int $courseId)
    {
        global $DB;
        $sql = 'SELECT ctc.id
                FROM {coursework_used_collections} AS cuc 
                INNER JOIN {coursework_theme_collections} AS ctc 
                ON cuc.collection = ctc.id 
                WHERE cuc.coursework = ?
                AND ctc.course = ?';
        $where = array($this->cm->instance, $courseId);
        return $DB->get_field_sql($sql, $where);
    }

    private function get_course_collection_themes(int $collectionId) 
    {
        global $DB;
        $where = array('collection' => $collectionId);
        return $DB->get_records('coursework_themes', $where, 'name');
    }

    private function filter_used_themes(array $allThemes) : array
    {
        $students = locallib::get_students_list_for_in_query($this->cm);

        $themes = array();
        foreach($allThemes as $theme)
        {
            if($this->is_theme_not_used($theme->id, $students))
            {
                $themes[] = $theme;
            }
        }
        return $themes;
    }

    private function is_theme_not_used($themeId, $students) : bool 
    {
        global $DB;
        $sql = "SELECT id
                FROM {coursework_students}
                WHERE coursework = ?
                AND theme = ?
                AND student IN ($students)";

        $where = array($this->cm->instance, $themeId);
        return !$DB->record_exists_sql($sql, $where);
    }

}