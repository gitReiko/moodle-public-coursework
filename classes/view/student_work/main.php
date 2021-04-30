<?php

namespace Coursework\View\StudentWork;

require_once 'theme_selection/main.php';
require_once 'task_assignment/main.php';
require_once 'work_completion/main.php';
require_once 'save_files/main.php';

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
use Coursework\View\StudentWork\TaskAssignment\Main as TaskAssignment;
use Coursework\View\StudentWork\WorkCompletion\Main as WorkCompletion;
use Coursework\View\StudentWork\SaveFiles\Main as SaveFiles;
use Coursework\View\StudentsWork as sw;
use Coursework\Lib\CommonLib as cl;

class Main 
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

    function __construct(\stdClass $course, \stdClass $cm, int $studentId)
    {
        $this->course = $course;
        $this->cm = $cm;
        $this->studentId = $studentId;

        $this->log_event_coursework_viewed();
    }

    private function log_event_coursework_viewed()
    {
        global $USER;

        if(cl::is_user_student($this->cm, $USER->id))
        {
            $this->log_event_student_view_own_work();
        }
        else 
        {
            $this->log_event_user_view_student_work();
        }

    }

    private function log_event_student_view_own_work()
    {
        $params = array
        (
            'context' => \context_module::instance($this->cm->id)
        );

        $event = \mod_coursework\event\student_view_own_work::create($params);
        $event->trigger();
    }

    private function log_event_user_view_student_work()
    {
        $params = array
        (
            'relateduserid' => $this->studentId, 
            'context' => \context_module::instance($this->cm->id)
        );
        
        $event = \mod_coursework\event\user_view_student_work::create($params);
        $event->trigger();
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
        else if(cl::is_coursework_use_task($this->cm->instance)
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
        $saveFiles = new SaveFiles(
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

