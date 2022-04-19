<?php

namespace Coursework\Lib\Database;

use Coursework\Lib\Enums;

class AddNewStudentSectionStatus  
{
    private $newStatus;

    function __construct(int $courseworkId, int $studentId, int $sectionId, string $status)
    {
        $this->newStatus = $this->get_new_status(
            $courseworkId, 
            $studentId, 
            $sectionId, 
            $status 
        );
    }

    private function get_new_status(int $courseworkId, int $studentId, int $sectionId, string $status)
    {
        $newStatus = new \stdClass;
        $newStatus->coursework = $courseworkId;
        $newStatus->student = $studentId;
        $newStatus->type = Enums::SECTION;
        $newStatus->instance = $sectionId;
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
            $exception = 'Student task section status <<'.$this->newStatus->status;
            $exception.= '>> not added.';
            throw new \Exception($exception);
        }
    }
    
}
