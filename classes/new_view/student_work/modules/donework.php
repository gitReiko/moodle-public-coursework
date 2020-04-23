<?php

use coursework_lib as lib;

class DoneWork extends ViewModule 
{

    private $work;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->work = $this->get_student_work();
    }

    protected function get_module_name() : string
    {
        return 'donework';
    }

    protected function get_module_header() : string
    {
        return get_string('done_work', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = $this->get_leader();
        $body.= $this->get_course();
        $body.= $this->get_theme();
        
        return $body;
    }

    private function get_student_work() : stdClass 
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$this->studentId);
        return $DB->get_record('coursework_students', $where);
    }

    private function get_leader() : string 
    {
        $leader = '<p><b>'.get_string('leader', 'coursework').':</b> ';
        $user = lib\get_user($this->work->teacher);
        $leader.= lib\get_user_fullname($user);
        $leader.= '</p>';
        return $leader;
    }

    private function get_course() : string 
    {
        $course = '<p><b>'.get_string('course', 'coursework').':</b> ';
        $course.= lib\get_course_fullname($this->work->course);
        $course.= '</p>';
        return $course;
    }

    private function get_theme() : string 
    {
        $theme = '<p><b>'.get_string('theme', 'coursework').':</b> ';
        if(empty($this->work->theme))
        {
            $theme.= $this->work->owntheme;
        }
        else 
        {
            $theme.= lib\get_theme_name($this->work->theme);
        }
        $theme.= '</p>';
        return $theme;
    }



}

