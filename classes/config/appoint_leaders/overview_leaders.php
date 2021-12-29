<?php

namespace Coursework\Config\AppointLeaders;

class OverviewLeaders
{
    private $course;
    private $cm;

    private $leaders;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->leaders = $this->get_configured_leaders();
    }

    public function get_gui() : string 
    {
        $gui = StepByStep::get_leader_explanation($this->get_page_header());
        $gui.= StepByStep::get_help_button();

        if($this->is_coursework_has_leaders())
        {
            $gui.= $this->get_coursework_leaders_table();
        }

        $gui.= $this->get_add_leader_button();

        $this->log_coursework_leaders_overview();

        return $gui;
    }

    private function get_configured_leaders()
    {
        global $DB;
        $sql = 'SELECT ct.id, ct.teacher, ct.course, c.fullname as coursename, 
                    ct.quota, u.firstname, u.lastname
                FROM {coursework_teachers} as ct, {user} as u, {course} as c
                WHERE ct.teacher = u.id AND ct.course = c.id
                AND ct.coursework = ?
                ORDER BY u.lastname';
        $conditions = array($this->cm->instance);

        return $DB->get_records_sql($sql, $conditions);
    }

    private function get_page_header() : string 
    {
        $text = get_string('appoint_leaders', 'coursework');
        return \html_writer::tag('h2', $text);
    }

    private function is_coursework_has_leaders() : bool
    {
        if(empty($this->leaders)) return false;
        else return true;
    }

    private function get_coursework_leaders_table() : string 
    {
        $text = get_string('leaders_overview_table_header', 'coursework');
        $tbl = \html_writer::tag('h3', $text);

        $attr = array('class' => 'leaders_overview');
        $tbl.= \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_coursework_leaders_table_header();
        $tbl.= $this->get_coursework_leaders_table_body();
        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_coursework_leaders_table_header() : string 
    {
        $attr = array('class' => 'header');
        $header = \html_writer::start_tag('tr', $attr);
        $header.= \html_writer::tag('td', get_string('leader', 'coursework'));
        $header.= \html_writer::tag('td', get_string('course', 'coursework'));
        $header.= \html_writer::tag('td', get_string('quota', 'coursework'));
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::end_tag('tr');

        return $header;
    }

    private function get_coursework_leaders_table_body() : string 
    {
        $body = '';

        foreach($this->leaders as $leader)
        {
            $body.= \html_writer::start_tag('tr');

            $text = $leader->lastname.' '.$leader->firstname;
            $body.= \html_writer::tag('td', $text);

            $text = $leader->coursename;
            $body.= \html_writer::tag('td', $text);

            $attr = array('align' => 'center');
            $text = $leader->quota;
            $body.= \html_writer::tag('td', $text, $attr);

            $text = $this->get_edit_button($leader);
            $body.= \html_writer::tag('td', $text);

            $text = $this->get_delete_button($leader->id);
            $body.= \html_writer::tag('td', $text);

            $body.= \html_writer::end_tag('tr');
        }

        return $body;
    }

    private function get_edit_button(\stdClass $leader) : string 
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
            'name' => Main::COURSE_MODULE_ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::EDIT_LEADER
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::LEADER_ID,
            'value' => $leader->teacher
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COURSE_ID,
            'value' => $leader->course
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::QUOTA,
            'value' => $leader->quota
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::LEADER_ROW_ID,
            'value' => $leader->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_delete_button(int $id) : string 
    {        
        $attr = array('method' => 'post');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('delete', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COURSE_MODULE_ID,
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
            'value' => Main::DELETE_LEADER
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::LEADER_ROW_ID,
            'value' => $id
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
            'value' => get_string('add_leader', 'coursework')
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::COURSE_MODULE_ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::ADD_LEADER
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function log_coursework_leaders_overview() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\coursework_leaders_overview::create($params);
        $event->trigger();
    }

}


