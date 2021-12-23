<?php

namespace Coursework\Config\SetSuggestedThemes;

class Edit extends Action 
{
    private $collectionId;
    private $themesUsingId;
    private $countOfSameThemes;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        parent::__construct($course, $cm);

        $this->collectionId = $this->get_collection_id();
        $this->themesUsingId = $this->get_themes_using_id();
        $this->countOfSameThemes = $this->get_count_of_same_thems();
    }

    private function get_collection_id() : int 
    {
        $id = optional_param(Main::COLLECTION_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing collection id.');
        return $id;
    }

    private function get_themes_using_id() : int 
    {
        $id = optional_param(Main::THEMES_USING_ID, null, PARAM_INT);
        if(empty($id)) throw new \Exception('Missing themes using id.');
        return $id;
    }

    private function get_count_of_same_thems() : int 
    {
        global $DB;
        $where = array('id' => $this->themesUsingId);
        return $DB->get_field('coursework_used_collections', 'countofsamethemes', $where);
    }

    protected function get_action_header() : string 
    {
        $text = get_string('change_suggested_themes_collection', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    protected function is_task_selected(int $collectionId) : bool
    {
        if($collectionId == $this->collectionId)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    protected function get_default_count_of_same_themes() : int
    {
        return $this->countOfSameThemes;
    }

    protected function get_action_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('change', 'coursework'),
            'form' => self::ACTION_FORM
        );
        return \html_writer::empty_tag('input', $attr);
    }

    protected function get_neccessary_input_params() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::CHANGE_USING_THEMES,
            'form' => self::ACTION_FORM
        );
        $params = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::THEMES_USING_ID,
            'value' => $this->themesUsingId,
            'form' => self::ACTION_FORM
        );
        $params.= \html_writer::empty_tag('input', $attr);

        return $params;
    }

}
