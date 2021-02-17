<?php

use coursework_lib as cw;

class DistributeStudents 
{
    // Выводить предупреждение в случае о уже распределённых студентах
    private $course;
    private $cm;

    private $students;
    private $leaders;

    private $selectedLeaderId = 0;
    private $leaderQuota = 0;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;
        
        $this->students = cw\get_distribute_students();
        $this->leaders = cw\get_teachers($this->cm->instance);
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
        $leaders = $this->get_unique_leaders();

        $select = '<select id="leaderselect" name="'.TEACHER.'" onchange="change_leader_courses()" autocomplete="off" autofocus>';
        foreach($leaders as $leader)
        {
            if(empty($this->selectedLeaderId))
            {
                $this->selectedLeaderId = $leader->teacherid;
                $this->leaderQuota = cw\get_leader_available_quota($this->cm, $leader->teacherid, $leader->courseid);
            }

            $select.= "<option value='{$leader->teacherid}'>".$leader->fullname.'</option>';
        }
        $select.= '</select>';

        return $select;
    }

    private function get_unique_leaders() : array 
    {
        $uniqueLeaders = array();

        foreach($this->leaders as $leader)
        {
            $unique = true;

            foreach($uniqueLeaders as $uniqueLeader)
            {
                if($uniqueLeader->teacherid == $leader->teacherid)
                {
                    $unique = false;
                    break;
                }
            }

            if($unique) $uniqueLeaders[] = $leader;
        }

        return $uniqueLeaders;
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
            if($this->selectedLeaderId == $leader->teacherid)
            {
                $select.= "<option class='leadercourse' value='{$leader->courseid}'>".$leader->coursename.'</option>';
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
        $panel.= '<p><input type="radio" name="'.StudentsDistribution::EXPAND_QUOTA.'" value="'.true.'"> ';
        $panel.= get_string('expand_quota', 'coursework').'</p>';
        $panel.= '<p><input type="radio" name="'.StudentsDistribution::EXPAND_QUOTA.'" value="'.false.'" checked> ';
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
        $btn.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.StudentsDistribution::OVERVIEW.'"/>';
        $btn.= '<button>'.get_string('back', 'coursework').'</button>';
        $btn.= '</form>';
        return $btn;
    }

    private function get_html_form_end() : string 
    {
        $form = '<input type="hidden" name="'.CONFIG_MODULE.'" value="'.STUDENTS_DISTRIBUTION.'"/>';
        $form.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::GUI_TYPE.'" value="'.StudentsDistribution::OVERVIEW.'"/>';
        $form.= '<input type="hidden" name="'.ConfigurationManager::DATABASE_EVENT.'" value="'.StudentsDistribution::OVERVIEW.'"/>';
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
            $jsdata.= "<p class='jsleaders' style='display: hidden' ";
            $jsdata.= "data-leaderid='{$leader->teacherid}' ";
            $jsdata.= "data-courseid='{$leader->courseid}' ";
            $jsdata.= "data-coursename='{$leader->coursename}' ";
            $jsdata.= "data-quota='{$leader->quota}' ";
            $jsdata.= "></p>";
        }
        $jsdata.= '<p id="studentscount" style="display: hidden" data-count="'.count($this->students).'"></p>';

        return $jsdata;
    }

}

