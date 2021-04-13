<?php

class AddNewCollectionUsing 
{
    private $course;
    private $cm;

    private $collections;
    private $formName ='add_form';

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collections = $this->get_not_used_collections();
    }

    public function get_gui() : string 
    {
        $gui = $this->add_new_collection_header();
        $gui.= $this->get_collection_field();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_add_form();
        return $gui;
    }

    // Only one collection can be used per course (in context of activity instance).
    private function get_not_used_collections()
    {
        $sql = 'SELECT *
                FROM {coursework_theme_collections}
                WHERE course NOT IN (
                    SELECT ctc.course 
                    FROM {coursework_used_collections} AS cuc
                    INNER JOIN {coursework_theme_collections} AS ctc
                    ON cuc.collection = ctc.id
                    WHERE cuc.coursework = ?)
                ORDER BY name';
        $conditions = array($this->cm->instance);
        global $DB;
        return $DB->get_records_sql($sql, $conditions);
    }

    private function add_new_collection_header() : string 
    {
        return '<h3>'.get_string('add_new_collection_using_header', 'coursework').'</h3>';
    }

    private function get_collection_field() : string 
    {
        $field = '<h4>'.get_string('themes_collection', 'coursework').'</h4>';

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
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEME_COLLECTIONS_USING.'">';
        $button.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.ThemesCollectionsUsing::OVERVIEW.'">';
        $button.= '<input type="submit" value="'.get_string('back', 'coursework').'" >';
        $button.= '</form>';
        return $button;
    }

    private function get_add_form() : string 
    {
        $btn = '<form id="'.$this->formName.'" method="post">';
        $btn.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEME_COLLECTIONS_USING.'"/>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.ThemesCollectionsUsing::OVERVIEW.'"/>';
        $btn.= '<input type="hidden" name="'.ConfigurationManager::DATABASE_EVENT.'" value="'.ThemesCollectionsUsing::ADD_THEME_USING.'"/>';
        $btn.= '</form>';
        return $btn;
    }





}
