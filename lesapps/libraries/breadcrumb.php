<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Permet de faire le breadcrumbs (file d'ariane) automatiquement sur chaque page.
 *
 * @author yves
 */
class Breadcrumb {

    protected $bundle = NULL;
    protected $method = NULL;
    // La methode de base qui est appelée si la class n'en a pas
    protected $default_method = NULL;
    protected $lang = NULL;
    protected $breadcrumb = '';
    // Contiendra un array(array('uri', 'label')) à 2 dimentions
    protected $elements = array();
    // Options
    protected $breadcrumb_prefix = '';
    protected $breadcrumb_suffix = '';
    protected $normal_element_prefix = '';
    protected $normal_element_suffix = '';
    protected $unavailable_element_prefix = '';
    protected $unavailable_element_suffix = '';
    protected $current_element_prefix = '';
    protected $current_element_suffix = '';
    protected $label_prefix = '';
    protected $label_suffix = '';

    public function __construct() {
        $router = &load_class('Router');
        $this->bundle = $router->class;
        $this->method = $router->method;

        $CI = & get_instance();
        $this->lang = $CI->lang;
        $CI->load->config('breadcrumb', TRUE);
        $this->default_method = $CI->config->item('default_method', 'breadcrumb');

        $this->breadcrumb_prefix = $CI->config->item('breadcrumb_prefix', 'breadcrumb');
        $this->breadcrumb_suffix = $CI->config->item('breadcrumb_suffix', 'breadcrumb');
        $this->normal_element_prefix = $CI->config->item('normal_element_prefix', 'breadcrumb');
        $this->normal_element_suffix = $CI->config->item('normal_element_suffix', 'breadcrumb');
        $this->unavailable_element_prefix = $CI->config->item('unavailable_element_prefix', 'breadcrumb');
        $this->unavailable_element_suffix = $CI->config->item('unavailable_element_suffix', 'breadcrumb');
        $this->current_element_prefix = $CI->config->item('current_element_prefix', 'breadcrumb');
        $this->current_element_suffix = $CI->config->item('current_element_suffix', 'breadcrumb');
        $this->label_prefix = $CI->config->item('label_prefix', 'breadcrumb');
        $this->label_suffix = $CI->config->item('label_suffix', 'breadcrumb');
    }

    /*
     * Crée le file d'ariane
     */

    public function builtBreadcrumb() {
        $this->breadcrumb .= $this->breadcrumb_prefix;
        foreach ($this->elements AS $element) {
            $this->breadcrumb .= $this->normal_element_prefix;

            $this->breadcrumb .= anchor($element['bundle'], $element['method'], $this->label_prefix . $element['label'] . $this->label_suffix);
            $this->breadcrumb .= $this->normal_element_suffix;
        }
        $this->breadcrumb .= $this->breadcrumb_suffix;

        return $this;
    }

    /*
     * Crée les élements du breadcrumb
     */

    private function _builtElements() {
        $i = 0;
        // On ajoute l'accueil de base
        $this->elements[$i++] = array('bundle' => '', 'method' => '', 'label' => $this->lang->line('bc_site'));

        // Si le bundle à un nom, on met dans quel bundle on est (par exemple le bundle main n'a pas de nom)
        $actu_bundle = $this->lang->line('bc_bundle');
        if (!empty($actu_bundle)) {
            $this->elements[$i++] = array('bundle' => $this->bundle, 'method' => $this->default_method, 'label' => $actu_bundle);
        }


        return $this;
    }

    /*
     * retourn le breadcrumb
     */

    public function getBreadcrumb() {
        if (empty($this->breadcrumb)) {
            $this->_builtElements()->builtBreadcrumb();
        }
        return $this->breadcrumb;
    }

}
