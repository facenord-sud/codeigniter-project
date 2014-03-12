<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of Twiggy
 *
 * @author leo
 */
class TwiggyView {

    private $class = '';
    private $method = '';
    private $CI;

    public function __construct() {
        $CI = & get_instance();
        $this->class = $CI->router->class;
        $this->method = $CI->router->method;
        $this->CI = & get_instance();
    }

    public function display() {
        if (ENVIRONMENT == 'testing') {
            return;
        }
        if (!isset($this->CI->twiggy)) {
            return;
        }
        $this->CI->twiggy->display($this->class . '/' . $this->method);
    }

    public function loadLanguage() {
        if (ENVIRONMENT != 'testing') {
            $this->CI->lang->loads($this->class . '/template/template');
            $this->CI->twiggy->title()->prepend($this->CI->lang->line('meta_title'));
            $this->CI->lang->loads($this->class . '/' . $this->method);
            $this->CI->twiggy->title()->prepend($this->CI->lang->line('meta_title'));
            $this->CI->twiggy->meta('Description', $this->CI->lang->line('meta_description'));
            $this->CI->twiggy->meta('Keywords', $this->CI->lang->line('meta_keywords'));
            $this->CI->twiggy->set($this->CI->lang->getLanguages());
        }
    }

}

?>
