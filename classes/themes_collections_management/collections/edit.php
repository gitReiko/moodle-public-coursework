<?php

namespace Coursework\View\ThemesCollectionsManagement\Collections;

use Coursework\View\ThemesCollectionsManagement\Main;

class Edit extends Action 
{
    private $collection;

    function __construct($course, $cm)
    {
        parent::__construct($course, $cm);

        $this->collection = $this->get_collection();
    }

    protected function get_action_header() : string
    {
        $text = get_string('edit_collection_header', 'coursework').' ';
        $text.= \html_writer::tag('b', $this->collection->name);
        return \html_writer::tag('h3', $text);
    }

    protected function get_name_input_value() : string
    {
        return $this->collection->name;
    }

    protected function is_course_selected(int $courseId) : bool
    {
        if($courseId == $this->collection->course) 
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    protected function get_description_text() : string
    {
        if(empty($this->collection->description))
        {
            return '';
        }
        else 
        {
            return $this->collection->description;
        }
    }

    protected function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('save_changes', 'coursework')
        );
        $btn = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $btn);
    }

    protected function get_unique_form_hidden_inputs() : string
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::EDIT_COLLECTION
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COLLECTION_ID,
            'value' => $this->collection->id
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_collection()
    {
        $collectionId = $this->get_collection_id();

        global $DB;
        $condition = array('id' => $collectionId);
        return $DB->get_record('coursework_themes_collections', $condition);
    }

    private function get_collection_id()
    {
        $collectionId = optional_param(Main::COLLECTION_ID, null, PARAM_INT);
        if(empty($collectionId)) throw new \Exception('Missing collection row id.');
        return $collectionId;
    }

}


