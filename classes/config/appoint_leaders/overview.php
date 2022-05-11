<?php

namespace Coursework\Config\AppointLeaders;

use Coursework\Lib\Getters\CoursesGetter as coug;
use Coursework\Lib\Getters\UserGetter as ug;

class Overview
{
    private $course;
    private $cm;

    private $leaders;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->leaders = $this->get_leaders();
    }

    public function get_gui() : string 
    {
        $gui = StepByStep::get_appoint_explanation($this->get_page_header());
        $gui.= StepByStep::get_help_button();

        if($this->is_coursework_has_leaders())
        {
            $gui.= $this->get_overview_table();
        }

        $gui.= $this->get_add_leader_button();

        $this->log_coursework_leaders_overview();

        return $gui;
    }

    private function get_leaders()
    {
        $leaders = $this->get_coursework_teachers();
        $leaders = $this->add_names_to_teachers($leaders);
        $leaders = $this->add_course_names_to_leaders($leaders);
        return $leaders;
    }

    private function get_coursework_teachers()
    {
        global $DB;
        $where = array('coursework' => $this->cm->instance);
        $fields = 'id,teacher,course,quota';
        return $DB->get_records('coursework_teachers', $where, '', $fields);
    }

    private static function add_names_to_teachers($teachers)
    {
        foreach($teachers as $teacher)
        {
            $user = ug::get_user($teacher->teacher);

            $teacher->firstname = $user->firstname;
            $teacher->lastname = $user->lastname;
        }

        usort($teachers, function($a, $b)
        {
            return strcmp(
                $a->lastname.$a->firstname,
                $b->lastname.$b->firstname
            );
        });

        return $teachers;
    }

    private function add_course_names_to_leaders($leaders)
    {
        foreach($leaders as $leader)
        {
            $leader->coursename = coug::get_course_fullname($leader->course);
        }

        return $leaders;
    }

    private function get_page_header() : string 
    {
        $attr = array('title' => get_string('appoint_explanation', 'coursework'));
        $text = get_string('appoint_leaders', 'coursework');
        return \html_writer::tag('h2', $text, $attr);
    }

    private function is_coursework_has_leaders() : bool
    {
        if(empty($this->leaders)) return false;
        else return true;
    }

    private function get_overview_table() : string 
    {
        $text = get_string('leaders_overview_table_header', 'coursework');
        $tbl = \html_writer::tag('h3', $text);

        $attr = array('class' => 'leaders_overview');
        $tbl.= \html_writer::start_tag('table', $attr);
        $tbl.= $this->get_overview_table_header();
        $tbl.= $this->get_overview_table_body();
        $tbl.= \html_writer::end_tag('table');

        return $tbl;
    }

    private function get_overview_table_header() : string 
    {
        $attr = array('class' => 'header');
        $header = \html_writer::start_tag('tr', $attr);
        $header.= $this->get_leader_cell();
        $header.= $this->get_course_cell();
        $header.= $this->get_quota_cell();
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::tag('td', '');
        $header.= \html_writer::end_tag('tr');

        return $header;
    }

    private function get_leader_cell() : string 
    {
        $attr = array('title' => get_string('leader_explanation', 'coursework'));
        $text = get_string('leader', 'coursework');
        $text = StepByStep::get_leader_explanation($text);
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_course_cell() : string 
    {
        $attr = array('title' => get_string('leader_course_explanation', 'coursework'));
        $text = get_string('course', 'coursework');
        $text = StepByStep::get_leader_course_explanation($text);
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_quota_cell() : string 
    {
        $attr = array('title' => get_string('quota_explanation', 'coursework'));
        $text = get_string('quota', 'coursework');
        $text = StepByStep::get_quota_explanation($text);
        return \html_writer::tag('td', $text, $attr);
    }

    private function get_overview_table_body() : string 
    {
        $body = '';

        $i = 0;
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

            $body.= $this->get_edit_button_body_cell($leader, $i);
            $body.= $this->get_delete_button_body_cell($leader, $i);

            $body.= \html_writer::end_tag('tr');

            $i++;
        }

        return $body;
    }

    private function get_edit_button_body_cell(\stdClass $leader, int $i) : string 
    {
        $text = $this->get_edit_button($leader);

        if(empty($i))
        {
            $text = StepByStep::get_edit_button_explanation($text);
        }

        return \html_writer::tag('td', $text);
    }

    private function get_delete_button_body_cell(\stdClass $leader, int $i) : string 
    {
        $text = $this->get_delete_button($leader->id);

        if(empty($i))
        {
            $text = StepByStep::get_delete_button_explanation($text);
        }

        return \html_writer::tag('td', $text);
    }

    private function get_edit_button(\stdClass $leader) : string 
    {
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('edit', 'coursework'),
            'title' => get_string('no_effect_on_choice_made', 'coursework')
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
        $confText = get_string('confirm_irreversible_action', 'coursework');
        $attr = array(
            'method' => 'post',
            'onsubmit' => 'return confirm_leader_deleting(`'.$confText.'`)',
            'action' => Main::MODULE_URL
        );
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'submit',
            'value' => get_string('delete', 'coursework'),
            'title' => get_string('no_effect_on_choice_made', 'coursework')
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
        $attr = array(
            'method' => 'post',
            'action' => Main::MODULE_URL
        );
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


