<?php

class ManagerViewDatabaseEventHandler
{
    private $course;
    private $cm;

    private $eventType;
    private $studentRecord;

    public function execute() : void
    {
        if($this->eventType === DEL.STUDENT) $this->delete_student_from_database();
    }

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->eventType = optional_param(DB_EVENT, null, PARAM_TEXT);
        
        if($this->eventType)
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

            if(empty($record->student)) throw new Exception(get_string('e:view:constr:missing-coursework-student-record', 'coursework'));

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
            else throw new Exception(get_string('e:view:del:missing-student-record-id', 'coursework'));
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
            exit();
        }
    }

    private function delete_student_from_database() : void 
    {
        try
        {
            global $DB;
            if($DB->delete_records('coursework_students', array('id'=>$this->studentRecord->id)))
            {
                $this->send_notification_to_student();
            }
            else throw new Exception(get_string('e:view:del:student-not-deleted', 'coursework'));
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function send_notification_to_student() : void
    {
        try
        {
            if(empty($this->studentRecord->student)) throw new Exception(get_string('e:view:del:missing-student-id', 'coursework'));

            global $CFG, $USER;

            $message = new \core\message\message();
            $message->component = 'mod_coursework';
            $message->name = 'selectionremoved';
            $message->userfrom = $USER;
            $message->userto = $this->studentRecord->student;
            $message->subject = get_string('selectionremoved:head','coursework');
            $message->fullmessage = get_string('selectionremoved:body','coursework');
            $message->fullmessageformat = FORMAT_MARKDOWN;
            $message->fullmessagehtml = $this->get_html_message();
            $message->smallmessage = get_string('selectionremoved:head','coursework');
            $message->notification = '1';
            $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$this->cm->id;
            $message->contexturlname = cw_get_coursework_name($this->cm->instance);
            $message->courseid = $this->course->id;

            message_send($message);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function get_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('manager_message','coursework', $params);
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }



}


