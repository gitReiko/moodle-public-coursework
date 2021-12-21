<?php 

namespace Coursework\Support\DeleteStudentCoursework;

use Coursework\View\StudentWork\SaveFiles\StudentFileManager;
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
        $this->delete_students_courseworks();
    }

    private function delete_students_courseworks()
    {
        foreach($this->studentsId as $studentId)
        {
            $rowid = $this->get_student_row_id($studentId);
            $this->delete_student_coursework($rowid, $studentId);
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

    private function delete_student_coursework(int $rowid, int $studentId)
    {
        global $DB;
        if($DB->delete_records('coursework_students', array('id'=>$rowid)))
        {
            $this->delete_student_files($studentId);
            $this->delete_teacher_files($studentId);
            $this->send_message_to_student($studentId);
            $this->log_student_coursework_deleted($studentId);
        }
        else throw new Exception(get_string('e:student_not_deleted', 'coursework'));
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

    private function delete_student_files(int $studentId)
    {
        $this->delete_files_from_area('student', $studentId);
    }

    private function delete_teacher_files(int $studentId)
    {
        $this->delete_files_from_area('teacher', $studentId);
    }

    private function delete_files_from_area(string $area, int $itemid)
    { 
        $fs = get_file_storage();
        $context = \context_module::instance($this->cm->id);
        $files = $fs->get_area_files($context->id, 'mod_coursework', $area, $itemid);
        foreach($files as $file) 
        {
            if($file)
            {
                $file->delete();
            }
        }
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
