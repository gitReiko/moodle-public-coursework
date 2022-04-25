<?php 

namespace Coursework\Support\ReturnToThemeSelection;

use Coursework\Classes\Lib\StudentsMassActions\StudentsTable as sma;
use Coursework\Lib\Database\AddNewStudentWorkStatus;
use Coursework\Lib\Getters\StudentsGetter as sg;
use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Notification;
use Coursework\Lib\Feedbacker;
use Coursework\Lib\Enums;

class Database 
{
    private $course;
    private $cm;

    private $studentsIds;
    private $leader;

    function __construct(\stdClass $course, \stdClass $cm) 
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->studentsIds = $this->get_students_ids();
    }

    public function execute() : string  
    {
        $feedback = '';

        foreach($this->studentsIds as $studentId)
        {
            $feedbackItem = $this->remove_student_theme_selection($studentId);
            $feedback.= Feedbacker::add_feedback_to_string($feedback, $feedbackItem);
        }

        return $feedback;
    }

    private function get_students_ids() : array 
    {
        $requestItems = optional_param_array(sma::STUDENTS, array(), PARAM_TEXT);

        $studentsIds = array();

        foreach($requestItems as $requestItem)
        {
            $chunks = explode(sma::SEPARATOR, $requestItem);
            $studentsIds[] = $chunks[0];
        }

        return $studentsIds;
    }

    private function remove_student_theme_selection(int $studentId) : \stdClass 
    {
        $work = sg::get_student_work($this->cm->instance, $studentId);
        $work->theme = null;
        $work->owntheme = null;

        global $DB;
        if($DB->update_record('coursework_students', $work))
        {
            $this->add_theme_reselection_status($work);
            $this->send_notification_to_student($studentId);
            $this->log_theme_selection_deleted($studentId);
            return $this->get_success_feedback($studentId);
        }
        else 
        {
            return $this->get_fail_feedback($studentId);
        }
    }

    private function add_theme_reselection_status(\stdClass $work)
    {
        $addNewStatus = new AddNewStudentWorkStatus(
            $work->coursework, 
            $work->student, 
            Enums::THEME_RESELECTION 
        );
        $addNewStatus->execute();
    }

    private function send_notification_to_student(int $studentId) : void 
    {
        global $USER;

        $cm = $this->cm;
        $course = $this->course;
        $userFrom = $USER;
        $userTo = cg::get_user($studentId); 
        $messageName = 'themeselectiondeleted';
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
        $message = get_string('theme_selection_deleted_reselect','coursework', $params);
        $message.= get_string('answer_not_require', 'coursework');
        return $message;
    }

    private function log_theme_selection_deleted($studentId) : void 
    {
        $params = array
        (
            'relateduserid' => $studentId,
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\theme_selection_deleted::create($params);
        $event->trigger();
    }

    private function get_success_feedback(int $studentId) : \stdClass  
    {
        $studentName = cg::get_user_name($studentId);
        $text = get_string('theme_selection_successfully_deleted', 'coursework', $studentName);
        return Feedbacker::get_success_feedback($text);
    }

    private function get_fail_feedback(int $studentId) : \stdClass  
    {
        $studentName = cg::get_user_name($studentId);
        $text = 'Student theme selection not removed (.'.$studentName.')';
        return Feedbacker::get_fail_feedback($text);
    }


}
