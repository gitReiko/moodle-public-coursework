<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\View\StudentsWorksList\CoursesSelector as cs;
use Coursework\View\StudentsWorksList\MainGetter as mg;
use Coursework\Lib\Getters\TeachersGetter as tg;

class CoursesGetter 
{
    private $course;
    private $cm;

    private $courses;
    private $selectedCourseId;
    private $selectedTeacherId;

    function __construct(\stdClass $course, \stdClass $cm, int $selectedTeacherId) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->selectedTeacherId = $selectedTeacherId;

        $this->init_courses();
        $this->init_selected_course_id();
    }

    public function get_courses() 
    {
        return $this->courses;
    }

    public function get_selected_course_id()
    {
        return $this->selectedCourseId;
    }

    public function add_student_courses($students)
    {
        $this->courses = $this->merge_courses(
            $this->courses,
            $this->get_courses_from_coursework_students($students)
        );
    }

    private function get_courses_from_coursework_students($students)
    {
        global $DB;
        $in = $this->get_coursework_students_in_clause($students);

        if($in)
        {
            $sql = "SELECT cs.id as cid, c.id, c.fullname 
                    FROM {coursework_students} AS cs
                    INNER JOIN {course} AS c
                    ON cs.course = c.id
                    WHERE cs.coursework = ?
                    AND cs.student IN($in)
                    ORDER BY c.fullname";
            $params = array($this->cm->instance);
            return $DB->get_records_sql($sql, $params);
        }
        else
        {
            return array();
        }
    }

    private function get_coursework_students_in_clause($students)
    {
        $in = '';

        foreach($students as $student)
        {
            $in.= $student->id.',';
        }

        $in = mb_substr($in,0,-1);

        return $in;
    }

    private function merge_courses($tCourses, $sCourses)
    {
        foreach($sCourses as $sCourse)
        {
            if($this->is_course_unique($tCourses, $sCourse))
            {
                $tCourses[] = $sCourse;
            }
        }

        usort($tCourses, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        return $tCourses;
    }

    private function is_course_unique($uniques, $course) : bool 
    {
        foreach($uniques as $unique)
        {
            if($unique->id == $course->id)
            {
                return false;
            }
        }

        return true;
    }

    private function init_courses()
    {
        if($this->selectedTeacherId == mg::ALL_COURSES)
        {
            $courses = $this->get_courses_from_coursework_teachers();
        }
        else 
        {
            $courses = $this->get_teacher_courses();
        }

        $courses = $this->add_all_courses_item_to_courses($courses);

        $this->courses = $courses;
    }

    private function get_courses_from_coursework_teachers()
    {
        $courses = $this->get_courses_from_coursework_teachers_from_database();
        $courses = $this->courses_unique_from_coursework_teachers($courses);

        return $courses;
    }

    private function get_courses_from_coursework_teachers_from_database()
    {
        global $DB;
        $sql = 'SELECT ct.id as cid, c.id, c.fullname 
                FROM {coursework_teachers} AS ct
                INNER JOIN {course} AS c
                ON ct.course = c.id 
                WHERE ct.coursework = ?
                ORDER BY c.fullname';
        $params = array($this->cm->instance);
        return $DB->get_records_sql($sql, $params);
    }

    private function courses_unique_from_coursework_teachers($courses)
    {
        $unique = array();

        foreach($courses as $course)
        {
            if($this->is_course_unique($unique, $course))
            {
                $unique[] = $course;
            }
        }

        return $unique;
    }

    private function get_teacher_courses()
    {
        return tg::get_teacher_courses(
            $this->cm->instance, 
            $this->selectedTeacherId
        );
    }

    private function add_all_courses_item_to_courses($courses)
    {
        $allCourses = array($this->get_all_courses_item());
        return array_merge($allCourses, $courses);
    }

    private function get_all_courses_item() : \stdClass 
    {
        $allCourses = new \stdClass;
        $allCourses->id = mg::ALL_COURSES;
        $allCourses->fullname = get_string('all_courses', 'coursework');
        $allCourses->shortname = get_string('all_courses', 'coursework');

        return $allCourses;
    }

    private function init_selected_course_id()
    {
        $course = optional_param(cs::COURSE, null, PARAM_INT);

        if(empty($course))
        {
            $this->selectedCourseId = reset($this->courses)->id;
        }
        else
        {
            $this->selectedCourseId = $course;
        }
    }


}
