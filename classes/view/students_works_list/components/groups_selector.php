<?php

namespace Coursework\View\StudentsWorksList;

use Coursework\Lib\Enums as enum;
use Coursework\View\StudentsWorksList\Page as p;

class GroupsSelector 
{
    const GROUP = 'group';
    const ALL_GROUPS = -1;

    private $d;

    function __construct(MainGetter $d) 
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

        if($groupMode == enum::NO_GROUPS)
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
        $selector.= $this->get_groups_options();
        $selector.= $this->get_selector_end();

        return $selector;
    }

    private function get_selector_start() : string 
    {
        $selector = \html_writer::start_tag('p');
        $selector.= $this->get_selector_label();

        $attr = array(
            'name' => self::GROUP,
            'onchange' => 'submit_form(`'.Page::FORM_ID.'`)',
            'autocomplete' => 'off'
        );
        $selector.= \html_writer::start_tag('select', $attr);

        return $selector;
    }

    private function get_selector_label() : string 
    {
        return get_string('group', 'coursework').' &nbsp;';
    }

    private function get_groups_options() : string 
    {
        $selector = '';

        foreach($this->d->get_groups() as $group)
        {
            $attr = array('value' => $group->id);

            if($this->d->get_selected_group_id() == $group->id)
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $selector.= \html_writer::start_tag('option', $attr);
            $selector.= $group->name;
            $selector.= \html_writer::end_tag('option');
        }

        return $selector;
    }

    private function get_selector_end() : string 
    {
        $selector = \html_writer::end_tag('select');
        $selector.= ' '.$this->get_link_to_course_participants();
        $selector.= \html_writer::end_tag('p'); 
        return $selector;
    }

    private function get_link_to_course_participants() : string 
    {
        $url = '/user/index.php?id='.$this->d->get_course()->id;
        $attr = array('href' => $url, 'target' => '_blank');
        $text = ' &nbsp;'.get_string('view_participants', 'coursework');
        $text.= ' <i class="fa fa-external-link"></i>';
        return \html_writer::tag('a', $text, $attr);
    }


}