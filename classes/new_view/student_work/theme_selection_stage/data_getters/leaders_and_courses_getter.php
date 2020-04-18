<?php


use coursework_lib as lib;

class LeadersAndCoursesGetter 
{
    private $course;
    private $cm;

    private $availableLeaders;
    private $availableCourses;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_available_leaders();
    }

    public function get_available_leaders()
    {
        return $this->availableLeaders;
    }

    public function get_available_courses() 
    {
        return $this->availableCourses;
    }

    private function init_available_leaders() 
    {
        $all = $this->get_all_coursework_leaders();
        $available = $this->filter_leaders_with_used_quota($all);

        $this->availableLeaders = $this->get_available_leaders_from_bunchs($available);
        $this->availableCourses = $this->get_available_courses_from_bunchs($available);
    }

    private function get_all_coursework_leaders()
    {
        global $DB;
        $where = array('coursework' => $this->cm->instance);
        return $DB->get_records('coursework_teachers', $where);
    }

    private function filter_leaders_with_used_quota($allLeaders) 
    {
        $leaders = array();

        foreach($allLeaders as $leader)
        {
            $allowableQuota = $leader->quota;
            $usedQuota = $this->get_leader_used_quota($leader);

            if($allowableQuota > $usedQuota)
            {
                $leaders[] = $leader;
            }
        }

        return $leaders;
    }

    private function get_leader_used_quota(stdClass $leader) 
    {
        global $DB;
        $where = array('coursework' => $this->cm->instance, 
                       'teacher' => $leader->teacher, 
                       'course' => $leader->course);
        return $DB->count_records('coursework_students', $where);
    }

    private function get_available_leaders_from_bunchs(array $bunchs) 
    {
        $leaders = array();
        foreach($bunchs as $bunch)
        {
            if($this->is_leader_exist($leaders, $bunch->teacher))
            {
                $this->add_course_to_leaders_from_bunch($leaders, $bunch);
            }
            else 
            {
                $leaders[] = $this->get_leader_from_bunch($bunch);
            }
        }
        return $leaders;
    }

    private function is_leader_exist(array $leaders, int $newLeaderId) : bool 
    {
        foreach($leaders as $leader)
        {
            if($leader->id == $newLeaderId) 
            {
                return true;
            }
        }

        return false;
    }

    private function add_course_to_leaders_from_bunch(array &$leaders, stdClass $bunch) 
    {
        foreach($leaders as $leader)
        {
            if($leader->id == $bunch->teacher) 
            {
                if($this->is_course_not_exist($leader->courses, $bunch->course))
                {
                    $leader->courses[] = $bunch->course;
                }
            }
        }
    }

    private function is_course_not_exist(array $courses, $newCourse) : bool 
    {
        foreach($courses as $course)
        {
            if($course == $newCourse)
            {
                return false;
            }
        }

        return true;
    }

    private function get_leader_from_bunch(stdClass $bunch) : stdClass 
    {
        $leader = new stdClass;
        $leader->id = $bunch->teacher;
        $leader->fullname = cw_get_user_name($bunch->teacher);
        $leader->courses = array($bunch->course);
        return $leader;
    }

    private function get_available_courses_from_bunchs($bunchs)
    {
        $courses = array();
        foreach($bunchs as $bunch)
        {
            if($this->is_course_not_exist_for_stdClass_array($courses, $bunch->course))
            {
                $courses[] = $this->get_course_from_bunch($bunch);
            }
        }
        return $courses;
    }

    private function get_course_from_bunch(stdClass $bunch) : stdClass 
    {
        $course = new stdClass;
        $course->id = $bunch->course;
        $course->fullname = cw_get_course_name($bunch->course);
        return $course;
    }

    private function is_course_not_exist_for_stdClass_array(array $courses, $newCourse) : bool 
    {
        foreach($courses as $course)
        {
            if($course->id == $newCourse)
            {
                return false;
            }
        }

        return true;
    }


}