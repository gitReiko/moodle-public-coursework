<?php

namespace View\StudentsWorksList;

use View\StudentsWorksList\GroupsSelector as grp;
use CourseWork\LocalLib as lib;

class GroupsGetter 
{
    private $course;
    private $cm;

    private $groupMode;
    private $selectedGroupId;
    private $groups;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->init_group_mode();
        $this->init_groups();
        $this->init_selected_group_id();
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

    private function init_group_mode() 
    {
        $this->groupMode = lib::get_coursework_group_mode($this->cm);
    }

    private function init_groups() 
    {
        $groups = $this->get_all_groups_group();
        $this->groups = array_merge(array($groups), lib::get_coursework_groups($this->cm));
    }

    private function get_all_groups_group() : \stdClass 
    {
        $group = new \stdClass;
        $group->id = grp::ALL_GROUPS;
        $group->name = get_string('all_groups', 'coursework');

        return $group;
    }

    private function init_selected_group_id()
    {
        $selectedGroupId = optional_param(grp::GROUP, null, PARAM_INT);

        if(empty($selectedGroupId))
        {
            if(empty(reset($this->groups)->id))
            {
                $this->selectedGroupId = grp::ALL_GROUPS;
            }
            else 
            {
                $this->selectedGroupId = reset($this->groups)->id;
            }
        }
        else 
        {
            $this->selectedGroupId = $selectedGroupId;
        }
    }


}