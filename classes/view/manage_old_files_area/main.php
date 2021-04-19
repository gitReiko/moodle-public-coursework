<?php 

namespace Coursework\View\ManageOldFilesArea;

require_once 'dashboard.php';
require_once 'getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;
use Coursework\Lib\Getters\TeachersGetter as tg;
use Coursework\Lib\Enums as enum;

class Main 
{
    const SELECTED_TEACHER_ID = 'teacher_id';

    private $cm;
    private $teachers;
    private $selectedTeacherId;

    function __construct(\stdClass $cm)
    {
        $getter = new Getter($cm);

        $this->cm = $getter->get_cm();
        $this->teachers = $getter->get_teachers();
        $this->selectedTeacherId = $getter->get_selected_teacher_id();
    }

    public function get_page() : string 
    {
        $page = cg::get_page_header($this->cm);
        $page.= $this->get_dashboard();

        return $page;
    }

    private function get_dashboard() : string 
    {
        $dashboard = new Dashboard (
            $this->cm, 
            $this->teachers, 
            $this->selectedTeacherId
        );
        return $dashboard->get();
    }





}
