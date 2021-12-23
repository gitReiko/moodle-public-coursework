<?php

namespace Coursework\View\StudentsWorksList;

class StudentsNamesFilter 
{
    const ALL = 'all';
    const LASTNAME = 'lastname';
    const FIRSTNAME = 'firstname';

    private $d;

    function __construct(MainGetter $d) 
    {
        $this->d = $d;
    }

    public function get_students_names_filter() : string 
    {
        $filter = $this->get_lastname_filter();
        $filter.= $this->get_firstname_filter();

        return $filter;
    }

    private function get_lastname_filter()
    {
        $lastname = new LastnameFilter($this->d);
        return $lastname->get_name_filter();
    }

    private function get_firstname_filter()
    {
        $firstname = new FirstnameFilter($this->d);
        return $firstname->get_name_filter();
    }


}


abstract class NameFilter 
{
    private $d;
    private $letters;

    function __construct(MainGetter $d) 
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

    private function get_letters_list($uniqueLastnamesLetters) : string 
    {
        $list = '';
        $list.= $this->add_all_letter($this->get_input_name());

        foreach($letters as $letter)
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
            'onclick' => $this->get_filter_js_func(StudentsNamesFilter::ALL)
        );

        if($this->is_letter_selected(StudentsNamesFilter::ALL))
        {
            $attr = $this->add_selected_letter_class($attr);
        }

        $text = get_string(self::ALL, 'coursework').' ';
        return \html_writer::tag('a', $text, $attr);
    }

    abstract protected function is_letter_selected($letter) : bool;

    private function get_filter_js_func($letter) : string 
    {
        $func = 'ViewStudentsWorks.filter_name(';
        $func.= '`'.$letter.'`,';
        $func.= '`'.$this->get_input_name().'`,';
        $func.= '`'.Page::FORM_ID.'`);';

        return $func;
    }

    abstract protected function get_input_name() : string;

    private function add_selected_letter_class(array $attr) : array 
    {
        return array_merge($attr, array('class' => 'selected-letter '));
    }


}

class LastnameFilter extends NameFilter
{

    function __construct(MainGetter $d) 
    {
        parent::__construct($d);
    }

    protected function get_all_letters()
    {
        $letters = array();

        foreach($this->d->get_students() as $student)
        {
            $letters[] = mb_substr($student->lastname, 0, 1);
        }

        return $letters;
    }

    protected function get_header_text() : string
    {
        return get_string('lastname', 'coursework');
    }

    protected function get_input_name() : string
    {
        return StudentsNamesFilter::LASTNAME;
    }

    protected function is_letter_selected($letter) : bool
    {
        if($this->d->get_lastname_filter() == $letter)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

}

class FirstnameFilter  extends NameFilter
{

    function __construct(MainGetter $d) 
    {
        parent::__construct($d);
    }

    protected function get_all_letters()
    {
        $letters = array();

        foreach($this->d->get_students() as $student)
        {
            $letters[] = mb_substr($student->firstname, 0, 1);
        }

        return $letters;
    }

    protected function get_header_text() : string
    {
        return get_string('firstname', 'coursework');
    }

    protected function get_input_name() : string
    {
        return StudentsNamesFilter::FIRSTNAME;
    }

    protected function is_letter_selected($letter) : bool
    {
        if($this->d->get_firstname_filter() == $letter)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

}
