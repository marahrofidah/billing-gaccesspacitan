<?php

class Template
{
    var $template_data = array();

    function  set($name, $value)
    {
        $this->template_data[$name] = $value;
    }

    function load($template = '', $view = '', $view_data = array(), $return = FALSE)
    {
        $this->CI = &get_instance();
        $this->set('contents', $this->CI->load->view($view, $view_data, TRUE));
        $template_data = array_merge($view_data, $this->template_data);
        $this->CI->load->view($template, $template_data, $return);
    }
}
