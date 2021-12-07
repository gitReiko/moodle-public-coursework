<?php

namespace Coursework\Lib\Getters;

require_once 'students_getter.php';

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Enums as enum;

class TeachersGetter
{

    public static function get_users_with_teacher_role(\stdClass $cm) : array 
    {
        // This method returns list of users with given capability, 
        // it ignores enrolment status and should be used only above the course contex.
        $teachers = get_users_by_capability(
            \context_module::instance($cm->id), 
            'mod/coursework:is_teacher', 
            'u.id,u.firstname,u.lastname', 
            'u.lastname');
        
        foreach($teachers as $teacher)
        {
            $teacher->fullname = $teacher->lastname.' '.$teacher->firstname;
        }

        return $teachers;
    }

    public static function get_configured_teachers(int $courseworkId)
    {
        global $DB;
        $sql = 'SELECT DISTINCT u.id, u.firstname, u.lastname, u.email, u.phone1, u.phone2
                FROM {coursework_teachers} as ct, {user} as u
                WHERE ct.teacher = u.id AND coursework = ?
                ORDER BY u.lastname, u.firstname ';
        $conditions = array($courseworkId);

        return $DB->get_records_sql($sql, $conditions);
    }

    public static function get_coursework_teachers(int $courseworkId)
    {
        $teachers = self::get_configured_teachers($courseworkId);

        if(empty($teachers))
        {
            $teachers = array();
        }

        $teachers = self::get_not_configured_teachers($courseworkId, $teachers);
        $teachers = self::sort_teachers_array($teachers);
        $teachers = self::get_unique_items($teachers);

        return $teachers;
    }

    private static function get_not_configured_teachers(int $courseworkId, $teachers)
    {
        $studentsWorks = sg::get_all_coursework_students_works($courseworkId);

        foreach($studentsWorks as $work)
        {
            if(self::is_teacher_not_exist_in_teachers_array($work->teacher, $teachers))
            {
                $teachers[] = self::get_teacher($work->teacher);
            }
        }

        return $teachers;
    }

    public static function get_teacher(int $userId) 
    {
        global $DB;
        $where = array('id' => $userId);
        $select = 'id,firstname,lastname,email,phone1,phone2';
        return $DB->get_record('user', $where, $select);
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
        $courses = self::get_unique_items($courses);

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

    public static function get_courses_with_quotas(\stdClass $cm, int $teacherId, $courses)
    {
        foreach($courses as $course)
        {
            $course->total_quota = self::get_course_configured_quota($cm, $teacherId, $course->id);
            $course->used_quota = self::get_course_used_quota($cm, $teacherId, $course->id); 
            $course->available_quota = $course->total_quota - $course->used_quota;
        }

        return $courses;
    }

    public static function get_available_leader_quota_in_course(\stdClass $cm, int $teacherId, int $courseId)
    {
        $totalQuota = self::get_course_configured_quota($cm, $teacherId, $courseId);
        $usedQuota = self::get_course_used_quota($cm, $teacherId, $courseId); 
        $availableQuota = $course->total_quota - $course->used_quota;

        return $availableQuota;
    }

    private static function get_course_configured_quota(\stdClass $cm, int $teacherId, int $courseId)
    {
        global $DB;
        $where = array(
            'coursework' => $cm->instance,
            'teacher' => $teacherId,
            'course' => $courseId
        );

        $quota = $DB->get_field('coursework_teachers', 'quota', $where);

        if($quota)
        {
            return $quota;
        }
        else 
        {
            return 0;
        }
    }

    private static function get_course_used_quota(\stdClass $cm, int $teacherId, int $courseId) : int 
    {
        $students = sg::get_all_students($cm);

        $usedQuota = 0;
        foreach($students as $student)
        {
            if(self::is_student_used_quota($cm, $student->id, $teacherId, $courseId))
            {
                $usedQuota++;
            }
        }
        
        return $usedQuota;
    }

    private static function is_student_used_quota(\stdClass $cm, int $studentId, int $teacherId, int $courseId) : bool 
    {
        global $DB;
        $conditions = array
        (
            'coursework' => $cm->instance, 
            'student' => $studentId,
            'teacher' => $teacherId, 
            'course' => $courseId
        );
        return $DB->record_exists('coursework_students', $conditions);
    }

    private static function is_teacher_not_exist_in_teachers_array(int $teacherId, $teachers) : bool
    {
        foreach($teachers as $teacher)
        {
            if($teacher->id == $teacherId)
            {
                return false;
            }
        }

        return true;
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

    private static function get_unique_items($items)
    {
        $uniqueItems = array();

        foreach($items as $item)
        {
            if(self::is_item_not_exist($uniqueItems, $item))
            {
                $uniqueItems[] = $item;
            }
        }

        return $uniqueItems;
    }

    private static function is_item_not_exist(array $uniqueItems, $item) : bool 
    {
        foreach($uniqueItems as $uniqueItem)
        {
            if($uniqueItem->id == $item->id)
            {
                return false;
            }
        }

        return true;
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
        $studentsWorks = sg::get_all_coursework_students_works($courseworkId);

        foreach($studentsWorks as $work)
        {
            if(self::is_course_exists($work))
            {
                if(self::is_course_belong_to_teacher($work, $teacherId))
                {
                    if(self::is_course_not_exist_in_courses_array($work->course, $courses))
                    {
                        $courses[] = self::get_course($work->course);
                    }
                }
            }
        }

        return $courses;
    }

    private static function is_course_exists(\stdClass $work) : bool 
    {
        if(empty($work->course))
        {
            return false;
        }
        else 
        {
            return true;
        }
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
        $studentsWorks = sg::get_all_coursework_students_works($courseworkId);

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
