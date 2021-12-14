<?php

namespace Coursework\Config\SetSuggestedThemes;

class Add 
{
    private $course;
    private $cm;

    private $collections;
    private $formName ='add_form';

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collections = $this->get_collections();
    }

    public function get_gui() : string 
    {
        $gui = $this->add_new_collection_header();
        $gui.= $this->get_collection_field();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_add_form();
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
        return $DB->get_records('coursework_theme_collections', $where, 'name');
    }

    private function add_new_collection_header() : string 
    {
        $text = get_string('add_suggested_themes_collection', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_collection_field() : string 
    {
        $text = get_string('name', 'coursework');
        $field = \html_writer::tag('h4', $text);

        if(count($this->collections))
        {
            $text = $this->get_collections_select();
            $field.= \html_writer::tag('p', $text);
        }
        else 
        {
            $text = get_string('not_suitable_for_use', 'coursework');
            $field.= \html_writer::tag('p', $text);
        }
        
        return $field;
    }

    private function get_collections_select() : string 
    {
        $attr = array(
            'name' => Main::COLLECTION_ID,
            'form' => $this->formName,
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

            $text = $collection->name;

            $select.= \html_writer::tag('option', $text, $attr);
        }

        $select.= \html_writer::end_tag('select');

        return $select;
    }

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $btns = \html_writer::start_tag('table', $attr);
        $btns.= \html_writer::start_tag('tr');
        $btns.= \html_writer::tag('td', $this->get_create_button());
        $btns.= \html_writer::tag('td', $this->get_back_to_overview_button());
        $btns.= \html_writer::end_tag('tr');
        $btns.= \html_writer::end_tag('table');

        return $btns;
    }

    private function get_create_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('create', 'coursework'),
            'form' => $this->formName
        );
        return \html_writer::empty_tag('input', $attr);
    }

    private function get_back_to_overview_button() : string 
    {
        $attr = array('method' => 'post');
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

    private function get_add_form() : string 
    {
        $attr = array('id' => $this->formName, 'method' => 'post');
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
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::ADD_THEME_USING
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

}
