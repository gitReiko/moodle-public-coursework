<?php

class UsingCollectionsOverview 
{
    private $course;
    private $cm;

    private $collections;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collections = $this->get_used_collections();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_using_themes_collections_header();
        if(count($this->collections))
        {
            $gui.= $this->get_used_collections_list();
        }
        $gui.= $this->get_use_new_theme_collection_button();
        return $gui;
    }

    private function get_used_collections()
    {
        $sql = 'SELECT ctc.* , cuc.id AS using_id
                FROM {coursework_used_collections} AS cuc 
                INNER JOIN {coursework_theme_collections} AS ctc 
                ON cuc.collection = ctc.id 
                WHERE cuc.coursework = ?
                ORDER BY ctc.name';
        $conditions = array($this->cm->instance);
        global $DB;
        return $DB->get_records_sql($sql, $conditions);
    }

    private function get_using_themes_collections_header() : string 
    {
        return '<h3>'.get_string('using_themes_collections_list', 'coursework').'</h3>';
    }

    private function get_used_collections_list() : string 
    {
        $table = '<table class="leaders_overview">';
        $table.= $this->get_collection_list_header();
        $table.= $this->get_collection_list_body();
        $table.= '</table>';
        return $table;
    }

    private function get_collection_list_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('description', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_collection_list_body() : string 
    {
        $body = '';

        foreach($this->collections as $collection)
        {
            $body.= '<tr>';
            $body.= '<td>'.$collection->name.'</td>';
            $body.= '<td style="max-width: 450px;">'.$collection->description.'</td>';
            $body.= '<td>'.$this->get_delete_button($collection).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_delete_button(stdClass $collection) : string
    {
        $str = '<form style="display:inline;">';
        $str.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $str.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ThemesCollectionsUsing::DELETE_THEME_USING.'">';
        $str.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEME_COLLECTIONS_USING.'">';
        $str.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.ThemesCollectionsUsing::OVERVIEW.'">';
        $str.= '<input type="hidden" name="'.COLLECTION.ROW.ID.'" value="'.$collection->using_id.'">';
        $str.= '<input type="submit" value="'.get_string('delete', 'coursework').'">';
        $str.= '</form>';
        return $str;
    }

    private function get_use_new_theme_collection_button() : string 
    {
        $btn = '<form>';
        $btn.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEME_COLLECTIONS_USING.'" >';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.ThemesCollectionsUsing::ADD_THEME_USING.'" >';
        $btn.= '<input type="submit" value="'.get_string('use_new_theme_collection', 'coursework').'" autofocus>';
        $btn.= '</form>';
        return $btn;
    }


}
