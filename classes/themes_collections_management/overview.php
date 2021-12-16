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
            $body.= \html_writer::start_tag('tr');

            $body.= \html_writer::tag('td', $collection->name);
            $body.= \html_writer::tag('td', $collection->coursename);

            $attr = array('style' => 'max-width: 450px;');
            $body.= \html_writer::tag('td', $collection->description, $attr);

            $body.= \html_writer::tag('td', $this->get_edit_button($collection));
            $body.= \html_writer::tag('td', $this->get_themes_management_button($collection));

            $body.= \html_writer::end_tag('tr');
        }

        return $body;
    }

    private function get_edit_button(\stdClass $collection) : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('edit', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::EDIT_COLLECTION
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COLLECTION_ID,
            'value' => $collection->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_themes_management_button(\stdClass $collection) : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('coursework_themes_management', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::THEMES_MANAGEMENT
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COLLECTION_ID,
            'value' => $collection->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_add_leader_button() : string 
    {
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('add_collection', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::ADD_COLLECTION
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

}
