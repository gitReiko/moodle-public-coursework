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
    private $availableGroups;
    private $groups;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->init_group_mode();
        $this->init_available_groups();
        $this->init_groups();
        $this->init_selected_group_id();
    }

    public function get_group_mode() 
    {
        return $this->groupMode;
    }

    public function get_available_groups()
    {
        return $this->availableGroups;
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

    private function init_available_groups()
    {
        $this->availableGroups = lib::get_coursework_groups($this->cm);
    }

    private function init_groups() 
    {
        $groups = array($this->get_all_groups_item());
        $this->groups = array_merge($groups, $this->availableGroups);
    }

    private function get_all_groups_item() : \stdClass 
    {
        $group = new \stdClass;
        $group->id = grp::ALL_GROUPS;
        $group->name = get_string('all_groups', 'coursework');

        return $group;
    }

    private function init_selected_group_id()
    {
        if($this->is_request_group_id_exists())
        {
            $id = $this->get_selected_group_id_from_request();
        }
        else 
        {
            if($this->is_groups_exists())
            {
                $id = $this->get_first_group_from_groups_array();
            }
            else 
            {
                $id = grp::ALL_GROUPS;
            }
        }

        $this->selectedGroupId = $id;
    }

    private function get_selected_group_id_from_request()
    {
        return optional_param(grp::GROUP, null, PARAM_INT);
    }

    private function is_request_group_id_exists() : bool 
    {
        if(optional_param(grp::GROUP, null, PARAM_INT))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_groups_exists() : bool 
    {
        if(empty(reset($this->groups)->id))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_first_group_from_groups_array()
    {
        return reset($this->groups)->id;
    }


}