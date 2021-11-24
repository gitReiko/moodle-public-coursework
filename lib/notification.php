<?php 

namespace Coursework\Lib;

require_once 'getters/common_getter.php';

use Coursework\Lib\Getters\CommonGetter as cg;

class Notification 
{
    private $cm;
    private $course;
    private $userFrom;
    private $userTo;
    private $messageName;
    private $messageText;

    function __construct(
        \stdClass $cm, 
        \stdClass $course,
        \stdClass $userFrom,
        \stdClass $userTo,
        string $messageName,
        string $messageText
    )
    {
        $this->cm = $cm;
        $this->course = $course;
        $this->userFrom = $userFrom;
        $this->userTo = $userTo;
        $this->messageName = $messageName;
        $this->messageText = $messageText;
    }

    public function send() : void 
    {
        global $CFG;

        $message = new \core\message\message();
        $message->component = 'mod_coursework';
        $message->name = $this->messageName;
        $message->userfrom = $this->userFrom;
        $message->userto = $this->userTo;
        $message->subject = $this->messageText;
        $message->fullmessage = $this->messageText;
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = $this->get_full_html_message();
        $message->smallmessage = $this->messageText;
        $message->notification = '1';
        $message->contexturl = $CFG->wwwroot.'/coursework/view.php?id='.$this->cm->id;
        $message->contexturlname = cg::get_coursework_name($this->cm->instance);
        $message->courseid = $this->course->id;
    
        message_send($message);
    }

    public static function get_sender_data() : \stdClass 
    {
        global $USER;
        $data = new \stdClass;
        $data->teacher = cg::get_user_name($USER->id);
        $data->date = date('d-m-Y');
        $data->time = date('G:i');
        return $data;
    }

    private function get_full_html_message() : string 
    {
        $htmlMessage = $this->get_links_tree();
        $htmlMessage.= \html_writer::empty_tag('hr');
        $htmlMessage.= $this->messageText;
        $htmlMessage.= $this->get_link_on_coursework();
        $htmlMessage.= \html_writer::empty_tag('hr');
        $htmlMessage.= $this->get_notifications();
    
        return $htmlMessage;
    }

    private function get_links_tree() : string 
    {
        global $CFG;

        // Tree of links
        $url = $CFG->wwwroot.'/course/view.php?id='.$this->course->id;
        $attr = array('href' => $url);
        $text = cg::get_course_fullname($this->course->id);
        $linksTree = \html_writer::tag('a', $text, $attr);

        $url = $CFG->wwwroot.'/mod/coursework/index.php?id='.$this->course->id;
        $attr = array('href' => $url);
        $text = get_string('modulenameplural', 'coursework');
        $linksTree.= \html_writer::tag('a', $text, $attr);

        $url = $CFG->wwwroot.'/mod/coursework/view.php?id='.$this->cm->id;
        $attr = array('href' => $url);
        $text = cg::get_coursework_name($this->cm->instance);
        $linksTree.= \html_writer::tag('a', $text, $attr);

        $linksTree = \html_writer::tag('p', $linksTree);

        return $linksTree;
    }

    private function get_link_on_coursework() : string 
    {
        global $CFG;

        $link = get_string('coursework_link1','coursework');

        $url = $CFG->wwwroot.'/mod/coursework/view.php?id='.$this->cm->id;
        $attr = array('href' => $url);
        $text = get_string('coursework_link2','coursework');
        $link.= \html_writer::tag('a', $text, $attr);

        $link = \html_writer::tag('p', $link);

        return $link;
    }

    private function get_notifications() : string 
    {
        $text = get_string('answer_not_require', 'coursework');
        return \html_writer::tag('p', $text);
    }




}
