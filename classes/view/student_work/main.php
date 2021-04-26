<?php

require_once 'theme_selection/main.php';
require_once 'task_assignment/main.php';
require_once 'work_completion.php';

require_once 'save_files/page.php';
require_once 'save_files/student_file_manager.php';
require_once 'save_files/teacher_file_manager.php';

require_once 'components/base.php';
require_once 'components/container.php';
require_once 'components/info.php';
require_once 'components/guidelines.php';
require_once 'components/chat.php';
require_once 'components/filemanager.php';
require_once 'components/task/main.php';
require_once 'components/workcheck.php';
require_once 'components/navigation.php';

require_once 'locallib.php';

use Coursework\View\StudentWork\ThemeSelection\Main as themeSelect;
use Coursework\View\StudentWork\SaveFiles as save_files;
use Coursework\View\StudentsWork as sw;

use Coursework\View\StudentWork\TaskAssignment\Main as TaskAssignment;
use Coursework\View\StudentWork\WorkCompletion;
use coursework_lib as lib;
use view_lib as view;

class StudentWorkMain 
{
    const TO_PAGE = 'to_page';
    const SAVE_FILES = 'save_files';

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
        if($this->is_neccessary_save_files())
        {
            return $this->get_save_files_page();
        }
        else if($this->is_theme_not_selected())
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

    private function is_neccessary_save_files() : bool 
    {
        $toPage = optional_param(self::TO_PAGE, null, PARAM_TEXT);

        if($toPage == self::SAVE_FILES)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_save_files_page() : string 
    {
        $saveFiles = new save_files\Page(
            $this->course,
            $this->cm,
            $this->studentId
        );
        return $saveFiles->get_page();
    }

    private function get_theme_selection_page() : string 
    {
        $themeSelection = new themeSelect($this->course, $this->cm, $this->studentId);
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
        $taskAssign = new TaskAssignment($this->course, $this->cm, $this->studentId);
        return $taskAssign->get_page();
    }

    private function get_work_completion_page() : string 
    {
        $workCompletion = new WorkCompletion($this->course, $this->cm, $this->studentId);
        return $workCompletion->get_page();
    }




}

