<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Notification;

class ThemeSelect 
{
      
    private $course;
    private $cm;

    function __construct(\stdClass $course, \stdClass $cm)
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

        if($this->is_teacher_must_give_task())
        {
            $this->send_notification_to_teacher_give_task($student);
        }
        else 
        {
            $this->send_notification_to_teacher_theme_selected($student);
        }

        $this->log_event_student_chose_theme();
    }

    private function is_teacher_must_give_task() : bool 
    {
        $coursework = cg::get_coursework($this->cm->instance);

        if($coursework->usetask)
        {
            if($coursework->automatictaskobtaining)
            {
                return false;
            }
            else 
            {
                return true;
            }
        }
        else 
        {
            return false;
        }
    }

    private function get_coursework_student_row() : \stdClass 
    {
        $row = new \stdClass;
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
            $this->log_event_assign_default_task_to_student($row);
        }

        return $row;
    }

    private function get_coursework() : int 
    {
        $coursework = $this->cm->instance;
        if(empty($coursework)) throw new \Exception('Missing coursework id.');
        return $coursework;
    }

    private function get_student() : int 
    {
        global $USER;
        $student = $USER->id;
        if(empty($student)) throw new \Exception('Missing student id');
        return $student;
    }

    private function get_teacher() : int 
    {
        $teacher = optional_param(TEACHER, null, PARAM_INT);
        if(empty($teacher)) throw new \Exception('Missing teacher id.');
        return $teacher;
    }

    private function get_course() : int 
    {
        $course = optional_param(COURSE, null, PARAM_INT);
        if(empty($course)) throw new \Exception('Missing course id.');
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

    private function handle_exceptions(\stdClass $row) : void 
    {
        if($this->is_user_didnt_selected_theme($row))
        {
            throw new \Exception(get_string('e:missing_theme_and_own_theme', 'coursework'));
        }
        if($this->is_theme_already_used($row))
        {
            throw new \Exception(get_string('e:theme_already_used', 'coursework'));
        }
        if($this->is_teacher_quota_gone($row)
            && $this->is_it_not_theme_select_update($row))
        {
            throw new \Exception(get_string('e:teacher_quota_over', 'coursework'));
        }
    }

    private function is_teacher_quota_gone($student) : bool
    {
        $course = new \stdClass;
        $course->id = $student->course;

        $courses = tg::get_courses_with_quotas(
            $this->cm, 
            $student->teacher, 
            array($course)
        );

        if(reset($courses)->available_quota > 0)
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function is_user_didnt_selected_theme(\stdClass $row) : bool 
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

    private function is_theme_already_used(\stdClass $row) : bool 
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

    private function is_it_not_theme_select_update(\stdClass $row) : bool 
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

    private function is_student_row_exist(\stdClass $row) : bool 
    {
        global $DB;
        $where = array('coursework' => $row->coursework, 'student' => $row->student);
        return $DB->record_exists('coursework_students', $where);
    }

    private function get_student_row_id(\stdClass $row) : int 
    {
        global $DB;
        $where = array('coursework' => $row->coursework, 'student' => $row->student);
        return $DB->get_field('coursework_students', 'id', $where);
    }

    private function add_student_row(\stdClass $row) : void 
    {
        global $DB;
        if(!$DB->insert_record('coursework_students', $row)) 
        {
            throw new \Exception(get_string('e:student_didnt_choose_theme', 'coursework'));
        }
    }

    private function update_student_row(\stdClass $row) : void 
    {
        global $DB;
        if(!$DB->update_record('coursework_students', $row)) 
        {
            throw new \Exception(get_string('e:upd:student_not_selected', 'coursework'));
        }
    }

    private function send_notification_to_teacher_theme_selected(\stdClass $row) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($row->teacher); 
        $messageName = 'selecttheme';
        $messageText = $this->get_select_theme_html_message();

        $notification = new Notification(
            $cm,
            $course,
            $userFrom,
            $userTo,
            $messageName,
            $messageText
        );

        $notification->send();
    }

    private function send_notification_to_teacher_give_task(\stdClass $work) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($work->teacher); 
        $messageName = 'givetask';
        $giveTask = true;
        $messageText = $this->get_select_theme_html_message($giveTask);

        $notification = new Notification(
            $cm,
            $course,
            $userFrom,
            $userTo,
            $messageName,
            $messageText
        );

        $notification->send();
    }

    private function get_select_theme_html_message($giveTask = false) : string
    {
        $params = $this->get_data_for_teacher_message();
        $text = get_string('student_select_theme','coursework', $params);
        $message = \html_writer::tag('p', $text);

        if($giveTask)
        {
            $text = get_string('give_them_a_task','coursework', $params);
            $message.= \html_writer::tag('p', $text);
        }

        return $message;
    }

    private function get_data_for_teacher_message() : \stdClass 
    {
        global $USER;
        $data = new \stdClass;
        $data->student = cg::get_user_name($USER->id);
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
        $task = $DB->get_field('coursework_default_task_use', 'task', $where);
        if(empty($task)) throw new \Exception('Task template is absent.');
        return $task;
    }

    private function log_event_student_chose_theme() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_chose_theme::create($params);
        $event->trigger();
    }

    private function log_event_assign_default_task_to_student(\stdClass $work) : void 
    {
        $params = array
        (
            'relateduserid' => $work->student, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\default_task_assigned_to_student::create($params);
        $event->trigger();
    }



}
