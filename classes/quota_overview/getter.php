<?php

namespace Coursework\View\QuotaOverview;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\TeachersGetter as tg;

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
        $students = sg::get_all_students($this->cm);
        $students = sg::add_works_to_students($this->cm->instance, $students);

        $this->students = $students;
    }

    private function init_teachers() : void
    {
        $teachers = tg::get_coursework_teachers($this->cm->instance);
        $teachers = $this->add_courses_with_quotas_to_teachers($teachers);
        $teachers = $this->add_students_to_teachers_array($teachers);

        $this->teachers = $teachers;
    }

    private function add_courses_with_quotas_to_teachers($teachers)
    {
        foreach($teachers as $teacher)
        {
            $teacher->courses = tg::get_teacher_courses($this->cm->instance, $teacher->id);
            $teacher->courses = tg::get_courses_with_quotas($this->cm, $teacher->id, $teacher->courses);
        }

        return $teachers;
    }

    private function add_students_to_teachers_array(array $teachers) : array
    {
        foreach($this->students as $student)
        {
            foreach($teachers as $teacher)
            {
                if(($teacher->id == $student->teacher))
                {
                    foreach($teacher->courses as $course)
                    {
                        if($course->id == $student->course)
                        {
                            if(empty($course->students))
                            {
                                $course->students = array();
                            }

                            $course->students[] = $student;
                        }
                    }
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
            foreach($teacher->courses as $course)
            {
                $totalPlannedQuota += $course->total_quota;
                $totalUsedQuota += $course->used_quota;
                $totalAvailableQuota += $course->available_quota;
            }
        }

        $this->totalPlannedQuota = $totalPlannedQuota;
        $this->totalUsedQuota = $totalUsedQuota;
        $this->totalAvailableQuota = $totalAvailableQuota;
    }


    


}