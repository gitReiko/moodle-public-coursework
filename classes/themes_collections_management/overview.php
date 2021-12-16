<?php

namespace Coursework\View\ThemesCollectionsManagement;

class Overview
{
    private $course;
    private $cm;

    private $collections;

    function __construct(\stdClass $course, \stdClass $cm)
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
        $text = get_string('collections_list', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_collections_table() : string 
    {
        $attr = array('class' => 'leaders_overview');
        $tbl = \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_collections_table_header();
        $tbl.= $this->get_collections_table_body();
        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_collections_table_header() : string 
    {
        $attr = array('class' => 'header');
        $head = \html_writer::start_tag('tr', $attr);
        $head.= \html_writer::tag('td', get_string('name', 'coursework'));
        $head.= \html_writer::tag('td', get_string('course', 'coursework'));
        $head.= \html_writer::tag('td', get_string('description', 'coursework'));
        $head.= \html_writer::tag('td', '');
        $head.= \html_writer::tag('td', '');
        $head.= \html_writer::end_tag('tr');

        return $head;
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

    private function get_edit_button(\stdClass $collection) : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('edit', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::EDIT_COLLECTION.'">';
        $button.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$collection->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_themes_management_button(\stdClass $collection) : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('coursework_themes_management', 'coursework').'">';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::THEMES_MANAGEMENT.'">';
        $button.= '<input type="hidden" name="'.COLLECTION.ID.'" value="'.$collection->id.'">';
        $button.= '</form>';
        return $button;
    }

    private function get_add_leader_button() : string 
    {
        $button = '<form method="post">';
        $button.= '<input type="submit" value="'.get_string('add_collection', 'coursework').'" autofocus>';
        $button.= '<input type="hidden" name="id" value="'.$this->cm->id.'" >';
        $button.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::ADD_COLLECTION.'">';
        $button.= '</form>';
        return $button;
    }
    

}


