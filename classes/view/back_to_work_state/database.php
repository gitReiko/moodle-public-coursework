<?php

namespace view\back_to_work_state;

use Coursework\Lib\Getters\CommonGetter as cg;

class Database 
{
    private $cm;

    function __construct(\stdClass $cm) 
    {
        $this->cm = $cm;
    }

    public function change_state_to_work() : void  
    {
        $studentWork = new \stdClass;
        $studentWork->coursework = $this->get_coursework();
        $studentWork->student = $this->get_student();
        $studentWork->id = $this->get_id($studentWork);
        $studentWork->status = \NOT_READY;
        $studentWork->workstatuschangedate = time();

        if($this->is_student_work_exist($studentWork))
        {
            $this->return_to_work_state($studentWork);
        }
        else 
        {
            $this->display_missing_work_message();
        }
    }

    private function get_coursework() : int
    {
        $coursework = optional_param(Main::COURSEWORK_ID, null, PARAM_INT);

        if(empty($coursework)) throw new Exception('Missing coursework id.');

        return $coursework;
    }

    private function get_student() : int 
    {
        $student = optional_param(Main::STUDENT_ID, null, PARAM_INT);

        if(empty($student)) throw new Exception('Missing student id.');

        return $student;
    }

    private function get_id(\stdClass $studentWork) : int 
    {
        global $DB;

        $conditions = array 
        (
            'coursework' => $studentWork->coursework,
            'student' => $studentWork->student
        );

        return $DB->get_field('coursework_students', 'id', $conditions);
    }

    private function is_student_work_exist(\stdClass $studentWork) : bool 
    {
        if(empty($studentWork->id)) return false;
        else return true;
    }

    private function display_missing_work_message() : void  
    {
        $attr = array
        (
            'style' => 'border: 1px solid #ffa500; 
                        background: #fffbd2;
                        padding: 10px;'
        );
        $text = get_string('impossible_return_to_work_state', 'coursework');
        echo \html_writer::tag('p', $text, $attr);
    }

    private function return_to_work_state(\stdClass $studentWork) : void 
    {
        global $DB;

        if($DB->update_record('coursework_students', $studentWork))
        {
            $this->send_notification_to_student($studentWork->student);
            $this->log_event($studentWork->student);
            $this->display_coursework_back_to_work_state_message();
        }
        else 
        {
            throw new Exception('Student work wasn\'t returned to work state.');
        }
    }

    private function send_notification_to_student(int $studentId) : void
    {
        try
        {
            $userto = cg::get_user($studentId);
            $headerMessage = get_string('leader_changed_for_student','coursework');
            $htmlMessage = $this->get_student_html_message();

            $this->send_notification($userto, $headerMessage, $htmlMessage);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function get_student_html_message() : string 
    {
        global $COURSE;

        $params = cw_prepare_data_for_message();
        $message = get_string('coursework_returned_to_work_state','coursework');
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->cm->course, $message, $notification);
    }

    private function send_notification(\stdClass $userto, string $headerMessage, string $htmlMessage) : void 
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'back_to_work_state';
        $message->userfrom = $USER;
        $message->userto = $userto;
        $message->subject = $headerMessage;
        $message->fullmessage = $headerMessage;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $htmlMessage;
        $message->smallmessage = $headerMessage;
        $message->notification = '1';
        $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$this->cm->id;
        $message->contexturlname = cw_get_coursework_name($this->cm->instance);
        $message->courseid = $this->cm->course;

        message_send($message);
    }

    private function log_event($studentId)
    {
        $params = array
        (
            'relateduserid' => $studentId, 
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\student_coursework_back_to_work_state::create($params);
        $event->trigger();
    }

    private function display_coursework_back_to_work_state_message() : void  
    {
        $attr = array
        (
            'style' => 'border: 1px solid #5ac18e; 
                        background: #cbecc8;
                        padding: 10px;'
        );
        $text = get_string('student_coursework_back_to_work_state', 'coursework');
        echo \html_writer::tag('p', $text, $attr);
    }





}

