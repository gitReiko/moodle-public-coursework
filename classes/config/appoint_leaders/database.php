<?php

namespace Coursework\Config\AppointLeaders;

class Database
{
    private $course;
    private $cm;
    private $event;

    function __construct(\stdClass $course, \stdClass $cm)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->event = optional_param(Main::DATABASE_EVENT, null, PARAM_TEXT);
    }

    public function execute() : void 
    {
        try
        {
            if($this->event == Main::ADD_LEADER)
            {
                $this->add_leader();
            }
            else if($this->event == Main::EDIT_LEADER)
            {
                $this->edit_leader();
            }
            else if($this->event == Main::DELETE_LEADER)
            {
                $this->delete_leader();
            }
        }
        catch(Exception $e)
        {
            cw_print_error_message($e->getMessage());
        }
    }

    private function add_leader() : void 
    {
        global $DB;
        $leader = $this->get_leader();

        // Because there can be only one bundle leader + course.
        if($this->is_leader_already_exist($leader))
        {
            throw new Exception(get_string('e-le-ev:leader_already_exist', 'coursework'));
        }
        else
        {
            $DB->insert_record('coursework_teachers', $leader, false);
            $this->log_added_coursework_leader();
        }
    }

    private function edit_leader() : void 
    {
        global $DB;
        $update = true;
        $leader = $this->get_leader($update);

        // Because there can be only one bundle leader + course.
        if($this->is_more_than_two_leaders($leader))
        {
            throw new Exception(get_string('e-le-ev:leader_already_exist', 'coursework'));
        }
        else
        {
            $DB->update_record('coursework_teachers', $leader);
            $this->log_coursework_leader_changed();
        }
    }

    private function delete_leader() : void
    {
        global $DB;
        $id = $this->get_leader_row_id();
        $DB->delete_records('coursework_teachers', array('id'=>$id));
        $this->log_coursework_leader_deleted();
    }

    private function get_leader(bool $update = false) : \stdClass 
    {
        $leader = new \stdClass;
        if($update) $leader->id = $this->get_leader_row_id();
        $leader->coursework = $this->get_leader_coursework();
        $leader->teacher = $this->get_leader_teacher();
        $leader->course = $this->get_leader_course();
        $leader->quota = $this->get_leader_quota();
        return $leader; 
    }

    private function get_leader_row_id() : int 
    {
        $rowId = optional_param(Main::LEADER_ROW_ID, null, PARAM_INT);

        if(isset($rowId)) return $rowId;
        else throw new Exception(get_string('e-le-ev:missing_row_id', 'coursework'));       
    }

    private function get_leader_coursework() : int 
    {
        $coursework = $this->cm->instance;

        if(isset($coursework)) return $coursework;
        else throw new Exception(get_string('e-le-ev:missing_coursework', 'coursework'));
    }

    private function get_leader_teacher() : int 
    {
        $teacher = optional_param(Main::LEADER_ID, null, PARAM_INT);

        if(isset($teacher)) return $teacher;
        else throw new Exception(get_string('e-le-ev:missing_teacher', 'coursework'));
    }

    private function get_leader_course() : int 
    {
        $course = optional_param(Main::COURSE_ID, null, PARAM_INT);

        if(isset($course)) return $course;
        else throw new Exception(get_string('e-le-ev:missing_course', 'coursework'));
    }

    private function get_leader_quota() : int 
    {
        $quota = optional_param(Main::QUOTA, null, PARAM_INT);

        if(isset($quota)) return $quota;
        else throw new Exception(get_string('e-le-ev:missing_quota', 'coursework'));
    }

    private function is_leader_already_exist(\stdClass $leader) : bool 
    {
        global $DB;
        $conditions = array('coursework'=> $leader->coursework,
                            'teacher'=>    $leader->teacher,
                            'course'=>     $leader->course);
        
        if($DB->record_exists('coursework_teachers', $conditions)) return true;
        else return false;
    }

    private function is_more_than_two_leaders(\stdClass $leader) : bool 
    {
        $leaders = $this->get_leaders($leader);
        $leadersCount = count($leaders);

        if($leadersCount > 1) return true;
        else if($leadersCount == 1)
        {
            if($leader->id == reset($leaders)->id) return false;
            else return true;
        }
        else return false;
    }

    private function get_leaders(\stdClass $leader) : array
    {
        global $DB;
        $leaders = array();
        $conditions = array('coursework'=> $leader->coursework,
                            'teacher'=>    $leader->teacher,
                            'course'=>     $leader->course);

        $leaders = $DB->get_records('coursework_teachers', $conditions);

        return $leaders;
    }

    private function log_added_coursework_leader() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\coursework_leader_added::create($params);
        $event->trigger();
    }

    private function log_coursework_leader_changed() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\coursework_leader_changed::create($params);
        $event->trigger();
    }

    private function log_coursework_leader_deleted() : void 
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\coursework_leader_deleted::create($params);
        $event->trigger();
    }


}

