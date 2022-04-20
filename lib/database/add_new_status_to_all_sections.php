<?php

namespace Coursework\Lib\Database;

require_once 'add_new_student_section_status.php';

class AddNewStatusToAllSections  
{
    private $studentWork;
    private $taskSections;
    private $newStatus;

    function __construct(\stdClass $studentWork, array $taskSections, string $newStatus)
    {
        $this->studentWork = $studentWork;
        $this->taskSections = $taskSections;
        $this->newStatus = $newStatus;
    }

    public function execute() : void 
    {
        foreach($this->taskSections as $section)
        {
            $addNewStatus = new AddNewStudentSectionStatus(
                $this->studentWork->coursework, 
                $this->studentWork->student, 
                $section->id,
                $this->newStatus 
            );
    
            $addNewStatus->execute();
        }
    }

}
