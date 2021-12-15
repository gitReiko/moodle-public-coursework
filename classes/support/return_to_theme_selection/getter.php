<?php

namespace Coursework\Support\ReturnToThemeSelection;

use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;

class Getter 
{
    private $course;
    private $cm;

    private $groups;
    private $students;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->groups = groups_get_activity_allowed_groups($cm);
        $this->students = $this->init_students();
    }

    public function get_groups()
    {
        return $this->groups;
    }

    public function get_students()
    {
        return $this->students;
    }

    private function init_students()
    {
        $students = sg::get_all_students($this->cm);
        $students = sg::add_works_to_students($this->cm->instance, $students);
        $students = $this->add_groups_to_students($students);

        return $students;
    }

    private function add_groups_to_students(array $students) : array 
    {
        foreach($students as $student)
        {
            foreach($this->groups as $group)
            {
                if(groups_is_member($group->id, $student->id))
                {
                    $temp = new \stdClass;
                    $temp->id = $group->id;
                    $temp->name = $group->name;

                    $student->groups[] = $temp;
                }
            }
        }

        return $students;
    }

}
