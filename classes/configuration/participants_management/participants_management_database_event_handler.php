<?php

/**
 * Inserts, updates and deletes coursework_groups ans coursework_tutors database tables.
 * 
 * @param stdClass $course - record of course Moodle database table
 * @param stdClass $cm - record of course_modules Moodle database table
 * @return void
 */
class ParticipantsManagementDatabaseEventHandler
{
    private $course;
    private $cm;

    public function execute() : void
    {
        $this->handle_coursework_groups_database_table();
        $this->handle_coursework_tutors_database_table();
    }

    function __construct(stdClass $course, stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
    }

    private function handle_coursework_groups_database_table() : void
    {
        $groups = optional_param_array(GROUPS, array(), PARAM_INT);

        foreach($groups as $group)
        {
            if($this->is_group_record_doesnt_exists($group))
            {
                $this->insert_group_record($group);
            }
        }

        $this->delete_unselected_in_gui_group_records($groups);
    }

    private function is_group_record_doesnt_exists(int $group) : bool
    {
        global $DB;
        $conditions = array('coursework'=>$this->cm->instance, 'groupid' => $group);
        if($DB->record_exists('coursework_groups', $conditions)) return false;
        else return true;
    }

    private function insert_group_record(int $group) : void
    {
        try
        {
            if(empty($group)) throw new Exception(get_string('e:missing-group-id', 'coursework'));

            $temp = new stdClass;
            $temp->coursework = $this->cm->instance;
            $temp->groupid = $group;

            global $DB;
            $DB->insert_record('coursework_groups', $temp, false);  
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function delete_unselected_in_gui_group_records(array $groups) : void  // Не реализовано удаление студентов, зависимых от группы
    {
        global $DB;

        $rows = $DB->get_records('coursework_groups', array('coursework'=>$this->cm->instance));

        foreach($rows as $row)
        {
            $is_used = false;

            foreach($groups as $group)
            {
                if($row->groupid == $group) $is_used = true;
            }

            if(!$is_used)
            {
                $DB->delete_records('coursework_groups', array('id'=>$row->id));
            }
        }
    }

    private function handle_coursework_tutors_database_table() : void
    {
        $id = optional_param_array(COURSEWORK.TUTORS.ID, array(), PARAM_INT);
        $tutors = optional_param_array(TUTORS, array(), PARAM_INT);
        $courses = optional_param_array(COURSES, array(), PARAM_INT);
        $quotas = optional_param_array(QUOTAS, array(), PARAM_INT);
        $deleteEvent = optional_param(DEL.TUTOR, 0, PARAM_INT);

        for($i = 0; $i < count($tutors); $i++)
        {
            if(isset($id[$i]))
            {
                $this->update_tutors_record($id[$i], $tutors[$i], $courses[$i], $quotas[$i]);
            }
            else
            {
                $this->insert_tutors_row($tutors[$i], $courses[$i], $quotas[$i]);
            }
        }

        if($deleteEvent) $this->delete_tutors_record($deleteEvent);
    }

    private function update_tutors_record(int $id, int $tutor, int $course, int $quota) : void
    {
        $record = new stdClass;
        $record->id = $id;
        $record->coursework = $this->cm->instance;
        $record->tutor = $tutor;
        $record->course = $course;
        $record->quota = $quota;

        global $DB;
        $DB->update_record('coursework_tutors', $record);
    }

    private function insert_tutors_row(int $tutor, int $course, int $quota) : void
    {
        try
        {
            if(!$tutor || !$course || !$quota) throw new Exception(get_string('e:no-tutor-necessary-data', 'coursework'));

            $temp = new stdClass;
            $temp->coursework = $this->cm->instance;
            $temp->tutor = $tutor;
            $temp->course = $course;
            $temp->quota = $quota;

            global $DB;
            $DB->insert_record('coursework_tutors', $temp, false);
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function delete_tutors_record(int $rowID) : void
    {
        global $DB;
        $DB->delete_records('coursework_tutors', array('id'=>$rowID));
    }





}


