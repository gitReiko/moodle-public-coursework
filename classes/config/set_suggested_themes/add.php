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
            $field.= '<p>'.$this->get_collections_select().'</p>';
        }
        else 
        {
            $field.= '<p>'.get_string('not_suitable_for_use', 'coursework').'</p>';
        }
        
        return $field;
    }

    private function get_collections_select() : string 
    {
        $select = '<select name="'.COLLECTION.'" form="'.$this->formName.'" autocomplete="off" autofocus>';
        foreach($this->collections as $collection)
        {
            $select.= '<option ';
            if(!empty($collection->description))
            {
                $select.= " title='{$collection->description}' ";
            }
            $select.= " value='{$collection->id}' >";
            $select.= $collection->name;
            $select.= '</option>';
        }
        $select.= '</select>';
        return $select;
    }

    private function get_buttons_panel() : string 
    {
        $btns = '<table class="btns_panel"><tr>';
        $btns.= '<td>'.$this->get_create_button().'</td>';
        $btns.= '<td>'.$this->get_back_to_overview_button().'</td>';
        $btns.= '</tr></table>';
        return $btns;
    }

    private function get_create_button() : string 
    {
        return '<input type="submit" value="'.get_string('create', 'coursework').'" form="'.$this->formName.'">';
    }

    private function get_back_to_overview_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::OVERVIEW.'">';
        $button.= '<input type="submit" value="'.get_string('back', 'coursework').'" >';
        $button.= '</form>';
        return $button;
    }

    private function get_add_form() : string 
    {
        $btn = '<form id="'.$this->formName.'" method="post">';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::OVERVIEW.'"/>';
        $btn.= '<input type="hidden" name="'.Main::DATABASE_EVENT.'" value="'.Main::ADD_THEME_USING.'"/>';
        $btn.= '</form>';
        return $btn;
    }





}
