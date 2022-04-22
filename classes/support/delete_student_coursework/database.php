<?php 

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\View\StudentWork\SaveFiles\StudentFileManager;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Cleaner;

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
        foreach($this->studentsId as $studentId)
        {
            $cleaner = new Cleaner($this->cm);
            $cleaner->delete_all_student_data($studentId);

            $this->send_message_to_student($studentId);
            $this->log_student_coursework_deleted($studentId);
        }
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

    private function log_student_coursework_deleted($studentId) : void 
    {
        $params = array
        (
            'relateduserid' => $studentId,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_coursework_deleted::create($params);
        $event->trigger();
    }

}
