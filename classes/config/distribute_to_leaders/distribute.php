<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\Classes\Lib\StudentsMassActions\Lib as massLib;
use Coursework\Lib\Getters\TeachersGetter as tg;

class Distribute 
{
    const DISTRIBUTE_FORM = 'distribute_form';

    private $course;
    private $cm;

    private $students;
    private $leaders;

    private $selectedLeaderId = 0;
    private $selectedCourseId = 0;
    private $selectedLeaderQuota = 0;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->students = massLib::get_distribute_students();
        $this->leaders = $this->get_teachers();
        $this->selectedLeaderId = reset($this->leaders)->id;
        $this->selectedLeaderQuota = $this->get_leader_quota();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form_start();
        $gui.= $this->get_students_distribution_header();
        $gui.= StepByStep::get_help_button();
        $gui.= $this->get_list_of_the_students_being_distributed();
        $gui.= massLib::get_action_students_inputs($this->students);
        $gui.= $this->get_leader_header();
        $gui.= $this->get_go_to_appoint_page();
        $gui.= StepByStep::get_leader_explanation($this->get_leader_select());
        $gui.= $this->get_course_header();
        $gui.= StepByStep::get_course_explanation($this->get_course_select());
        $gui.= StepByStep::get_quota_increase_explanation($this->get_expand_quota_panel());
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_data_for_javascript();

        return $gui;
    }

    private function get_teachers()
    {
        $teachers = tg::get_configured_teachers($this->cm->instance);

        foreach($teachers as $teacher)
        {
            $courses = tg::get_teacher_courses($this->cm->instance, $teacher->id);
            $teacher->courses = tg::get_courses_with_quotas($this->cm, $teacher->id, $courses);
        }

        return $teachers;
    }

    private function get_leader_quota()
    {
        return reset(reset($this->leaders)->courses)->available_quota;
    }

    private function get_html_form_start() : string 
    {
        $attr = array('id' => self::DISTRIBUTE_FORM, 'method' => 'post');
        return \html_writer::start_tag('form', $attr);
    }

    private function get_students_distribution_header() : string
    {
        $text = get_string('distribute_students_header', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_list_of_the_students_being_distributed() : string 
    {
        $text = '';

        foreach($this->students as $student)
        {
            $text.= $student->fullname.', ';
        }
        $text = mb_substr($text, 0, (mb_strlen($text) - 2));

        return \html_writer::tag('p', $text);
    }

    private function get_leader_header() : string 
    {
        $text = get_string('leader', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_go_to_appoint_page() : string 
    {
        $href = '/mod/coursework/pages/config/appoint_leaders.php?id='.$this->cm->id;
        $text = get_string('no_leaders_needed', 'coursework').'? ';
        $text.= get_string('go_to_leaders_appointment', 'coursework');
        $text = \html_writer::tag('a', $text, array('href' => $href));
        return \html_writer::tag('p', $text);
    }

    private function get_leader_select() : string
    {
        $attr = array(
            'id' => 'leaderselect',
            'name' => Main::TEACHER,
            'onchange' => 'change_leader_courses()',
            'autocomplete' => 'off',
            'autofocus' => 'autofocus'
        );
        $select = \html_writer::start_tag('select', $attr);

        foreach($this->leaders as $leader)
        {
            $attr = array(
                'value' => $leader->id
            );
            $text = $leader->lastname.' '.$leader->firstname;
            $select.= \html_writer::tag('option', $text, $attr);
        }

        $select.= \html_writer::end_tag('select');

        $select = \html_writer::tag('p', $select);

        return $select;
    }

    private function get_course_header() : string 
    {
        $text = get_string('course', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_course_select() : string
    {
        $attr = array(
            'id' => 'coursesselect',
            'name' => Main::COURSE,
            'autocomplete' => 'off',
            'onchange' => 'display_or_hide_expand_quota_panel_when_course_changes()'
        );
        $select = \html_writer::start_tag('select', $attr);
        foreach($this->leaders as $leader)
        {
            foreach($leader->courses as $course)
            {
                if($this->selectedLeaderId == $leader->id)
                {
                    $attr = array(
                        'class' => 'leadercourse',
                        'value' => $course->id
                    );
                    $text = $course->fullname;
                    $select.= \html_writer::tag('option', $text, $attr);
                }
            }
        }
        $select.= \html_writer::end_tag('select');

        $select = \html_writer::tag('p', $select);

        return $select;
    }

    private function get_expand_quota_panel() : string 
    {
        $attr = array('id' => 'expandquotapanel');

        if(count($this->students) > $this->selectedLeaderQuota)
        {
            $attr = array_merge($attr, array('style' => 'display: block;'));
        }
        else 
        {
            $attr = array_merge($attr, array('style' => 'display: none;'));
        }

        $panel = \html_writer::start_tag('div', $attr);

        $text = get_string('quota_exceeded', 'coursework');
        $text = \html_writer::tag('b', $text);
        $panel.= \html_writer::tag('p', $text);

        $attr = array(
            'type' => 'radio',
            'name' => Main::EXPAND_QUOTA,
            'value' => true
        );
        $text = \html_writer::empty_tag('input', $attr);
        $text.= ' '.get_string('expand_quota', 'coursework');
        $panel.= \html_writer::tag('p', $text);

        $attr = array(
            'type' => 'radio',
            'name' => Main::EXPAND_QUOTA,
            'value' => false,
            'checked' => 'checked'
        );
        $text = \html_writer::empty_tag('input', $attr);
        $text.= ' '.get_string('dont_change_quota', 'coursework');
        $panel.= \html_writer::tag('p', $text);

        $panel.= \html_writer::end_tag('div');

        return $panel;
    }

    private function get_buttons_panel() : string 
    {
        $p = \html_writer::start_tag('table');
        $p.= \html_writer::start_tag('tr');
        $p.= \html_writer::tag('td', $this->get_distribute_button());
        $p.= \html_writer::tag('td', $this->get_back_button());
        $p.= \html_writer::end_tag('tr');
        $p.= \html_writer::end_tag('table');

        return $p;
    }

    private function get_distribute_button() : string 
    {
        $attr = array('form' => self::DISTRIBUTE_FORM);
        $text = get_string('distribute', 'coursework');
        return \html_writer::tag('button', $text, $attr);
    }

    private function get_back_button() : string 
    {
        $attr = array('method' => 'post', 'class' => 'back_button_form');
        $btn = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $btn.= \html_writer::empty_tag('input', $attr);

        $btn.= \html_writer::tag('button', get_string('back', 'coursework'));

        $btn.= \html_writer::end_tag('form');

        return $btn;
    }

    private function get_html_form_end() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $end = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $end.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::DATABASE_EVENT
        );
        $end.= \html_writer::empty_tag('input', $attr);

        $end.= \html_writer::end_tag('form');

        return $end;
    }

    private function get_data_for_javascript() : string 
    {
        $js = '';
        foreach($this->leaders as $leader) 
        {
            foreach($leader->courses as $course)
            {
                $attr = array(
                    'class' => 'jsleaders',
                    'style' => 'display: hidden',
                    'data-leaderid' => $leader->id,
                    'data-courseid' => $course->id,
                    'data-coursename' => $course->fullname,
                    'data-quota' => $course->available_quota
                );

                $js.= \html_writer::tag('p', '', $attr);
            }
        }

        $attr = array(
            'id' => 'studentscount',
            'style' => 'display: hidden',
            'data-count' => count($this->students)
        );
        $js.= \html_writer::tag('p', '', $attr);

        return $js;
    }

}

