<?php 

namespace Coursework\View\StudentsWorksList\StudentsTable;

require_once 'main_row.php';
require_once 'notification_row.php';

use Coursework\View\StudentsWorksList as swl;
use Coursework\Lib\Notifications;

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
            $ntfs = $this->get_notifications($student);

            $body.= $this->get_main_row($student, $ntfs);
            $body.= $this->get_notification_row($student, $ntfs);
        }

        $body.= \html_writer::end_tag('tbody');

        return $body;
    }

    private function get_notifications(\stdClass $student) : Notifications
    {
        return new Notifications(
            $this->d->get_cm()->instance,
            $student,
            $this->d->get_selected_teacher_id()
        );
    }

    private function get_main_row(\stdClass $student, Notifications $ntfs) : string 
    {
        $mainRow = new MainRow(
            $this->d->get_cm(), 
            $student, 
            $ntfs
        );
        return $mainRow->get();
    }

    private function get_notification_row(\stdClass $student, Notifications $ntfs) : string 
    {
        $notificationsRow = new NotificationRow($student, $ntfs);
        return $notificationsRow->get();
    }



}
