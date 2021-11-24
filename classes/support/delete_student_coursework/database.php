<?php 

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

/**
 * @todo Test notification send
 */
class Database  
{
    private $course;
    private $cm;
    private $studentsRowId;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentsRowId = optional_param_array(Main::STUDENT_ROW_ID, null, PARAM_TEXT);
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
            $this->send_message_to_student($studentId);
        }
        else throw new Exception(get_string('e:student-not-deleted', 'coursework'));
    }

    private function send_message_to_student(int $studentId)
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($studentId); 
        $messageName = 'studentworkdeleted';
        $messageText = $this->get_message_text();

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
    
    private function get_message_text() : string
    {
        $params = Notification::get_sender_data();
        $message = get_string('manager_message','coursework', $params);
        $message.= get_string('answer_not_require', 'coursework');
        return $message;
    }

}
