<?php 

namespace Coursework\Lib;

class Cleaner 
{
    private $courseworkId;

    function __construct(int $courseworkId)
    {
        $this->courseworkId = $courseworkId;
    }

    public function delete_all_student_data(int $studentId)
    {
        $this->delete_student_custom_task($studentId);
        $this->delete_from_coursework_students($studentId);
        $this->delete_from_coursework_students_statuses($studentId);
        $this->delete_student_messages_from_chat($studentId);
        $this->delete_teacher_messages_from_chat($studentId);
        $this->delete_student_attached_files($studentId);
        $this->delete_teacher_attached_files($studentId);
    }

    private function delete_student_custom_task(int $studentId)
    {
        $taskId = $this->get_student_task($studentId);

        if($this->is_task_custom($taskId))
        {
            $this->delete_custom_task_sections($taskId);
            $this->delete_custom_task($taskId);
        }
    }

    private function get_student_task(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework'=> $this->courseworkId,
            'student' => $studentId
        );
        return $DB->get_field('coursework_students', 'task', $where);
    }

    private function is_task_custom(int $taskId) : bool
    {
        global $DB;
        $where = array(
            'id' => $taskId,
            'template' => 0
        );
        return $DB->record_exists('coursework_tasks', $where);
    }

    private function delete_custom_task_sections(int $taskId)
    {
        global $DB;
        $where = array('task' => $taskId);
        return $DB->delete_records('coursework_tasks_sections', $where);
    }

    private function delete_custom_task(int $taskId)
    {
        global $DB;
        $where = array('id' => $taskId);
        return $DB->delete_records('coursework_tasks', $where);
    }

    private function delete_from_coursework_students(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'student' => $studentId
        );
        return $DB->delete_records('coursework_students', $where);
    }

    private function delete_from_coursework_students_statuses(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'student' => $studentId
        );
        return $DB->delete_records('coursework_students_statuses', $where);
    }

    private function delete_student_messages_from_chat(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'userfrom' => $studentId
        );
        return $DB->delete_records('coursework_chat', $where);
    }

    private function delete_teacher_messages_from_chat(int $studentId)
    {
        global $DB;
        $where = array(
            'coursework' => $this->courseworkId,
            'userto' => $studentId
        );
        return $DB->delete_records('coursework_chat', $where);
    }

    private function delete_student_attached_files(int $studentId)
    {
        $this->delete_files_from_area('student', $studentId);
    }

    private function delete_teacher_attached_files(int $studentId)
    {
        $this->delete_files_from_area('teacher', $studentId);
    }

    private function delete_files_from_area(string $area, int $itemid)
    { 
        $fs = get_file_storage();
        $context = \context_module::instance($this->courseworkId);
        $files = $fs->get_area_files($context->id, 'mod_coursework', $area, $itemid);
        foreach($files as $file) 
        {
            if($file)
            {
                $file->delete();
            }
        }
    }

}
