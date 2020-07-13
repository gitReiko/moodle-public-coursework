<?php

use coursework_lib as lib;

class SendForCheck extends ViewModule 
{
    private $sections;

    function __construct(stdClass $course, stdClass $cm, int $studentId, bool $displayBlock = false)
    {
        parent::__construct($course, $cm, $studentId, $displayBlock);

        $this->sections = $this->get_not_ready_sections($this->cm);
    }

    protected function get_module_name() : string
    {
        return 'sendforcheck';
    }

    protected function get_module_header() : string
    {
        return get_string('send_for_check', 'coursework');
    }

    protected function get_module_body() : string
    {
        $body = '<div class="send_to_check">';
        $body.= $this->get_send_to_check_sections_buttons();
        $body.= $this->get_send_to_check_work_buttons();
        $body.= '</div>';
        return $body;
    }

    private function get_not_ready_sections() 
    {
        $sections = lib\get_sections_to_check($this->cm, $this->studentId);

        $notReadySections = array();
        foreach($sections as $section)
        {
            if(lib\is_section_status_exist($this->cm, $this->studentId, $section->id))
            {
                $status = $this->get_section_status($section);

                if($status == NEED_TO_FIX)
                {
                    $notReadySections[] = $section;
                }
            }
            else 
            {
                $notReadySections[] = $section;
            }
        }

        return $notReadySections;
    }

    private function get_section_status($section) : string  
    {
        global $DB;
        $where = array('coursework'=>$this->cm->instance, 
                        'student' => $this->studentId,
                        'section' => $section->id);
        return $DB->get_field('coursework_sections_status', 'status', $where);
    }

    private function get_send_to_check_sections_buttons() : string 
    {
        $btns = '';
        foreach($this->sections as $section)
        {
            $btns.= '<p>';
            $btns.= '<form>';
            $btns.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
            $btns.= '<input type="hidden" name="'.SECTION.'" value="'.$section->id.'"/>';
            $btns.= '<input type="hidden" name="'.STUDENT.'" value="'.$this->studentId.'"/>';
            $btns.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::SEND_SECTION_FOR_CHECK.'">';
            $btns.= '<button>'.get_string('send_for_check_section', 'coursework').' «'.$section->name.'»</button>';
            $btns.= '</form>';
            $btns.= '</p>';
        }

        return $btns;
    }

    private function get_send_to_check_work_buttons() : string 
    {
        $btn = '<p>';
        $btn.= '<form>';
        $btn.= '<input type="hidden" name="'.ID.'" value="'.$this->cm->id.'"/>';
        $btn.= '<input type="hidden" name="'.STUDENT.'" value="'.$this->studentId.'"/>';
        $btn.= '<input type="hidden" name="'.DB_EVENT.'" value="'.ViewDatabaseHandler::SEND_WORK_FOR_CHECK.'">';
        $btn.= '<button>'.get_string('send_for_check_work', 'coursework').'</button>';
        $btn.= '</form>';
        $btn.= '</p>';
        return $btn;
    }






}

