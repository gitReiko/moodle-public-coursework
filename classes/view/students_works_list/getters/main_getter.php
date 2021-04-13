<?php

namespace Coursework\View\StudentsWorksList;

require_once 'groups_getter.php';
require_once 'students_getter.php';

use Coursework\View\StudentsWorksList\GroupsSelector as grp;
use Coursework\View\StudentsWorksList\TeachersSelector as ts;
use Coursework\View\StudentsWorksList\CoursesSelector as cs;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Getters\CommonGetter as cg;

class MainGetter 
{
    private $course;
    private $cm;

    private $groupMode;
    private $selectedGroupId;
    private $availableGroups;
    private $groups;

    private $teachers;
    private $selectedTeacherId;

    private $courses;
    private $selectedCourseId;

    private $teacherStudents;
    private $studentsWithoutTeacher;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->init_group_params();
        $this->init_teachers();

        if($this->is_teachers_exists())
        {
            $this->init_selected_teacher();
            $this->init_courses();
            $this->init_selected_course_id();
            $this->init_students();
        }
    }

    public function get_course() : \stdClass
    {
        return $this->course;
    }

    public function get_cm() : \stdClass
    {
        return $this->cm;
    }

    public function get_course_work_name() : string 
    {
        return cg::get_coursework_name($this->cm->instance);
    }

    public function get_group_mode() 
    {
        return $this->groupMode;
    }

    public function get_groups() 
    {
        return $this->groups;
    }

    public function get_selected_group_id() 
    {
        return $this->selectedGroupId;
    }

    public function get_teachers()
    {
        return $this->teachers;
    }

    public function get_selected_teacher_id()
    {
        return $this->selectedTeacherId;
    }

    public function get_courses() 
    {
        return $this->courses;
    }

    public function get_selected_course_id()
    {
        return $this->selectedCourseId;
    }

    public function get_teacher_students() 
    {
        return $this->teacherStudents;
    }

    public function get_students_without_teacher() 
    {
        return $this->studentsWithoutTeacher;
    }

    private function init_group_params() 
    {
        $grp = new GroupsGetter($this->course, $this->cm);

        $this->groupMode = $grp->get_group_mode();
        $this->groups = $grp->get_groups();
        $this->selectedGroupId = $grp->get_selected_group_id();
        $this->availableGroups = $grp->get_available_groups();
    }

    private function init_teachers() 
    {
        $this->teachers = tg::get_coursework_teachers($this->cm->instance);
    }

    private function is_teachers_exists() : bool 
    {
        if(is_array($this->teachers))
        {
            if(count($this->teachers) > 0)
            {
                return true;
            }
            else 
            {
                return false;
            }
        }
        else 
        {
            return false;
        }
    }

    private function init_selected_teacher()
    {
        $teacher = optional_param(ts::TEACHER, null, PARAM_INT);

        if(empty($teacher))
        {
            $this->selectedTeacherId = reset($this->teachers)->id;
        }
        else 
        {
            $this->selectedTeacherId = $teacher;
        }
    }

    private function init_courses()
    {
        $this->courses = tg::get_teacher_courses(
            $this->cm->instance, 
            $this->selectedTeacherId
        );
    }

    private function init_selected_course_id()
    {
        $course = optional_param(cs::COURSE, null, PARAM_INT);

        if(empty($course))
        {
            $this->selectedCourseId = reset($this->courses)->id;
        }
        else if(tg::is_this_course_is_teacher_course($this->cm->instance, $this->selectedTeacherId, $course))
        {
            $this->selectedCourseId = $course;
        }
        else 
        {
            $this->selectedCourseId = reset($this->courses)->id;
        }
    }

    private function init_students() 
    {
        $st = new StudentsGetter(
            $this->course, 
            $this->cm,
            $this->groupMode,
            $this->selectedGroupId,
            $this->availableGroups,
            $this->selectedTeacherId,
            $this->selectedCourseId
        );

        $this->teacherStudents = $st->get_teacher_students();
        $this->studentsWithoutTeacher = $st->get_students_without_teacher();
    }


}