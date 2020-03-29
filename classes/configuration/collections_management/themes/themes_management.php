<?php

class ThemesManagement
{
    private $course;
    private $cm;

    private $collection;
    private $themes;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collection = $this->get_collection();
        $this->themes = $this->get_collection_themes();
    }

    public function get_gui() : string
    {
        $gui = $this->get_themes_management_header();
        $gui.= $this->get_course_header();
        $gui.= $this->get_themes_list();
        $gui.= $this->get_back_to_overview_button();
        return $gui;
    }

    private function get_collection() : stdClass 
    {
        $collectionId = $this->get_collection_id();
        global $DB;
        return $DB->get_record('coursework_theme_collections', array('id'=>$collectionId));
    }

    private function get_collection_id() 
    {
        $id = optional_param(COLLECTION.ID, null, PARAM_INT);
        if(empty($id)) throw new Exception('Missing collection id.');
        return $id;
    }

    private function get_collection_themes()
    {
        global $DB;
        return $DB->get_records('coursework_themes', array('collection'=>$this->collection->id), 'name');
    }

    private function get_themes_management_header() : string 
    {
        $header = '<h3>'.get_string('collection_themes_management_header', 'coursework');
        $header.= ' <b>'.$this->collection->name.'</b></h3>';
        return $header;
    }

    private function get_course_header() : string 
    {
        $header = '<h3>'.get_string('collection_course_header', 'coursework');
        $header.= ' <b>'.cw_get_course_name($this->collection->course).'</b></h3>';
        return $header;
    }

    private function get_themes_list() : string
    {
        $str = '<ol>';
        foreach ($this->themes as $theme)
        {
            $str.= '<li>';
            $str.= $theme->name;
            $str.= $this->get_edit_theme_button($theme);
            $str.= $this->get_delete_theme_button($theme);
            $str.= '</li>';
        }
        $str.= $this->get_add_new_theme_button();
        $str.= '</ol>';

        return $str;
    }

    private function get_edit_theme_button(stdClass $theme) : string
    {
        $str = '<form style="display:inline;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.CollectionsManagement::EDIT_THEME.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.COLLECTIONS_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.CollectionsManagement::THEMES_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$this->collection->id.'">';
        $str.= '<input type="hidden" name="'.THEME.ID.'" value="'.$theme->id.'">';
        $str.= '<input type="text" minlength="5" maxlength="255" required name="'.NAME.'" size="80" autocomplete="off" style="display:none;" id="theme'.$theme->id.'">';
        $str.= '<input type="submit" value="'.get_string('edit', 'coursework').'" onclick="edit_theme('.$theme->id.',`'.$theme->name.'`)">';
        $str.= '</form>';
        return $str;
    }

    private function get_delete_theme_button(stdClass $theme) : string
    {
        $str = '<form style="display:inline;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.CollectionsManagement::DELETE_THEME.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.COLLECTIONS_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.CollectionsManagement::THEMES_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$this->collection->id.'">';
        $str.= '<input type="hidden" name="'.THEME.ID.'" value="'.$theme->id.'">';
        $str.= '<input type="submit" value="'.get_string('delete', 'coursework').'">';
        $str.= '</form>';
        return $str;
    }

    private function get_add_new_theme_button() : string
    {
        $str = '<li>';
        $str.= '<form>';
        $str.= '<input type="text" minlength="5" maxlength="255" required name="'.NAME.'" size="80" autocomplete="off">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.CollectionsManagement::ADD_THEME.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.COLLECTIONS_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.CollectionsManagement::THEMES_MANAGEMENT.'">';
        $str.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$this->collection->id.'">';
        $str.= '<input type="submit" value="'.get_string('add_new_theme', 'coursework').'" >';
        $str.= '</form>';
        $str.= '</li>';
        return $str;
    }

    private function get_back_to_overview_button() : string 
    {
        $button = "<p><form>";
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.CollectionsManagement::OVERVIEW.'">';
        $button.= '<input type="submit" value="'.get_string('back', 'coursework').'" >';
        $button.= '</form></p>';
        return $button;
    }

}


