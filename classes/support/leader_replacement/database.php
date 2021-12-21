<?php 

namespace Coursework\Support\LeaderReplacement;

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
        return optional_param_array(Main::STUDENTS, array(), PARAM_INT);
    }

    private function get_leader() : int 
    {
        return optional_param(Main::TEACHER, null, PARAM_INT);
    }

    private function update_student_leader(int $studentId) : void 
    {
        $record = $this->get_new_coursework_students_row($studentId);

        global $DB;
        if($DB->update_record('coursework_students', $record))
        {
            $this->log_student_leader_replaced($studentId);
            $this->send_notification_to_student($studentId);
        }
        else throw new Exception(get_string('e:leader_hasnt_been_changed', 'coursework'));
    }

    private function get_new_coursework_students_row(int $studentId) : \stdClass 
    {
        $newRow = new \stdClass;
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


}
