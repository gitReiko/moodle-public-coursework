<?php

/**
 * Handles database events of manager view.
 * 
 * Currently there is only one type of event (student deletion).
 * 
 * @param stdClass $course - moodle course
 * @param stdClass $cm - moodle course module
 * @param string $eventType
 * @param stdClass $studentRecord - record of coursework_students db table
 * @author Denis Makouski (Reiko)
 */
class ViewDatabaseEventHandler
{
    private $course;
    private $cm;

    private $eventType;
    private $studentRecord;

    public function execute() : void
    {
        if($this->eventType === DEL.STUDENT) $this->delete_student_from_database();
        if($this->eventType === UPDATE.STUDENT) $this->update_student_grade_and_comment();
    }

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->eventType = optional_param(DB_EVENT, null, PARAM_TEXT);
        
        if($this->eventType === UPDATE.STUDENT)
        {
            $this->studentRecord = $this->get_student_record();
            $this->add_new_grade_and_comment_to_student_record();
        }
        else if($this->eventType === DEL.STUDENT)
        {
            $this->studentRecord = $this->get_student_record();
        }
    }

    private function get_student_record() : stdClass 
    {
        try
        {
            $recordID = $this->get_student_record_id();

            global $DB;
            $record = $DB->get_record('coursework_students', array('id'=>$recordID));

            if(empty($record->student)) throw new Exception(get_string('e:missing-coursework-student-record', 'coursework'));

            return $record;
        }
        catch(Eception $e)
        {
            cw_print_error_message($e->getMessage());
            exit();
        }
    }

    private function get_student_record_id() : int 
    {
        try
        {
            $recordID = optional_param(RECORD.ID, 0, PARAM_INT);

            if($recordID) return $recordID;
            else throw new Exception(get_string('e:missing-student-record-id', 'coursework'));
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
            exit();
        }
    }

    private function add_new_grade_and_comment_to_student_record()
    {
        try
        {
            $grade = optional_param(GRADE, null, PARAM_TEXT);
            $comment = optional_param(COMMENT, null, PARAM_TEXT);

            if(empty($grade) && empty($comment)) throw new Exception(get_string('e:missing-grade-and-comment', 'coursework'));

            if(isset($this->studentRecord->grade)) $this->studentRecord->grade = $grade;
            if(isset($this->studentRecord->comment)) $this->studentRecord->comment = $comment;
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
            exit();
        }
    }

    private function update_student_grade_and_comment() : void
    {
        try
        {
            $record = new stdClass;
            $record->id = $this->studentRecord->id;
            $record->grade = $this->studentRecord->grade;
            $record->comment = $this->studentRecord->comment;

            global $DB;
            if($DB->update_record('coursework_students', $record))
            {
                $this->send_notification_from_tutor();
            }
            else throw new Exception(get_string('e:student-not-updated', 'coursework'));
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function delete_student_from_database() : void 
    {
        try
        {
            global $DB;
            if($DB->delete_records('coursework_students', array('id'=>$this->studentRecord->id)))
            {
                $this->send_notification_from_manager();
            }
            else throw new Exception(get_string('e:student-not-deleted', 'coursework'));
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function send_notification_from_tutor() : void
    {
        try
        {
            if(empty($this->studentRecord->student)) throw new Exception(get_string('e:missing-student-id', 'coursework'));

            $userto = $this->get_user_record($this->studentRecord->student);
            $headerMessage = get_string('studentgraded:head','coursework');
            $htmlMessage = $this->get_tutor_html_message();

            $this->send_notification($userto, $headerMessage, $htmlMessage);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function send_notification_from_manager() : void
    {
        try
        {
            if(empty($this->studentRecord->student)) throw new Exception(get_string('e:missing-student-id', 'coursework'));

            $userto = $this->get_user_record($this->studentRecord->student);
            $headerMessage = get_string('selectionremoved:body','coursework');
            $htmlMessage = $this->get_manager_html_message();

            $this->send_notification($userto, $headerMessage, $htmlMessage);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function get_user_record(int $userID) : stdClass
    {
        try
        {
            global $DB;
            $user = $DB->get_record('user', array('id'=>$userID));

            if(empty($user->id)) throw new Exception(get_string('e:missing-user-record', 'coursework'));

            return $user;
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function send_notification(stdClass $userto, string $headerMessage, string $htmlMessage) : void 
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'selectionremoved';
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
        $message->courseid = $this->course->id;

        message_send($message);
    }

    private function get_tutor_html_message() : string 
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('tutor_message','coursework', $params);
        $notification = get_string('grade_isnt_final', 'coursework');
        $notification.= get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    private function get_manager_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('manager_message','coursework', $params);
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }
  


}


