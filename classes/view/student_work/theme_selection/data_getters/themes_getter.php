<?php

namespace Coursework\View\StudentWork\ThemeSelection;

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\CommonGetter as cg;

class ThemesGetter 
{
    private $course;
    private $cm;
    private $themesCourses;
    private $students;
    private $selectedCourse;

    private $availableThemes;
    private $selectedThemes;

    function __construct(\stdClass $course, \stdClass $cm, array $themesCourses, $students, $selectedCourse)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->themesCourses = $themesCourses;
        $this->students = $students;
        $this->selectedCourse = $selectedCourse;

        $this->init_available_themes();
        $this->init_selected_themes();
    }

    public function get_available_themes()
    {
        return $this->availableThemes;
    }

    public function get_selected_themes()
    {
        return $this->selectedThemes;
    }

    private function init_available_themes() 
    {
        $themes = array();
        foreach($this->themesCourses as $course)
        {
            $collection = new \stdClass;
            $collection->course = $course->id;
            $samethemescount = locallib::get_count_of_same_themes($this->cm->instance, $course->id);
            $collection->themes = $this->get_course_available_themes($course->id, $samethemescount);

            $themes[] = $collection;
        }

        $this->availableThemes = $themes;
    }

    private function init_selected_themes()
    {
        $selectedThemes = array();

        foreach($this->availableThemes as $value)
        {
            if($value->course == $this->selectedCourse->id)
            {
                $this->selectedThemes = $value->themes;
            }
        }
    }

    private function get_course_available_themes(int $courseId, int $samethemescount) 
    {
        $collectionId = $this->get_course_collection_id($courseId);
        $themes = $this->get_course_collection_themes($collectionId);
        $themes = $this->filter_used_themes($themes, $samethemescount);
        return $themes;
    }

    private function get_course_collection_id(int $courseId)
    {
        global $DB;
        $sql = 'SELECT ctc.id
                FROM {coursework_themes_collections_use} AS ctcu 
                INNER JOIN {coursework_themes_collections} AS ctc 
                ON ctcu.collection = ctc.id 
                WHERE ctcu.coursework = ?
                AND ctc.course = ?';
        $where = array($this->cm->instance, $courseId);
        return $DB->get_field_sql($sql, $where);
    }

    private function get_course_collection_themes(int $collectionId) 
    {
        global $DB;
        $where = array('collection' => $collectionId);
        return $DB->get_records('coursework_themes', $where, 'content');
    }

    private function filter_used_themes(array $themes, int $availableCountOfUsages) : array
    {
        $students = $this->get_students_list_for_in_query();

        $filtered = array();

        foreach($themes as $theme)
        {
            $usagesCount = locallib::get_count_of_theme_usages(
                $this->cm->instance, 
                $theme->id, 
                $students
            );

            if(locallib::is_theme_not_used($usagesCount, $availableCountOfUsages))
            {
                $filtered[] = $theme;
            }
        }

        return $filtered;
    }

    private function get_students_list_for_in_query()
    {
        $inQuery = '';
        foreach($this->students as $student)
        {
            $inQuery.= $student->id.',';
        }
        $inQuery = mb_substr($inQuery, 0, -1);
        return $inQuery;
    }

}