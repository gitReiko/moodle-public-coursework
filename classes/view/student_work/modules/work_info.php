<?php

use coursework_lib as lib;
use view_lib as view;

class WorkInfo extends ViewModule 
{

    private $work;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->work = $this->get_student_work();
    }

    protected function get_module_name() : string
    {
        return 'work_info';
    }

    protected function get_module_header() : string
    {
        return get_string('work_info', 'coursework');
    }

    protected function get_module_body() : string
    {
        return $this->get_info_table();
    }

    private function get_info_table() : string 
    {
        $table = '<table>';
        $table.= $this->get_leader_row();
        $table.= $this->get_course_row();
        $table.= $this->get_theme_row();
        $table.= $this->get_task_row();
        $table.= $this->get_status_row();
        $table.= $this->get_grade_row();
        $table.= '</table>';
        return $table;
    }

    private function get_student_work() : stdClass 
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 'student'=>$this->studentId);
        return $DB->get_record('coursework_students', $where);
    }

    private function get_leader_row() : string 
    {
        $leader = $this->get_leader_row_start();
        $leader.= '<td>'.$this->get_leader().'</td>';
        $leader.= '<td>'.$this->get_date($this->work->themeselectiondate).'</td>';
        $leader.= '</tr>';
        return $leader;
    }

    private function get_leader_row_start() : string 
    {
        if(empty($this->work->teacher))
        {
            return '<tr class="red-background">';
        }
        else
        {
            return '<tr class="green-background">';
        }
    }

    private function get_leader() : string 
    {
        $user = lib\get_user($this->work->teacher);
        $leader = '<b>'.get_string('leader', 'coursework').':</b>';
        $leader.= ' '.lib\get_user_fullname($user);
        return $leader;
    }

    private function get_date($date) : string 
    {
        if(empty($date))
        {
            return '';
        }
        else
        {
            return date('H:i d-m-Y', $date);
        }
    }

    private function get_course_row() : string 
    {
        $course = $this->get_course_row_start();
        $course.= '<td>'.$this->get_course().'</td>';
        $course.= '<td>'.$this->get_date($this->work->themeselectiondate).'</td>';
        $course.= '</tr>';
        return $course;
    }

    private function get_course_row_start() : string 
    {
        if(empty($this->work->course))
        {
            return '<tr class="red-background">';
        }
        else
        {
            return '<tr class="green-background">';
        }
    }

    private function get_course() : string 
    {
        $course = '<b>'.get_string('course', 'coursework').':</b> ';
        $course.= lib\get_course_fullname($this->work->course);
        return $course;
    }

    private function get_theme_row() : string 
    {
        $theme = $this->get_theme_row_start();
        $theme.= '<td>'.$this->get_theme().'</td>';
        $theme.= '<td>'.$this->get_date($this->work->themeselectiondate).'</td>';
        $theme.= '</tr>';
        return $theme;
    }

    private function get_theme() : string 
    {
        $theme = '<b>'.get_string('theme', 'coursework').':</b> ';
        if(empty($this->work->theme))
        {
            $theme.= $this->work->owntheme;
        }
        else 
        {
            $theme.= lib\get_theme_name($this->work->theme);
        }
        return $theme;
    }

    private function get_theme_row_start() : string 
    {
        if(empty($this->work->theme)
            && empty($this->work->owntheme))
        {
            return '<tr class="red-background">';
        }
        else
        {
            return '<tr class="green-background">';
        }
    }

    private function get_task_row() : string 
    {
        $task = $this->get_task_row_start();
        $task.= '<td>'.$this->get_task().'</td>';
        $task.= '<td>'.$this->get_date($this->work->receivingtaskdate).'</td>';
        $task.= '</tr>';
        return $task;
    }

    private function get_task_row_start() : string 
    {
        if(view\is_coursework_use_task($this->cm))
        {
            if(empty($this->work->task)) 
            {
                return '<tr class="red-background">';
            }
            else
            {
                return '<tr class="green-background">';
            }
        }
        else
        {
            return '<tr class="grey-background">';
        }
    }

    private function get_task() : string 
    {
        $task = '<b>'.get_string('task', 'coursework').':</b> ';

        if(empty($this->work->task)) 
        {
            $task.= get_string('not_received', 'coursework');
        }
        else
        {
            $task.= get_string('received', 'coursework');
        }
        return $task;
    }

    private function get_status_row() : string 
    {
        $status = $this->get_status_row_start();
        $status.= '<td>'.$this->get_status().'</td>';
        $status.= '<td>'.$this->get_date($this->work->workstatuschangedate).'</td>';
        $status.= '</tr>';
        return $status;
    }

    private function get_status_row_start() : string 
    {
        switch($this->work->status) 
        {
            case NOT_READY:
                return '<tr class="red-background">';
            case READY:
                return '<tr class="green-background">';
            case NEED_TO_FIX:
                return '<tr class="yellow-background">';
            case SENT_TO_CHECK:
                return '<tr class="blue-background">';
            default:
                return '<tr class="red-background">';
        }
    }

    private function get_status() : string 
    {
        $status = '<b>'.get_string('state', 'coursework').':</b> ';
        $status.= get_string($this->work->status, 'coursework');
        return $status;
    }

    private function get_grade_row() : string 
    {
        $grade = $this->get_grade_row_start().'<td>';
        $grade.= '<b>'.get_string('grade', 'coursework').':</b> ';
        $grade.= $this->work->grade;
        $grade.= '</td><td></td></tr>';
        return $grade;
    }
    
    private function get_grade_row_start() : string 
    {
        if(empty($this->work->grade))
        {
            return '<tr class="red-background">';
        }
        else
        {
            return '<tr class="green-background">';
        }
    }

}

