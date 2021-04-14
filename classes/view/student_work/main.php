<?php

require_once 'modules/module.php';
require_once 'modules/chat.php';
require_once 'modules/guidelines.php';
require_once 'modules/work_info.php';
require_once 'modules/task_template.php';
require_once 'modules/task_completion.php';
require_once 'modules/filemanager.php';
require_once 'modules/send_for_check.php';
require_once 'modules/work_check.php';
require_once 'theme_selection/main.php';
require_once 'task_assignment/main.php';
require_once 'work_completion/main.php';

require_once 'grids/base_grid.php';
require_once 'grids/info_grid.php';
require_once 'grids/guidelines_grid.php';
require_once 'grids/chat_grid.php';

use Coursework\View\StudentsWork as sw;

use coursework_lib as lib;
use view_lib as view;

class StudentWorkMain 
{
    const COURSEWORK_STAGE = 'coursework_stage';
    const THEME_SELECTION = 'theme_selection';
    const TASK_GETTING = 'task_getting';
    const WORK_COMPLETION = 'work_completion';

    private $course;
    private $cm;
    private $studentId;

    function __construct(stdClass $course, stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;
    }

    public function get_page() : string 
    {
        if($this->is_theme_not_selected())
        {
            return $this->get_theme_selection_page();
        }
        else if(view\is_coursework_use_task($this->cm)
                    && $this->is_task_not_assign_to_student())
        {
            return $this->get_task_assignment_page();
        }
        else 
        {
            return $this->get_work_completion_page();
        }
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new ThemeSelectionMain($this->course, $this->cm, $this->studentId);
        return $themeSelection->get_page();
    }

    private function is_theme_not_selected() : bool 
    {
        global $DB;
        $conditions = array('coursework' => $this->cm->instance, 'student' => $this->studentId);
        $record = $DB->get_record('coursework_students', $conditions);

        if(!empty($record->theme) || !empty($record->owntheme))
        {
            return false;
        }
        else 
        {
            return true;
        }
    }

    private function is_task_not_assign_to_student() : bool 
    {
        global $DB;
        $sql = 'SELECT id
                FROM {coursework_students} 
                WHERE coursework = ? 
                AND student = ? 
                AND (task IS NULL OR task = 0)';
        $params = array($this->cm->instance, $this->studentId);
        return $DB->record_exists_sql($sql, $params);
    }

    private function get_task_assignment_page() : string 
    {
        $taskAssign = new TaskAssignmentMain($this->course, $this->cm, $this->studentId);
        return $taskAssign->get_page();
    }

    private function get_work_completion_page() : string 
    {
        $workCompletion = new WorkCompletionMain($this->course, $this->cm, $this->studentId);
        return $workCompletion->get_page();
    }




}

