<?php

namespace Coursework\Support\DeleteStudentCoursework;

require_once '../../classes/lib/main_template.php';
require_once 'page.php';
require_once 'database.php';

class Main extends \Coursework\Classes\Lib\MainTemplate
{
    const MODULE_URL = '/mod/coursework/pages/support/delete_student_coursework.php';

    const STUDENT_ID = 'student_id';

    protected $course;
    protected $cm;

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        parent::__construct($course, $cm);
    }

    protected function get_redirect_path() : string
    {
        return '/mod/coursework/pages/support/delete_student_coursework.php';
    }

    protected function get_redirect_params() : array
    {
        return array('id' => $this->cm->id);
    }

    protected function get_content() : string 
    {
        $p = new Page($this->course, $this->cm);
        return $p->get_page();
    }

    protected function execute_database_handler() 
    {
        $handler = new Database($this->course, $this->cm);
        $handler->execute();
    }

}
