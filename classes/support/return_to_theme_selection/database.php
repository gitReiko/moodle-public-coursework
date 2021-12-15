<?php 

namespace Coursework\Support\ReturnToThemeSelection;

use Coursework\ClassesLib\StudentsMassActions\StudentsTable as sma;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;

class Database 
{
    private $course;
    private $cm;

    private $students;
    private $leader;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->students = $this->get_students();
    }

    public function execute() 
    {
        foreach($this->students as $student)
        {
            $this->remove_student_theme_selection($student);
        }
    }

    private function get_students() : array 
    {
        return optional_param_array(sma::STUDENTS, array(), PARAM_INT);
    }

    private function remove_student_theme_selection(int $studentId) : void 
    {
        $student = sg::get_students_work($this->cm->instance, $studentId);
        $student->theme = null;
        $student->owntheme = null;

        global $DB;
        if($DB->update_record('coursework_students', $student))
        {
            /*
            // print success message in top of page
            $this->log_student_leader_replaced($studentId);
            $this->send_notification_to_student($studentId);
            */
        }
        else throw new Exception('Student theme selection not removed.');
    }

    /*
    private function send_notification_to_student(int $studentId) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($studentId); 
        $messageName = 'leaderreplaced';
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
        $message = get_string('leader_changed_for_student','coursework', $params);
        $message.= get_string('answer_not_require', 'coursework');
        return $message;
    }

    private function log_student_leader_replaced($studentId) : void 
    {
        $params = array
        (
            'relateduserid' => $studentId,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\student_leader_replaced::create($params);
        $event->trigger();
    }

    */


}