<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of role_modify_form
 *
 * @author leo
 * @property Role_entity $role
 */
class role_modify_form extends CI_Model {

    private $role;

    public function printForm() {
        echo validation_errors();

        echo form_open();
        echo form_fieldset($this->lang->line('fieldset_title'));
        echo my_form('nick_name', 'nick_name', '', $this->role->nick_name);
        echo form_submit('submit', $this->lang->line('submit'));
        echo form_fieldset_close();
        echo form_close();
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

}

?>
