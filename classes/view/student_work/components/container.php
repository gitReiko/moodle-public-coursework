<?php

namespace Coursework\View\StudentWork\Components;

class Container extends Base 
{
    private $header;
    private $content;

    function __construct(\stdClass $course, \stdClass $cm, int $studentId, string $header, string $content)
    {
        $this->header = $header;
        $this->content = $content;

        parent::__construct($course, $cm, $studentId);
    }

    protected function get_hiding_class_name() : string
    {
        return 'work_container_content';
    }

    protected function get_header_text() : string
    {
        return $this->header;
    }

    protected function get_content() : string
    {
        return $this->content;
    }




}
