<?php 

use coursework_lib as cw;

/**
 * @todo Test notification send
 */
class RemoveDistributionDatabaseEventsHandler 
{
    private $course;
    private $cm;
    private $studentsRowId;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentsRowId = optional_param_array(STUDENT.ROW.ID, null, PARAM_TEXT);
    }

    public function execute()
    {
        $this->remove_students_distribution();
    }

    private function remove_students_distribution()
    {
        foreach($this->studentsRowId as $rowid)
        {
            $studentId = $this->get_student_id($rowid);
            $this->remove_student($rowid, $studentId);
        }
    }

    private function get_student_id(int $rowid)
    {
        global $DB;
        $query = $DB->get_record('coursework_students', array('id'=> $rowid));
        return $query->student;
    }

    private function remove_student(int $rowid, int $studentId)
    {
        global $DB;
        if($DB->delete_records('coursework_students', array('id'=>$rowid)))
        {
            $this->send_notification_from_manager($studentId);
        }
        else throw new Exception(get_string('e:student-not-deleted', 'coursework'));
    }
    
    private function send_notification_from_manager(int $studentId) : void
    {
        try
        {
            if(empty($studentId)) throw new Exception(get_string('e:missing-student-id', 'coursework'));

            $messagename = 'selectionremoved';
            global $USER;
            $userfrom = $USER;
            $userto = $this->get_user_record($studentId);
            $headerMessage = get_string('selection_removed_header','coursework');
            $htmlMessage = $this->get_manager_html_message();

            cw\send_notification($this->cm, $this->course->id, $messagename, $userfrom, $userto, $headerMessage, $htmlMessage);
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

    private function get_manager_html_message() : string
    {
        $params = cw_prepare_data_for_message();
        $message = get_string('manager_message','coursework', $params);
        $notification = get_string('answer_not_require', 'coursework');

        return cw_get_html_message($this->cm, $this->course->id, $message, $notification);
    }

}
