<?php

namespace View\StudentsWorksList;

use CourseWork\LocalLib as lib;
use View\StudentsWorksList\Page as p;

class GroupsSelector 
{
    const GROUP = 'group';
    const ALL_GROUPS = -1;

    private $d;

    function __construct(Getter $d) 
    {
        $this->d = $d;
    }

    public function get_groups_selector() : string 
    {
        if($this->is_groups_enabled())
        {
            return $this->get_selector();
        }
        else 
        {
            return '';
        }
    }

    private function is_groups_enabled() : bool 
    {
        $groupMode = $this->d->get_group_mode();

        if($groupMode === lib::NO_GROUPS)
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_selector() : string 
    {
        $selector = $this->get_selector_start();
        $selector.= $this->get_all_groups_option();
        $selector.= $this->get_groups_options();
        $selector.= $this->get_selector_end();

        return $selector;
    }

    private function get_selector_start() : string 
    {
        $selector = \html_writer::start_tag('p');
        $selector.= get_string('group', 'coursework').' ';

        $attr = array(
            'name' => self::GROUP,
            'autocomplete' => 'off'
        );
        $selector.= \html_writer::start_tag('select', $attr);

        return $selector;
    }

    private function get_all_groups_option() : string 
    {
        $attr = array('value' => self::ALL_GROUPS);
        $text = get_string('all_groups', 'coursework');
        return \html_writer::tag('option', $text, $attr);
    }

    private function get_groups_options() : string 
    {
        $selector = '';

        foreach($this->d->get_groups() as $group)
        {
            $attr = array('value' => $group->id);
            $selector.= \html_writer::start_tag('option', $attr);
            $selector.= $group->name;
            $selector.= \html_writer::end_tag('option');
        }

        return $selector;
    }

    private function get_selector_end() : string 
    {
        $selector = \html_writer::end_tag('select');
        $selector.= \html_writer::end_tag('p'); 
        return $selector;
    }


}