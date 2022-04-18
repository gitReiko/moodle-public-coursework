<?php

namespace Coursework\View\DatabaseHandlers;

use Coursework\View\DatabaseHandlers\Lib\SetStatusStartedToTaskSections;
use Coursework\View\DatabaseHandlers\Lib\AddNewStudentWorkStatus;
use Coursework\View\DatabaseHandlers\Main as MainDB;
use Coursework\View\StudentWork\Locallib as locallib;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Notification;
use Coursework\Lib\Enums;

class SelectTheme
{
    private $course;
    private $cm;
    private $coursework;
    private $studentWork;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->coursework = cg::get_coursework($this->cm->instance);
        $this->studentWork = $this->get_student_work();
    }

    public function handle()
    {
        $this->handle_exceptions();

        if($this->update_student_work())
        {
            $this->add_theme_selection_status_to_student();

            if($this->is_neccessary_assign_task())
            {
                $this->add_task_receipt_status_to_student();

                if($this->is_teacher_must_give_task())
                {
                    $this->send_notification_give_task_to_teacher();
                }
                else 
                {
                    $this->add_started_status_to_student_work();
                    $this->set_status_started_to_task_sections();
                    $this->log_event_assign_default_task_to_student();
                }
            }
            else 
            {
                $this->add_started_status_to_student_work();
            }
    
            $this->send_notification_theme_selected_to_teacher();
            $this->log_event_student_chose_theme();
        }
    }

    private function get_student_work() : \stdClass 
    {
        $work = new \stdClass;
        $work->coursework = $this->get_coursework_id();
        $work->student = $this->get_student_id();
        $work->teacher = $this->get_teacher_id();
        $work->course = $this->get_course_id();
        $work->theme = $this->get_theme_id();
        $work->owntheme = $this->get_own_theme();

        $work->id = $this->get_student_work_id($work);

        if($this->is_task_assigned_automatically())
        {
            $work->task = $this->get_default_task_id();
        }

        return $work;
    }

    private function get_coursework_id() : int 
    {
        $courseworkId = $this->cm->instance;
        if(empty($courseworkId)) throw new \Exception('Missing coursework id.');
        return $courseworkId;
    }

    private function get_student_id() : int 
    {
        global $USER;
        if(empty($USER->id)) throw new \Exception('Missing student id');
        return $USER->id;
    }

    private function get_teacher_id() : int 
    {
        $teacher = optional_param(MainDB::TEACHER, null, PARAM_INT);
        if(empty($teacher)) throw new \Exception('Missing teacher id.');
        return $teacher;
    }

    private function get_course_id() : int 
    {
        $course = optional_param(MainDB::COURSE, null, PARAM_INT);
        if(empty($course)) throw new \Exception('Missing course id.');
        return $course;
    }

    private function get_theme_id()
    {
        $theme = optional_param(MainDB::THEME, null, PARAM_INT);
        return $theme;
    }

    private function get_own_theme()
    {
        return optional_param(MainDB::OWN_THEME, null, PARAM_TEXT);
    }

    private function get_student_work_id(\stdClass $work) : int 
    {
        global $DB;
        $where = array('coursework' => $work->coursework, 'student' => $work->student);
        return $DB->get_field('coursework_students', 'id', $where);
    }

    private function is_task_assigned_automatically() : bool 
    {
        if($this->is_neccessary_assign_task() && (!$this->is_teacher_must_give_task()))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_neccessary_assign_task() : bool 
    {
        if($this->coursework->usetask)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_teacher_must_give_task() : bool 
    {
        if($this->coursework->autotaskissuance)
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function get_default_task_id() : int 
    {
        $defaultTask = cg::get_default_coursework_task($this->cm);
        if(empty($defaultTask->id)) throw new \Exception('Task template is missing.');
        return $defaultTask->id;
    }

    private function handle_exceptions() : void 
    {
        if($this->is_user_didnt_selected_theme())
        {
            throw new \Exception(get_string('e:missing_theme_and_own_theme', 'coursework'));
        }
        if($this->is_theme_already_used())
        {
            throw new \Exception(get_string('e:theme_already_used', 'coursework'));
        }
        if($this->is_teacher_quota_gone() && $this->is_it_not_theme_select_update())
        {
            throw new \Exception(get_string('e:teacher_quota_over', 'coursework'));
        }
    }

    private function is_user_didnt_selected_theme() : bool 
    {
        if(empty($this->studentWork->theme) && empty($this->studentWork->owntheme))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function is_theme_already_used() : bool 
    {
        if(empty($this->studentWork->theme))
        {
            return false;
        }
        else 
        {
            $availableCountOfUsages = locallib::get_count_of_same_themes(
                $this->cm->instance, 
                $this->studentWork->course
            );
    
            $students = locallib::get_students_list_for_in_query($this->cm);
            $usagesCount = locallib::get_count_of_theme_usages(
                $this->cm->instance, 
                $this->studentWork->theme, 
                $students
            );
    
            return !locallib::is_theme_not_used($usagesCount, $availableCountOfUsages);
        }
    }

    private function is_teacher_quota_gone() : bool
    {
        $course = new \stdClass;
        $course->id = $this->studentWork->course;

        $courses = tg::get_courses_with_quotas(
            $this->cm, 
            $this->studentWork->teacher, 
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

    private function is_it_not_theme_select_update() : bool 
    {
        global $DB;
        $where = array
        (
            'coursework' => $this->studentWork->coursework, 
            'student' => $this->studentWork->student,
            'teacher' => $this->studentWork->teacher,
            'course' => $this->studentWork->course,
        );
        return !$DB->record_exists('coursework_students', $where);
    }

    private function update_student_work() : bool
    {
        global $DB;

        if($this->is_student_work_exists())
        {
            if($DB->update_record('coursework_students', $this->studentWork)) 
            {
                return true;
            }
            else 
            {
                throw new \Exception(
                    get_string('e:upd:student_not_selected', 'coursework')
                );
            }
            
        }
        else 
        {
            if($DB->insert_record('coursework_students', $this->studentWork)) 
            {
                return true;
            }
            else 
            {
                throw new \Exception(
                    get_string('e:student_didnt_choose_theme', 'coursework')
                );
            }
        }
    }

    private function is_student_work_exists() : bool 
    {
        if($this->studentWork->id)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function add_theme_selection_status_to_student()
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            Enums::THEME_SELECTION 
        );
        $addNewStatus->execute();
    }

    private function send_notification_give_task_to_teacher() : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($this->studentWork->teacher); 
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

    private function add_task_receipt_status_to_student()
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            Enums::TASK_RECEIPT 
        );
        $addNewStatus->execute();
    }

    private function add_started_status_to_student_work()
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $this->studentWork->coursework, 
            $this->studentWork->student, 
            Enums::STARTED 
        );
        $addNewStatus->execute();
    }

    private function set_status_started_to_task_sections() : void 
    {
        $setStatusStartedToTaskSections = new SetStatusStartedToTaskSections(
            $this->studentWork,
            cg::get_task_sections($this->studentWork->task)
        );
        $setStatusStartedToTaskSections->execute();
    }

    private function log_event_assign_default_task_to_student() : void 
    {
        $params = array
        (
            'relateduserid' => $this->studentWork->student, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\default_task_assigned_to_student::create($params);
        $event->trigger();
    }

    private function send_notification_theme_selected_to_teacher() : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($this->studentWork->teacher); 
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

    private function log_event_student_chose_theme() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_chose_theme::create($params);
        $event->trigger();
    }


}
