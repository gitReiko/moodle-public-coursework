<?php

namespace Coursework\Config\SetDefaultTaskTemplate;

use Coursework\Lib\Getters\CommonGetter as cg;

class SetDefaultTask 
{
    protected $course;
    protected $cm;

    protected $coursework;
    protected $templates;

    private $backToOverviewFormName = 'backToOverviewForm';

    const ACTION_FORM = 'action_form';

    function __construct($course, $cm)
    {
        $this->course = $course;
        $this->cm = $cm;

        $this->coursework = cg::get_coursework($this->cm->instance);
        $this->tasks = $this->get_task_templates();
    }

    public function get_gui() : string 
    {
        $gui = '';
        
        $gui.= $this->get_action_header();
        $gui.= $this->get_go_to_tasks_templates_setup_page();

        if($this->is_task_templates_exists())
        {
            $gui.= $this->get_html_form_start();
            $gui.= $this->get_task_template_field();
            $gui.= $this->get_buttons_panel();
            $gui.= $this->get_form_hidden_inputs();
            $gui.= $this->get_html_form_end();
        }
        else
        {
            $gui.= $this->get_task_templates_not_exists();
            $gui.= $this->get_back_to_overview_button();
        }

        $gui.= $this->get_back_to_overview_form();
     
        return $gui;
    }

    private function is_task_templates_exists() : bool 
    {
        if(count($this->tasks))
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_task_templates()
    {
        global $DB;
        return $DB->get_records('coursework_tasks', array('template' => 1), 'name');
    }
    
    private function get_html_form_start() : string 
    {
        $attr = array(
            'id' => self::ACTION_FORM,
            'method' => 'post'
        );
        return \html_writer::start_tag('form', $attr);
    }

    private function get_action_header() : string
    {
        $text = get_string('select_default_task_template', 'coursework');
        return \html_writer::tag('h3', $text);
    }

    private function get_go_to_tasks_templates_setup_page() : string 
    {
        $url = '/mod/coursework/pages/tasks_templates_management.php?id='.$this->cm->id;
        $attr = array('href' => $url);
        $text = get_string('go_to_task_templates_setup_page', 'coursework');
        $text = \html_writer::tag('a', $text, $attr);
        return \html_writer::tag('p', $text);
    }

    private function get_task_template_field() : string 
    {
        $text = get_string('task_template', 'coursework');
        $field = \html_writer::tag('h4', $text);

        $text = $this->get_task_template_select();
        $field.= \html_writer::tag('p', $text);

        return $field;
    }

    private function get_task_template_select() : string 
    {
        $attr = array(
            'name' => Main::TASK,
            'autocomplete' => 'off',
            'autofocus' => 'autofocus'
        );
        $s = \html_writer::start_tag('select', $attr);

        foreach($this->tasks as $task)
        {
            $attr = array('value' => $task->id);

            if($this->is_task_selected($task->id))
            {
                $attr = array_merge($attr, array('selected' => 'selected'));
            }

            $text = $task->name;

            $s.= \html_writer::tag('option', $text, $attr);
        }
        $s.= \html_writer::end_tag('select');

        return $s;
    }

    protected function is_task_selected(int $taskId) : bool
    {
        if(empty($this->coursework->defaulttask))
        {
            return false;
        }
        else if($taskId == $this->coursework->defaulttask)
        {
            return true;
        }
        else 
        {
            return false;
        }
    }

    private function get_buttons_panel() : string 
    {
        $attr = array('class' => 'btns_panel');
        $p = \html_writer::start_tag('table', $attr);
        $p.= \html_writer::start_tag('tr');
        $p.= \html_writer::tag('td', $this->get_action_button());
        $p.= \html_writer::tag('td', $this->get_back_to_overview_button());
        $p.= \html_writer::end_tag('tr');
        $p.= \html_writer::end_tag('table');

        return $p;
    }

    private function get_action_button() : string
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('select_task_template', 'coursework')
        );
        $btn = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $btn);
    }

    private function get_back_to_overview_button() : string 
    {
        $attr = array(
            'type' => 'submit',
            'value' => get_string('back', 'coursework'),
            'form' => $this->backToOverviewFormName
        );
        $btn = \html_writer::empty_tag('input', $attr);
        return \html_writer::tag('p', $btn);
    }

    private function get_form_hidden_inputs() : string 
    {
        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $inputs = \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::DATABASE_EVENT,
            'value' => Main::SET_DEFAULT_TASK
        );
        $inputs.= \html_writer::empty_tag('input', $attr);

        return $inputs;
    }

    private function get_html_form_end() : string 
    {
        return \html_writer::end_tag('form');
    }

    private function get_back_to_overview_form() : string 
    {
        $attr = array(
            'id' => $this->backToOverviewFormName,
            'method' => 'post'
        );
        $form = \html_writer::start_tag('form', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::ID,
            'value' => $this->cm->id
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $attr = array(
            'type' => 'hidden',
            'name' => Main::GUI_TYPE,
            'value' => Main::OVERVIEW
        );
        $form.= \html_writer::empty_tag('input', $attr);

        $form.= \html_writer::end_tag('form');

        return $form;
    }

    private function get_task_templates_not_exists() : string 
    {
        $text = get_string('task_template_not_exists', 'coursework');
        return \html_writer::tag('p', $text);
    }


} 

