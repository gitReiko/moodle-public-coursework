<?php

namespace Coursework\Lib\Database;

use Coursework\Lib\Enums;

class AddNewStudentWorkStatus  
{
    private $newStatus;

    function __construct(int $courseworkId, int $studentId, string $status)
    {
        $this->newStatus = $this->get_new_status(
            $courseworkId, 
            $studentId, 
            $status
        );
    }

    private function get_new_status(int $courseworkId, int $studentId, string $status)
    {
        $newStatus = new \stdClass;
        $newStatus->coursework = $courseworkId;
        $newStatus->student = $studentId;
        $newStatus->type = Enums::COURSEWORK;
        $newStatus->instance = $courseworkId;
        $newStatus->status = $status;
        $newStatus->changetime = time();

        return $newStatus;
    }

    public function execute()
    {
        global $DB;

        if($DB->insert_record('coursework_students_statuses', $this->newStatus)) 
        {
            return true;
        }
        else 
        {
            $exception = 'Student coursework state <<'.$this->newStatus->status;
            $exception.= '>> not added.';
            throw new \Exception($exception);
        }
    }
    
}
