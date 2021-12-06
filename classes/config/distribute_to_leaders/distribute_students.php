<?php

namespace Coursework\Config\DistributeToLeaders;

use Coursework\ClassesLib\StudentsMassActions\StudentsTable as st;
use Coursework\Lib\Getters\TeachersGetter as tg;

class DistributeStudents 
{
    // Выводить предупреждение в случае о уже распределённых студентах
    private $course;
    private $cm;

    private $students;
    private $leaders;

    private $selectedLeaderId = 0;
    private $leaderQuota = 0;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->students = $this->get_distribute_students();
        $this->leaders = $this->get_teachers();
    }

    public function get_gui() : string 
    {
        $gui = $this->get_html_form_start();
        $gui.= $this->get_students_distribution_header();
        $gui.= $this->get_list_of_the_students_being_distributed();
        $gui.= $this->get_hidden_students_inputs();
        $gui.= $this->get_leader_header();
        $gui.= $this->get_leader_select();
        $gui.= $this->get_course_header();
        $gui.= $this->get_course_select();
        $gui.= $this->get_expand_quota_panel();
        $gui.= $this->get_buttons_panel();
        $gui.= $this->get_html_form_end();
        $gui.= $this->get_data_for_javascript();

        return $gui;
    }

    private function get_distribute_students() : array 
    {
        $students = array();
        $strings = optional_param_array(st::STUDENTS, null, PARAM_TEXT);

        foreach($strings as $string) 
        {
            $str = explode(st::SEPARATOR, $string);

            $student = new \stdClass;
            $student->id = $str[0];
            $student->fullname = $str[1];

            $students[] = $student;
        }

        return $students;
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

    private function get_html_form_start() : string 
    {
        return '<form method="post">';
    }

    private function get_students_distribution_header() : string
    {
        return'<h3>'.get_string('distribute_students_header', 'coursework').'</h3>';
    }

    private function get_list_of_the_students_being_distributed() : string 
    {
        $names = '<p>';
        foreach($this->students as $student)
        {
            $names.= $student->fullname.', ';
        }
        $names = mb_substr($names, 0, (mb_strlen($names) - 2));
        $names.= '.</p>';

        return $names;
    }

    private function get_hidden_students_inputs() : string 
    {
        $inputs = '';
        foreach($this->students as $student)
        {
            $inputs.= '<input type="hidden" name="'.STUDENT.'[]" ';
            $inputs.= 'value="'.$student->id.SEPARATOR.$student->fullname.'">';
        }

        return $inputs;
    }

    private function get_leader_header() : string 
    {
        return '<h3>'.get_string('leader', 'coursework').'</h3>';
    }

    private function get_leader_select() : string
    {
        $select = '<select id="leaderselect" name="'.TEACHER.'" onchange="change_leader_courses()" autocomplete="off" autofocus>';
        foreach($this->leaders as $leader)
        {
            if(empty($this->selectedLeaderId))
            {
                $this->selectedLeaderId = $leader->id;

                $this->leaderQuota = $this->get_leader_quota($leader);
            }

            $select.= "<option value='{$leader->teacherid}'>".$leader->lastname.' '.$leader->firstname.'</option>';
        }
        $select.= '</select>';

        return $select;
    }

    private function get_leader_quota(\stdClass $leader)
    {
        $quota = 0;

        foreach($leader->courses as $course)
        {
            $quota += $course->available_quota;
        }

        return $quota;
    }

    private function get_course_header() : string 
    {
        return '<h3>'.get_string('course', 'coursework').'</h3>';
    }

    private function get_course_select() : string
    {
        $select = '<select id="coursesselect" name="'.COURSE.'" autocomplete="off"';
        $select.= ' onchange="display_or_hide_expand_quota_panel_when_course_changes()">';
        foreach($this->leaders as $leader)
        {
            foreach($leader->courses as $course)
            {
                if($this->selectedLeaderId == $leader->id)
                {
                    $select.= "<option class='leadercourse' value='{$course->id}'>".$course->fullname.'</option>';
                }
            }
        }
        $select.= '</select>';

        return $select;
    }

    private function get_expand_quota_panel() : string 
    {
        $panel = '<div id="expandquotapanel" style="';
        if(count($this->students) > $this->leaderQuota) $panel.= 'display: block;';
        else $panel.= 'display: none;';
        $panel.= '">';

        $panel.= '<p><b>'.get_string('quota_exceeded', 'coursework').'</b></p>';
        $panel.= '<p><input type="radio" name="'.Main::EXPAND_QUOTA.'" value="'.true.'"> ';
        $panel.= get_string('expand_quota', 'coursework').'</p>';
        $panel.= '<p><input type="radio" name="'.Main::EXPAND_QUOTA.'" value="'.false.'" checked> ';
        $panel.= get_string('dont_change_quota', 'coursework').'</p>';

        $panel.= '</div>';

        return $panel;
    }

    private function get_buttons_panel() : string 
    {
        $panel = '<table><tr>';
        $panel.= '<td>'.$this->get_distribute_button().'</td>';
        $panel.= '<td>'.$this->get_back_button().'</td>';
        $panel.= '</tr></table>';
        return $panel;
    }

    private function get_distribute_button() : string 
    {
        return '<button>'.get_string('distribute', 'coursework').'</button>';
    }

    private function get_back_button() : string 
    {
        $btn = '<form method="post">';
        $btn.= '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.STUDENTS_DISTRIBUTION.'"/>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::OVERVIEW.'"/>';
        $btn.= '<button>'.get_string('back', 'coursework').'</button>';
        $btn.= '</form>';
        return $btn;
    }

    private function get_html_form_end() : string 
    {
        $form = '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.STUDENTS_DISTRIBUTION.'"/>';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.Main::GUI_TYPE.'" value="'.Main::OVERVIEW.'"/>';
        $form.= '<input type="hidden" name="'.Main::DATABASE_EVENT.'" value="'.Main::OVERVIEW.'"/>';
        foreach($this->students as $student)
        {
            $form.= '<input type="hidden" name="'.STUDENTS.'" value="'.$student->id.'[]"/>';
        }
        $form.= '</form>';

        return $form;
    }

    private function get_data_for_javascript() : string 
    {
        $jsdata = '';
        foreach($this->leaders as $leader) 
        {
            foreach($leader->courses as $course)
            {
                $jsdata.= "<p class='jsleaders' style='display: hidden' ";
                $jsdata.= "data-leaderid='{$leader->id}' ";
                $jsdata.= "data-courseid='{$course->id}' ";
                $jsdata.= "data-coursename='{$course->fullname}' ";
                $jsdata.= "data-quota='{$course->available_quota}' ";
                $jsdata.= "></p>";
            }
        }
        $jsdata.= '<p id="studentscount" style="display: hidden" data-count="'.count($this->students).'"></p>';

        return $jsdata;
    }

}

