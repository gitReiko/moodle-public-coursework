<?php

namespace Coursework\Config\SetSuggestedThemes;

use Coursework\Lib\Getters\CommonGetter as cg;

abstract class Action 
{
    const ACTION_FORM = 'action_form';
    
    private $course;
    private $cm;

    private $collections;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collections = $this->get_collections();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_action_header();
        $gui.= Lib::get_go_to_collections_setup_page($this->cm->id);
        $gui.= $this->get_course_name();

        if($this->is_collection_exists())
        {
            $gui.= $this->get_collection_field();
            $gui.= $this->get_count_of_same_themes_field();
            $gui.= $this->get_buttons_panel();
            $gui.= $this->get_action_form();
        }
        else
        {
            $gui.= $this->get_themes_collections_not_exists();
            $gui.= $this->get_back_to_overview_button();
        }

        return $gui;
    }

    private function get_collection_course() : int 
    {
        $id = optional_param(Main::COURSE_ID, null, PARAM_INT);
        if(empty($id)) throw \Exception('Missing course id.');
        return $id;
    }

    private function get_collections()
    {
        global $DB;
        $where = array('course' => $this->get_collection_course());
        return $DB->get_records('coursework_themes_collections', $where, 'name');
    }

    abstract protected function get_action_header() : string;

    private function get_course_name() : string 
    {
        $courseName = cg::get_course_fullname($this->get_collection_course());
        $text = get_string('for_course', 'coursework').' ';
        $text.= \html_writer::tag('b', $courseName);
        return \html_writer::tag('p', $text);
    }

    private function is_collection_exists() : bool 
    {
        if(count($this->collections))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private function get_themes_collections_not_exists() : string 
    {
        $text = get_string('not_suitable_for_use', 'coursework');
        return \html_writer::tag('p', $text);
    }

    private function get_collection_field() : string 
    {
        $text = get_string('name', 'coursework');
        $field = \html_writer::tag('h4', $text);

        $text = $this->get_collections_select();
        $field.= \html_writer::tag('p', $text);
        
        return $field;
    }

    private function get_count_of_same_themes_field() : string 
    {
        $text = get_string('count_of_same_themes', 'coursework');
        $field = \html_writer::tag('h4', $text);

        $text = $this->get_count_of_same_themes_input();
        $field.= \html_writer::tag('p', $text);

        return $field;
    }

    private function get_count_of_same_themes_input() : string 
    {
        $attr = array(
            'type' => 'number',
            'name' => Main::COUNT_OF_SAME_THEMES,
            'value' => $this->get_default_count_of_same_themes(),
            'form' => self::ACTION_FORM,
            'min' => 1,
            'max' => 255,
            'required' => 'required',
            'autocomplete' => 'off',
        );
        return \html_writer::empty_tag('input', $attr);
    }

    abstract protected function get_default_count_of_same_themes() : int;

    private function get_collections_select() : string 
    {
        $attr = array(
            'name' => Main::COLLECTION_ID,
            'form' => self::ACTION_FORM,
            'autocomplete' => 'off',
            'autofocus' => 'autofocus'
        );
        $select = \html_writer::start_tag('select', $attr);

        foreach($this->collections as $collection)
        {
            $attr = array('value' => $collection->id);

            if(!empty($collection->description))
            {
                $attr = array_merge(
                    $attr, 
                    array('title' => $collection->description)
                );
            }

            if($this->is_task_selected($collection->id))
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $text = $collection->name;

            $select.= \html_writer::tag('option', $text, $attr);
        }

        $select.= \html_writer::end_tag('select');

        return $select;
    }

    abstract protected function is_task_selected(int $collectionId) : bool;

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table', $attr);
        $btns.= \html_writer::start_tag('tr');
        $btns.= \html_writer::tag('td', $this->get_action_button());
        $btns.= \html_writer::tag('td', $this->get_back_to_overview_button());
        $btns.= \html_writer::end_tag('tr');
        $btns.= \html_writer::end_tag('table');

        return $btns;
    }

    abstract protected function get_action_button() : string;

    private function get_back_to_overview_button() : string 
    {
        $attr = array(
            'method' => 'post', 
            'class' => 'back_button_form',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_action_form() : string 
    {
        $attr = array(
            'id' => self::ACTION_FORM, 
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= $this->get_neccessary_input_params();

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    abstract protected function get_neccessary_input_params() : string;


}
