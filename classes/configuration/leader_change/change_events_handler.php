<?php 

use coursework_lib as lib;

class ChangeLeaderDBEventsHandler 
{
    private $course;
    private $cm;

    private $students;
    private $leader;

    function __construct(stdClass $course, stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->students = $this->get_students();
        $this->leader = $this->get_leader();
    }

    public function execute() 
    {
        foreach($this->students as $student)
        {
            $this->update_student_leader($student);
        }
    }

    private function get_students() : array 
    {
        return optional_param_array(STUDENTS, array(), PARAM_INT);
    }

    private function get_leader() : int 
    {
        return optional_param(TEACHER, null, PARAM_INT);
    }

    private function update_student_leader(int $studentId) : void 
    {
        $record = $this->get_new_coursework_students_row($studentId);

        global $DB;
        if($DB->update_record('coursework_students', $record))
        {
            $this->send_notification_to_student($studentId);
        }
        else throw new Exception(get_string('e-tc:leader_not_changed', 'coursework'));
    }

    private function get_new_coursework_students_row(int $studentId) : stdClass 
    {
        $newRow = new stdClass;
        $newRow->id = $this->get_coursework_students_row_id($studentId);
        $newRow->teacher = $this->leader;

        return $newRow;
    }

    private function get_coursework_students_row_id(int $studentId) : int 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance,
                            'student' => $studentId);

        return $DB->get_field('coursework_students', 'id', $conditions);
    }

    private function send_notification_to_student(int $studentId) : void
    {
        try
        {
            $userto = lib\get_user_record($studentId);
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
        $params = cw_prepare_data_for_message();
        $message = get_string('leader_changed_for_student','coursework');
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

    private function send_notification(stdClass $userto, string $headerMessage, string $htmlMessage) : void 
    {
        global $CFG, $USER;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = 'leader_changed';
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


}
