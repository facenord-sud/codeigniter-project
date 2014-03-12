<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('img')) {

    function img($path, $class='', $alt = 'l\'image ne peut être affichée') {
        if(!empty($class)) {
            $class = 'class="'.$class.'"';
        }
        return '<img '.$class.' src="' . base_url() . 'www/images/' . $path . '" alt="' . $alt . '"/>'."\n";
    }

}

if (!function_exists('css')) {

    function css($path, $media = '') {
        return'<link rel="stylesheet" href="'.  base_url().'www/stylesheets/' . $path . '" media="' . $media . '"/>'."\n";
    }

}

if (!function_exists('js')) {

    function js($path) {
        return '<script src="'.  base_url().'www/javascripts/' . $path . '"></script>'."\n";
    }

}
?>
