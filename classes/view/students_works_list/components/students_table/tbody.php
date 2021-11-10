<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

require_once 'main_row.php';
require_once 'notification_row.php';
require_once 'sections_rows.php';

use Coursework\View\StudentsWorksList as swl;
use Coursework\Lib\TeacherNotifications;

class Tbody 
{

    private $d;

    function __construct(swl\MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get() : string 
    {
        $body = \html_writer::start_tag('tbody');

        foreach($this->d->get_students() as $student)
        {
            $body.= $this->get_row($student);
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_row($student) : string 
    {
        $ntfs = $this->get_notifications($student);

        $body = $this->get_main_row($student, $ntfs);
        $body.= $this->get_notification_row($student, $ntfs);
        $body.= $this->get_sections_rows($student);

        return $body;
    }

    private function get_notifications(\stdClass $student) : TeacherNotifications
    {
        return new TeacherNotifications(
            $this->d->get_cm()->instance,
            $student,
            $this->d->get_selected_teacher_id()
        );
    }

    private function get_main_row(\stdClass $student, TeacherNotifications $ntfs) : string 
    {
        $mainRow = new MainRow(
            $this->d->get_cm(), 
            $student, 
            $ntfs
        );
        return $mainRow->get();
    }

    private function get_notification_row(\stdClass $student, TeacherNotifications $ntfs) : string 
    {
        $notificationsRow = new NotificationRow($student, $ntfs);
        return $notificationsRow->get();
    }

    private function get_sections_rows($student) : string 
    {
        $sectionsRows = new SectionsRows($student);
        return $sectionsRows->get();
    }



}
