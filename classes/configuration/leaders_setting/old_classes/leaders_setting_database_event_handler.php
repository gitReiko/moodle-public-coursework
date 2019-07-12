<?php

/**
 * Handle coursework_teachers database table.
 * 
 * @param stdClass $course instance of course db table
 * @param stdClass $cm instance of course_modules db table
 */
class LeadersSettingDatabaseEventHandler
{
    private $course;
    private $cm;

    public function execute() : void
    {
        $id = optional_param_array(COURSEWORK.TEACHERS.ID, array(), PARAM_INT);
        $teachers = optional_param_array(TEACHERS, array(), PARAM_INT);
        $courses = optional_param_array(COURSES, array(), PARAM_INT);
        $quotas = optional_param_array(QUOTAS, array(), PARAM_INT);
        $deleteEvent = optional_param(DEL.TEACHER, 0, PARAM_INT);

        for($i = 0; $i < count($teachers); $i++)
        {
            if(isset($id[$i]))
            {
                $this->update_teachers_row($id[$i], $teachers[$i], $courses[$i], $quotas[$i]);
            }
            else
            {
                $this->insert_teachers_row($teachers[$i], $courses[$i], $quotas[$i]);
            }
        }

        if($deleteEvent) $this->delete_teachers_row($deleteEvent);
    }

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    private function update_teachers_row(int $id, int $teacher, int $course, int $quota) : void
    {
        $record = new stdClass;
        $record->id = $id;
        $record->coursework = $this->cm->instance;
        $record->teacher = $teacher;
        $record->course = $course;
        $record->quota = $quota;

        global $DB;
        $DB->update_record('coursework_teachers', $record);
    }

    private function insert_teachers_row(int $teacher, int $course, int $quota) : void
    {
        try
        {
            if(!$teacher || !$course || !$quota) throw new Exception(get_string('e:no-teacher-necessary-data', 'coursework'));

            $temp = new stdClass;
            $temp->coursework = $this->cm->instance;
            $temp->teacher = $teacher;
            $temp->course = $course;
            $temp->quota = $quota;

            global $DB;
            $DB->insert_record('coursework_teachers', $temp, false);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function delete_teachers_row(int $rowID) : void
    {
        global $DB;
        $DB->delete_records('coursework_teachers', array('id'=>$rowID));
    }


}

