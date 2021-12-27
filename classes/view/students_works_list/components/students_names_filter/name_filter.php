<?php

namespace Coursework\View\StudentsWorksList\StudentsNamesFilter;

use Coursework\View\StudentsWorksList as swl;

abstract class NameFilter 
{
    protected $d;
    protected $letters;

    function __construct(swl\MainGetter $d) 
    {
        $this->d = $d;
        $this->letters = $this->get_letters();
    }

    public function get_name_filter()
    {
        $filter = $this->get_header();
        $filter.= $this->get_letters_list();

        return \html_writer::tag('p', $filter);
    }

    protected function get_letters()
    {
        $letters = $this->get_all_letters();
        $letters = array_unique($letters);
        sort($letters);

        return $letters;
    }

    abstract protected function get_all_letters();

    private function get_header() : string 
    {
        $attr = array(
            'class' => 'help',
            'title' => get_string('names_filter_title', 'coursework')
        );
        $text = $this-> get_header_text().': ';
        return \html_writer::tag('span', $text, $attr);
    }

    abstract protected function get_header_text() : string;

    private function get_letters_list() : string 
    {
        $list = '';
        $list.= $this->add_all_letter($this->get_input_name());

        foreach($this->letters as $letter)
        {
            $attr = array(
                'href' => '#', 
                'onclick' => $this->get_filter_js_func($letter)
            );

            if($this->is_letter_selected($letter))
            {
                $attr = $this->add_selected_letter_class($attr);
            }

            $text = $letter.' ';
            $list.= \html_writer::tag('a', $text, $attr);
        }

        return $list;
    }

    private function add_all_letter($name)
    {
        $attr = array(
            'href' => '#', 
            'onclick' => $this->get_filter_js_func(Main::ALL)
        );

        if($this->is_letter_selected(Main::ALL))
        {
            $attr = $this->add_selected_letter_class($attr);
        }

        $text = get_string(Main::ALL, 'coursework').' ';
        return \html_writer::tag('a', $text, $attr);
    }

    abstract protected function is_letter_selected($letter) : bool;

    private function get_filter_js_func($letter) : string 
    {
        $func = 'ViewStudentsWorks.filter_name(';
        $func.= '`'.$letter.'`,';
        $func.= '`'.$this->get_input_name().'`,';
        $func.= '`'.swl\Page::FORM_ID.'`);';

        return $func;
    }

    abstract protected function get_input_name() : string;

    private function add_selected_letter_class(array $attr) : array 
    {
        return array_merge($attr, array('class' => 'selected-letter '));
    }

}
