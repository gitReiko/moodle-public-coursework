<?php


class CollectionsOverview
{
    private $course;
    private $cm;

    private $collections;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->collections = $this->get_all_collections();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_overview_header();

        if(count($this->collections))
        {
            $gui.= $this->get_collections_table();
        }

        $gui.= $this->get_add_leader_button();

        return $gui;
    }

    private function get_all_collections()
    {
        global $DB;
        $sql = 'SELECT ctc.* , c.fullname AS coursename
                FROM {coursework_theme_collections} AS ctc 
                INNER JOIN {course} AS c 
                ON ctc.course = c.id
                ORDER BY ctc.name';
        return $DB->get_records_sql($sql, array());
    }

    private function get_overview_header() : string 
    {
        return '<h3>'.get_string('collections_list', 'coursework').'</h3>';
    }

    private function get_collections_table() : string 
    {
        $table = '<table class="leaders_overview">';
        $table.= $this->get_collections_table_header();
        $table.= $this->get_collections_table_body();
        $table.= '</table>';
        return $table;
    }

    private function get_collections_table_header() : string 
    {
        $header = '<tr class="header">';
        $header.= '<td>'.get_string('name', 'coursework').'</td>';
        $header.= '<td>'.get_string('course', 'coursework').'</td>';
        $header.= '<td>'.get_string('description', 'coursework'). '</td>';
        $header.= '<td></td>';
        $header.= '<td></td>';
        $header.= '</tr>';
        return $header;
    }

    private function get_collections_table_body() : string 
    {
        $body = '';

        foreach($this->collections as $collection)
        {
            $body.= '<tr>';
            $body.= '<td>'.$collection->name.'</td>';
            $body.= '<td>'.$collection->coursename.'</td>';
            $body.= '<td style="max-width: 450px;">'.$collection->description.'</td>';
            $body.= '<td>'.$this->get_edit_button($collection).'</td>';
            $body.= '<td>'.$this->get_themes_management_button($collection).'</td>';
            $body.= '</tr>';
        }

        return $body;
    }

    private function get_edit_button(stdClass $collection) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::EDIT_COLLECTION.'">';
        $button.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$collection->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_themes_management_button(stdClass $collection) : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('coursework_themes_management', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::THEMES_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$collection->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_add_leader_button() : string 
    {
        $button = '<form>';
        $button.= '<input type="submit" value="'.get_string('add_collection', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.THEMES_COLLECTIONS_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.LeadersSetting::GUI_TYPE.'" value="'.CollectionsManagement::ADD_COLLECTION.'">';
        $button.= '</form>';
        return $button;
    }
    

}


