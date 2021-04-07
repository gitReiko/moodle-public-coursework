<?php

namespace Coursework\Lib\Getters;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Enums as enum;

class TeachersGetter
{

    public static function get_only_configured_course_work_teachers(int $courseworkId)
    {
        global $DB;
        $sql = 'SELECT ct.id as courseworkteacherid, ct.teacher as teacherid, 
                    u.firstname, u.lastname, u.email, u.phone1, u.phone2, 
                    ct.course as courseid, c.fullname as coursename, ct.quota
                FROM {coursework_teachers} as ct, {user} as u, {course} as c
                WHERE ct.teacher = u.id AND ct.course = c.id AND coursework = ?
                ORDER BY u.lastname, u.firstname ';
        $conditions = array($courseworkId);

        return $DB->get_records_sql($sql, $conditions);
    }

    public static function get_all_course_work_teachers(int $courseworkId)
    {
        $teachers = self::get_only_configured_course_work_teachers($courseworkId);

        if(empty($teachers))
        {
            $teachers = array();
        }

        $studentsWorks = sg::get_students_works($courseworkId);
        foreach($studentsWorks as $work)
        {
            if(self::is_teacher_not_exist_in_array($work->teacher, $teachers))
            {
                $teachers = self::add_teacher_to_array($courseworkId, $work->teacher, $teachers);
            }
        }

        $teachers = self::sort_teachers_array($teachers);

        return $teachers;
    }

    public static function get_teacher_from_id(int $courseworkId, int $userId) 
    {
        global $DB;
        $sql = 'SELECT u.id as teacherid, u.firstname, u.lastname, u.email,  u.phone1, 
                    u.phone2, cs.course as courseid, c.fullname as coursename
                FROM {coursework_students} as cs, {user} as u, {course} as c
                WHERE cs.teacher = u.id AND cs.course = c.id 
                AND coursework = ? AND u.id = ? ';     
        $conditions = array($courseworkId, $userId);

        $teacher = $DB->get_record_sql($sql, $conditions);
        $teacher->quota = 0;

        return $teacher;
    }

    public static function get_teacher_courses(int $courseworkId, int $teacherId)
    {
        $courses = self::get_configured_teacher_courses($courseworkId, $teacherId);

        if(empty($courses))
        {
            $courses = array();
        }

        $courses = self::get_teacher_courses_from_students_works($courseworkId, $teacherId, $courses);

        $courses = self::sort_courses_array($courses);

        return $courses;
    }

    public static function is_this_course_is_teacher_course(int $courseworkId, int $teacherId, int $courseId) : bool 
    {
        if(self::is_this_course_is_configured_teacher_course($courseworkId, $teacherId, $courseId))
        {
            return true;
        }
        else if(self::is_this_course_exist_in_teacher_students_works($courseworkId, $teacherId, $courseId))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private static function is_teacher_not_exist_in_array(int $teacherId, $teachers) : bool
    {
        foreach($teachers as $teacher)
        {
            if($teacher->teacherid == $teacherId)
            {
                return false;
            }
        }

        return true;
    }

    private static function add_teacher_to_array(int $courseworkId, int $userId, $teachers)
    {
        $teacher = self::get_teacher_from_id($courseworkId, $userId);
        return array_merge($teachers, array($teacher));
    }

    private static function sort_teachers_array($teachers)
    {
        if(count($teachers) > 1)
        {
            usort($teachers, function($a, $b)
            {
                return strcmp(
                    $a->lastname.$a->firstname, 
                    $b->lastname.$b->firstname);
            });
        }

        return $teachers;
    }

    private static function get_configured_teacher_courses(int $courseworkId, int $teacherId)
    {
        global $DB;
        $sql = 'SELECT c.id, c.fullname, c.shortname 
                FROM {coursework_teachers} AS ct 
                INNER JOIN {course} AS c
                ON ct.course = c.id 
                WHERE ct.coursework = ? AND ct.teacher = ? ';
        $params = array($courseworkId, $teacherId);
        return $DB->get_records_sql($sql, $params);
    }

    private static function get_teacher_courses_from_students_works(int $courseworkId, int $teacherId, $courses)
    {
        $studentsWorks = sg::get_students_works($courseworkId);

        foreach($studentsWorks as $work)
        {
            if(self::is_course_belong_to_teacher($work, $teacherId))
            {
                if(self::is_course_not_exist_in_courses_array($work->course, $courses))
                {
                    $courses[] = self::get_course($work->course);
                }
            }
        }

        return $courses;
    }

    private static function is_course_belong_to_teacher(\stdClass $work, int $teacherId) : bool 
    {
        if($work->teacher == $teacherId)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private static function is_course_not_exist_in_courses_array(int $courseId, array $courses)
    {
        foreach($courses as $course)
        {
            if($course->id == $courseId)
            {
                return false;
            }
        }

        return true;
    }

    private static function get_course(int $courseId)
    {
        global $DB;
        $where = array('id' => $courseId);
        return $DB->get_record('course', $where, 'id,fullname,shortname');
    }

    private static function sort_courses_array($courses)
    {
        if(count($courses) > 1)
        {
            usort($courses, function($a, $b)
            {
                return strcmp($a->fullname, $b->fullname);
            });
        }

        return $courses;
    }

    private static function is_this_course_is_configured_teacher_course(int $courseworkId, int $teacherId, int $courseId) : bool 
    {
        global $DB;
        $where = array(
            'coursework' => $courseworkId,
            'teacher' => $teacherId,
            'course' => $courseId
        );
        return $DB->record_exists('coursework_teachers', $where);
    }

    private static function is_this_course_exist_in_teacher_students_works(int $courseworkId, int $teacherId, int $courseId) : bool 
    {
        $studentsWorks = sg::get_students_works($courseworkId);

        foreach($studentsWorks as $work)
        {
            if(($work->teacher == $teacherId) && ($work->course == $courseId))
            {
                return true;
            }
        }

        return false;
    }


}
