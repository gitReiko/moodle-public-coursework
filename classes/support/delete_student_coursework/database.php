<?php 

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class Database  
{
    private $course;
    private $cm;
    private $studentsId;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentsId = optional_param_array(Main::STUDENT_ID, null, PARAM_TEXT);
    }

    public function execute()
    {
        $this->remove_students_distribution();
    }

    private function remove_students_distribution()
    {
        foreach($this->studentsId as $studentId)
        {
            $rowid = $this->get_student_row_id($studentId);
            $this->remove_student($rowid, $studentId);
        }
    }

    private function get_student_row_id(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework'=> $this->cm->instance,
            'student' => $studentId
        );
        return $DB->get_field('coursework_students', 'id', $where);
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
