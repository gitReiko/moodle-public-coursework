<?php 

namespace Coursework\View\ManageOldFilesArea;

require_once 'getter.php';
require_once 'dashboard.php';
require_once 'filemanager.php';

use Coursework\Lib\Getters\CommonGetter as cg;

class Main 
{
    const SELECTED_TEACHER_ID = 'selected_teacher_id';

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
        $page.= $this->get_filemanager();

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

    private function get_filemanager() : string 
    {
        $filemanager = new FileManager(
            $this->cm,
            $this->selectedTeacherId
        );
        return \html_writer::tag('p', $filemanager->get()); 
    }





}
