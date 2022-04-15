<?php

namespace Coursework\View\DatabaseHandlers\Lib;

use Coursework\Lib\Enums;

class SetStatusStartedToTaskSection 
{
    private $studentWork;
    private $taskSections;

    function __construct(\stdClass $studentWork, array $taskSections)
    {
        $this->studentWork = $studentWork;
        $this->taskSections = $taskSections;
    }

    public function execute() : void 
    {
        foreach($this->taskSections as $section)
        {
            $this->add_section_status(
                $this->get_section_status($section->id)
            );
        }
    }

    private function add_section_status(\stdClass $sectionStatus) : void 
    {
        global $DB;
        $DB->insert_record('coursework_students_statuses', $sectionStatus);
    }

    private function get_section_status(int $sectionId) : \stdClass 
    {
        $sectionStatus = new \stdClass;
        $sectionStatus->coursework = $this->studentWork->coursework;
        $sectionStatus->student = $this->studentWork->student;
        $sectionStatus->type = Enums::SECTION;
        $sectionStatus->instance = $sectionId;
        $sectionStatus->status = Enums::STARTED;
        $sectionStatus->changetime = time();
        return $sectionStatus;
    }

}
