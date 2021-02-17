<?php

namespace view\quota_overview;
use coursework_lib as lib;

class Getter 
{

    private $cm;
    private $teachers;
    private $students;
    private $totalPlannedQuota;
    private $totalUsedQuota;
    private $totalAvailableQuota;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
        $this->init_students();
        $this->init_teachers();
        $this->init_total_quotas();
    }

    public function get_teachers() : array 
    {
        return $this->teachers;
    }

    public function get_students() : array 
    {
        return $this->students;
    }

    public function get_students_count() : int 
    {
        return count($this->students);
    }

    public function get_total_planned_quota() : int 
    {
        return $this->totalPlannedQuota;
    }

    public function get_total_used_quota() : int 
    {
        return $this->totalUsedQuota;
    }

    public function get_total_available_quota() : int 
    {
        return $this->totalAvailableQuota;
    }

    private function init_students() : void
    {
        $students = lib\get_coursework_students($this->cm);
        $students = $this->add_student_work_info($students);

        $this->students = $students;
    }

    private function add_student_work_info(array $students) : array 
    {
        foreach($students as $student)
        {
            $work = $this->get_student_work($student->id);

            $student->teacherid = $work->teacher;
            $student->courseid = $work->course;

            if(empty($work->theme))
            {
                $student->themename = $work->owntheme;
            }
            else
            {
                $student->themename = lib\get_theme_name($work->theme);
            }
        }

        return $students;
    }

    private function get_student_work(int $studentId) 
    {
        global $DB;
        $conditions = array 
        (
            'coursework' => $this->cm->instance,
            'student' => $studentId,
        );
        return $DB->get_record('coursework_students', $conditions);
    }

    private function init_teachers() : void
    {
        $teachers = lib\get_teachers($this->cm->instance);
        $teachers = $this->add_not_configurated_teachers_from_students_array($teachers);
        $teachers = $this->add_used_and_available_quotas_to_teachers_array($teachers);
        $teachers = $this->add_students_to_teachers_array($teachers);

        $this->teachers = $teachers;
    }

    private function add_not_configurated_teachers_from_students_array(array $teachers) : array
    {
        foreach($this->students as $student)
        {
            if($this->is_student_assigned_to_teacher($student->id))
            {
                if($this->is_teacher_missing_in_teachers_array($teachers, $student))
                {
                    $teachers = $this->add_teacher_to_teachers_array($teachers, $student->teacherid, $student->courseid);
                }
            }
        }

        return $teachers;
    }

    private function is_student_assigned_to_teacher(int $studentId) : bool 
    {
        global $DB;
        $conditions = array 
        (
            'coursework' => $this->cm->instance,
            'student' => $studentId,
        );
        return $DB->record_exists('coursework_students', $conditions);
    }

    private function is_teacher_missing_in_teachers_array(array $teachers, \stdClass $student) : bool 
    {
        foreach($teachers as $teacher)
        {
            if(($teacher->teacherid == $student->teacherid)
                && ($teacher->courseid == $student->courseid))
            {
                return false;
            }
        }

        return true;
    }

    private function add_teacher_to_teachers_array(array $teachers, int $studentTeacher, int $courseId) : array
    {
        $teacher = $this->get_teacher($studentTeacher, $courseId);
        $teachers = array_merge($teachers, array($teacher));

        usort($teachers, function($a, $b)
        {
            return strcmp($a->fullname, $b->fullname);
        });

        return $teachers;
    }

    private function get_teacher(int $teacherId, int $courseId) : \stdClass
    {
        $teacher = lib\get_user_stdclass($teacherId);
        $teacher->teacherid = $teacherId;
        $teacher->fullname = lib\get_user_fullname($teacher);
        $teacher->courseid = $courseId;
        $teacher->coursename = lib\get_course_fullname($courseId);
        $teacher->quota = 0;

        return $teacher;
    }

    private function add_used_and_available_quotas_to_teachers_array(array $teachers) : array
    {
        foreach($teachers as $teacher)
        {
            $teacher->total_quota = $teacher->quota;
            $teacher->used_quota = lib\get_leader_used_quota($this->cm, $teacher->teacherid, $teacher->courseid); 
            $teacher->available_quota = $teacher->total_quota - $teacher->used_quota;
        }

        return $teachers;
    }

    private function add_students_to_teachers_array(array $teachers) : array
    {
        foreach($this->students as $student)
        {
            foreach($teachers as $teacher)
            {
                if(($teacher->teacherid == $student->teacherid)
                    && ($teacher->courseid == $student->courseid))
                {
                    if(empty($teacher->students))
                    {
                        $teacher->students = array();
                    }

                    $teacher->students[] = $student;
                }
            }
        }

        return $teachers;
    }

    private function init_total_quotas() : void
    {
        $totalPlannedQuota = 0;
        $totalUsedQuota = 0;
        $totalAvailableQuota = 0;

        foreach($this->teachers as $teacher)
        {
            $totalPlannedQuota += $teacher->total_quota;
            $totalUsedQuota += $teacher->used_quota;
            $totalAvailableQuota += $teacher->available_quota;
        }

        $this->totalPlannedQuota = $totalPlannedQuota;
        $this->totalUsedQuota = $totalUsedQuota;
        $this->totalAvailableQuota = $totalAvailableQuota;
    }


    


}