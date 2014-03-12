<?php

/**
 * Description of role_traduce_form
 *
 * @author leo
 * @property Role_entity $role
 */
class role_traduce_form extends CI_Model {

    private $role;

    public function printForm() {
        echo validation_errors();

        echo form_open();
        echo form_fieldset($this->lang->line('fieldset_title'));
        echo my_form('name', 'name', '', $this->role->name);
        echo my_form('description', 'description', 'text', $this->role->description);
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
