<?php

require_once 'data_getters/students_works_getter.php';

use coursework_lib as lib;

class StudentsWorksMain 
{
    private $course;
    private $cm;

    private $works;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $getter = new StudentsWorksGetter($this->course, $this->cm);
        $this->works = $getter->get_students_works();
    }

    public function get_page() : string 
    {
        $page = $this->get_page_header();
        $page.= $this->get_students_list();
        $page.= lib\get_back_to_course_button($this->course->id);
        return $page;
    }

    private function get_page_header() : string 
    {
        return '<h3>'.get_string('student_works_list_header', 'coursework').'</h3>';
    }

    private function get_students_list() : string 
    {
        $list = '<table class="students_works">';
        $list.= $this->get_students_list_header();
        $list.= $this->get_students_list_body();
        $list.= '</table>';
        return $list;
    }

    private function get_students_list_header() : string 
    {
        $header = '<thead><tr>';
        $header.= $this->get_student_header();
        $header.= $this->get_leader_header();
        $header.= $this->get_course_header();
        $header.= $this->get_theme_header();
        $header.= '<td></td>';
        $header.= '</tr></thead>';
        return $header;
    }

    private function get_student_header() : string 
    {
        return '<td>'.get_string('student', 'coursework').'</td>';
    }

    private function get_leader_header() : string 
    {
        return '<td title="'.get_string('leader', 'coursework').'">'.get_string('leader_short', 'coursework').'</td>';
    }

    private function get_course_header() : string 
    {
        return '<td title="'.get_string('course', 'coursework').'">'.get_string('course_short', 'coursework').'</td>';
    }

    private function get_theme_header() : string 
    {
        return '<td title="'.get_string('theme', 'coursework').'">'.get_string('theme_short', 'coursework').'</td>';
    }

    private function get_students_list_body() : string 
    {
        $body = '';
        foreach($this->works as $work)
        {
            $body.= '<tr>';
            $body.= $this->get_student_body_cell($work);
            $body.= $this->get_teacher_body_cell($work);
            $body.= $this->get_course_body_cell($work);
            $body.= $this->get_theme_body_cell($work);
            $body.= $this->get_go_to_page_cell($work);
            $body.= '</tr>';
        }
        return $body;
    }

    private function get_student_body_cell(stdClass $work) : string 
    {
        $td = "<td title='{$work->studentFullName}'>";
        $td.= cw_get_user_photo($work->studentId);
        $td.= "<span title='{$work->studentFullName}'>";
        $td.= $work->studentShortName;
        $td.= '</span>';
        $td.= '</td>';
        return $td;
    }

    private function get_teacher_body_cell(stdClass $work) : string 
    {
        if(empty($work->teacherId))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->teacherFullName;
        }

        $td = "<td class='";
        if(empty($work->teacherId)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_course_body_cell(stdClass $work) : string 
    {
        if(empty($work->courseId))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->courseName;
        }

        $td = "<td class='";
        if(empty($work->courseId)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_theme_body_cell(stdClass $work) : string 
    {
        if(empty($work->themeName))
        {
            $title = get_string('not_selected', 'coursework');
        }
        else 
        {
            $title = $work->themeName;
        }

        $td = "<td class='";
        if(empty($work->themeName)) $td.= 'red-background';
        else $td.= 'green-background';
        $td.= "' title='{$title}'>";
        $td.= '</td>';
        return $td;
    }

    private function get_go_to_page_cell(stdClass $work) : string 
    {
        $link = '/mod/coursework/view.php?id='.$this->cm->id;
        $link.= '&'.ViewMain::GUI_EVENT.'='.ViewMain::USER_WORK;
        $link.= '&'.STUDENT.ID.'='.$work->studentId;
        $td = '<td>';
        $td.= "<a href='$link' >";
        $td.= '<button>'.get_string('go_to_work', 'coursework').'</button>';
        $td.= '</a>';
        $td.= '</td>';
        return $td;
    }


}