<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_delete_form
 *
 * @author leo
 */
class user_delete_form extends CI_Model{
    
    public function printForm() {
        echo validation_errors();
        echo form_open();
        echo form_fieldset($this->lang->line('fieldset_title'));
        echo my_form('username', 'username');
        echo form_submit('submit', $this->lang->line('submit'));
        echo form_fieldset_close();
        echo form_close();
    }
}

?>
