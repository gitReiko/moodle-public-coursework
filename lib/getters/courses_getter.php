<?php

namespace Coursework\Lib\Getters;

class CoursesGetter
{

    public static function get_all_site_courses() : array
    {
        global $DB;
        $where = array();
        return $DB->get_records('course', $where, 'fullname', 'id,fullname');
    }

    public static function get_coursework_teachers_courses(int $courseworkId)
    {
        $courses = self::get_coursework_teachers_courses_id($courseworkId);
        $courses = self::add_fullnames_to_courses($courses);
        return $courses;
    }

    public static function get_coursework_teacher_courses(int $courseworkId, int $teacherId)
    {
        $courses = self::get_coursework_teacher_courses_id($courseworkId, $teacherId);
        $courses = self::add_fullnames_to_courses($courses);
        return $courses;
    }

    public static function get_course(int $courseId) : \stdClass 
    {
        global $DB;
        $where = array('id' => $courseId);
        $course = $DB->get_record('course', $where, 'id,fullname,shortname');

        if($course)
        {
            return $course;
        }
        else 
        {
            $deleted = new \stdClass;
            $deleted->id = $courseId;
            $deleted->fullname = get_string('deleted_course_num', 'coursework', $courseId);
            $deleted->shortname = get_string('deleted_course_num', 'coursework', $courseId);

            return $deleted;
        }
    }

    public static function get_course_fullname(int $courseId)
    {
        global $DB;

        $fullname = $DB->get_field('course', 'fullname', array('id' => $courseId));

        if($fullname)
        {
            return $fullname;
        }
        else 
        {
            return get_string('deleted_course_num', 'coursework', $courseId);
        }
    }

    private static function get_coursework_teachers_courses_id(int $courseworkId)
    {
        global $DB;
        $where = array('coursework' => $courseworkId);
        $sort = '';
        $fields = 'course as id';
        return $DB->get_records('coursework_teachers', $where, $sort, $fields);
    }

    private static function get_coursework_teacher_courses_id(int $courseworkId, int $teacherId)
    {
        global $DB;
        $where = array(
            'coursework' => $courseworkId,
            'teacher' => $teacherId
        );
        $sort = '';
        $fields = 'course as id';
        return $DB->get_records('coursework_teachers', $where, $sort, $fields);
    }

    private static function add_fullnames_to_courses($courses)
    {
        foreach($courses as $course)
        {
            $course->fullname = self::get_course_fullname($course->id);
        }

        usort($courses, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        return $courses;
    } 


}
