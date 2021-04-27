<?php

use Coursework\View\StudentWork\Locallib as locallib;
use coursework_lib as lib;
use view_lib as view;

class ThemeSelectDatabaseHandler 
{
      
    private $course;
    private $cm;

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    public function handle()
    {
        $student = $this->get_coursework_student_row();
        $this->handle_exceptions($student);

        if($this->is_student_row_exist($student))
        {
            $student->id = $this->get_student_row_id($student);
            $this->update_student_row($student);
        }
        else 
        {
            $this->add_student_row($student);
        }

        $work = $this->get_student_coursework($student);
        if(view\is_teacher_must_give_task($work))
        {
            $this->send_notification_to_teacher_give_task($student);
        }
        else 
        {
            $this->send_notification_to_teacher_theme_selected($student);
        }
    }

    private function get_coursework_student_row() : stdClass 
    {
        $row = new stdClass;
        $row->coursework = $this->get_coursework();
        $row->student = $this->get_student();
        $row->teacher = $this->get_teacher();
        $row->course = $this->get_course();
        $row->theme = $this->get_theme();
        $row->owntheme = $this->get_own_theme();
        $row->themeselectiondate = time();

        if($this->is_task_obtained_automatically())
        {
            $row->task = $this->get_coursework_task_template();
            $row->receivingtaskdate = time();
        }

        return $row;
    }

    private function get_coursework() : int 
    {
        $coursework = $this->cm->instance;
        if(empty($coursework)) throw new Exception('Missing coursework id.');
        return $coursework;
    }

    private function get_student() : int 
    {
        global $USER;
        $student = $USER->id;
        if(empty($student)) throw new Exception('Missing student id');
        return $student;
    }

    private function get_teacher() : int 
    {
        $teacher = optional_param(TEACHER, null, PARAM_INT);
        if(empty($teacher)) throw new Exception('Missing teacher id.');
        return $teacher;
    }

    private function get_course() : int 
    {
        $course = optional_param(COURSE, null, PARAM_INT);
        if(empty($course)) throw new Exception('Missing course id.');
        return $course;
    }

    private function get_theme()
    {
        $theme = optional_param(THEME, null, PARAM_INT);
        return $theme;
    }

    private function get_own_theme()
    {
        $theme = optional_param(OWN_THEME, null, PARAM_TEXT);
        return $theme;
    }

    private function handle_exceptions(stdClass $row) : void 
    {
        if($this->is_user_didnt_selected_theme($row))
        {
            throw new Exception(get_string('e:missing-theme-and-owntheme', 'coursework'));
        }
        if($this->is_theme_already_used($row))
        {
            throw new Exception(get_string('e:theme-already-used', 'coursework'));
        }
        if(lib\is_teacher_quota_gone($this->cm, $row->teacher, $row->course)
            && $this->is_it_not_theme_select_update($row))
        {
            throw new Exception(get_string('e:teacher-quota-over', 'coursework'));
        }
    }

    private function is_user_didnt_selected_theme(stdClass $row) : bool 
    {
        if(empty($row->theme) && empty($row->owntheme))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_theme_already_used(stdClass $row) : bool 
    {
        if(isset($row->theme))
        {
            global $DB;
            $students = locallib::get_students_list_for_in_query($this->cm);
            $sql = "SELECT id 
                    FROM {coursework_students}
                    WHERE coursework = ?
                    AND theme = ? 
                    AND student IN ($students)";
            $params = array($this->cm->instance, $row->theme);

            return $DB->record_exists_sql($sql, $params);
        }
        else
        {
            return false;
        }
    }

    private function is_it_not_theme_select_update(stdClass $row) : bool 
    {
        global $DB;
        $where = array
        (
            'coursework' => $row->coursework, 
            'student' => $row->student,
            'teacher' => $row->teacher,
            'course' => $row->course,
        );
        return !$DB->record_exists('coursework_students', $where);
    }

    private function is_student_row_exist(stdClass $row) : bool 
    {
        global $DB;
        $where = array('coursework' => $row->coursework, 'student' => $row->student);
        return $DB->record_exists('coursework_students', $where);
    }

    private function get_student_row_id(stdClass $row) : int 
    {
        global $DB;
        $where = array('coursework' => $row->coursework, 'student' => $row->student);
        return $DB->get_field('coursework_students', 'id', $where);
    }

    private function add_student_row(stdClass $row) : void 
    {
        global $DB;
        if(!$DB->insert_record('coursework_students', $row)) 
        {
            throw new Exception(get_string('e:ins:student-not-selected', 'coursework'));
        }
    }

    private function update_student_row(stdClass $row) : void 
    {
        global $DB;
        if(!$DB->update_record('coursework_students', $row)) 
        {
            throw new Exception(get_string('e:upd:student-not-selected', 'coursework'));
        }
    }

    private function send_notification_to_teacher_theme_selected(stdClass $row) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'selecttheme';
        $userFrom = $USER;
        $userTo = lib\get_user($row->teacher); 
        $headerMessage = get_string('theme_selection_header','coursework');
        $fullMessageHtml = $this->get_select_theme_html_message();

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);
    }

    private function send_notification_to_teacher_give_task(stdClass $work) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $messageName = 'givetask';
        $userFrom = $USER;
        $userTo = lib\get_user($work->teacher); 
        $headerMessage = get_string('give_task_header','coursework');
        $giveTask = true;
        $fullMessageHtml = $this->get_select_theme_html_message($giveTask);

        lib\send_notification($cm, $course, $messageName, $userFrom, $userTo, $headerMessage, $fullMessageHtml);
    }

    private function get_select_theme_html_message($giveTask = false) : string
    {
        $params = $this->get_data_for_teacher_message();
        $message = '<p>'.get_string('student_select_theme','coursework', $params).'</p>';

        if($giveTask)
        {
            $message.= '<p>'.get_string('give_them_a_task','coursework', $params).'</p>';
        }

        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    private function get_data_for_teacher_message() : stdClass 
    {
        global $USER;
        $data = new stdClass;
        $data->student = cw_get_user_name($USER->id);
        $data->date = date('d-m-Y');
        $data->time = date('G:i');
        return $data;
    }

    private function is_task_obtained_automatically() : bool 
    {
        global $DB;
        $where = array('id'=>$this->cm->instance, 'usetask'=>1, 'automatictaskobtaining'=>1);
        return $DB->record_exists('coursework', $where);
    }

    private function get_coursework_task_template() : int 
    {
        global $DB;
        $where = array('coursework' => $this->cm->instance);
        $task = $DB->get_field('coursework_tasks_using', 'task', $where);
        if(empty($task)) throw new Exception('Task template is absent.');
        return $task;
    }

    private function get_student_coursework($row) : stdClass
    {
        global $DB;
        $where = array('coursework' => $row->coursework, 'student' => $row->student);
        return $DB->get_record('coursework_students', $where);
    }



}
