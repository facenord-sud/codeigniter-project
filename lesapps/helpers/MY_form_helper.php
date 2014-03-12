<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

if (!function_exists('my_form')) {

    function my_form($name, $lang = '', $type = '', $default = '', $break = TRUE, $select = array()) {
        // Afficher l'erreur 
        echo form_error($name);
        // Afficher le label
        if (!empty($lang)) {
            echo lang($lang, $name);
        } else {
            echo lang('form_' . $name, $name);
        }

        if ($break) {
            echo '<br/>';
        }

        if ($type == 'url') {
            echo '<div class="row collapse"><div class="small-3 large-2 columns">
        <span class="prefix">http://</span></div><div class="small-9 large-10 columns">';
            echo form_input(array('name' => $name, 'id' => $name, 'value' => set_value($name, $default)));
            echo "</div></div>";
        }
        if (empty($type)) {
            echo form_input(array('name' => $name, 'id' => $name, 'value' => set_value($name, $default)));
        }
        if ($type == 'password') {
            echo form_password(array('name' => $name, 'id' => $name, 'value' => set_value($name, $default)));
        }
        if ($type == 'text') {
            echo form_textarea(array('name' => $name, 'id' => $name, 'value' => set_value($name, $default)));
        }
        if ($type == 'file') {
            echo form_upload(array('name' => $name, 'id' => $name, 'value' => set_value($name, $default)));
        }
        if ($type == 'cb') {
            echo form_checkbox($name, 1, $default, "id=\"$name\"");
        }
        if ($type == 'multi') {
            echo form_multiselect($name . '[]', $default, $select);
        }
    }
}

if (!function_exists('pdoArrayToDropDown')) {

    function pdoArrayToDropDown($pdoArray, $view = 'name', $select = 'id') {
        $dropDownArray = array();
        foreach ($pdoArray as $value) {
            $dropDownArray[$value[$select]] = $value[$view];
        }
        return $dropDownArray;
    }

}

if (!function_exists('formSelect')) {

    function formSelect($name, $pdoArray, $lang = '', $view = 'name', $select = 'id', $selected = NULL, $break = TRUE) {
        // Afficher l'erreur 
        echo form_error($name);

        // Afficher le label
        if (!empty($lang)) {
            echo lang($lang, $name);
        } else {
            echo lang('form_' . $name, $name);
        }

        $dropDownArray = array();
        foreach ($pdoArray as $value) {
            if (is_object($value)) {
                $dropDownArray[$value->$select] = $value->$view;
            } elseif (is_array($value)) {
                $dropDownArray[$value[$select]] = $value[$view];
            } else {
                throw new Exception('The second parameter must be an array or an object');
            }
        }
//        if (!empty(set_value($name))) {
//            $selected = set_value($name);
//        }
        echo form_dropdown($name, $dropDownArray, $selected);
        if ($break) {
            echo '<br/>';
        }
    }

}

    