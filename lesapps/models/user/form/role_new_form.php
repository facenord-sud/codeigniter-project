<?php

/**
 * La classe Rser_new_form permet la création d'un nouveau rôle
 *
 * @author leo
 */
class Role_new_form extends CI_Model{

    public function printForm() {
        echo validation_errors();

        echo form_open();

        echo form_fieldset($this->lang->line('fieldset_title'));

        echo my_form('nick_name', 'nick_name');

        echo form_submit('submit', $this->lang->line('submit'));
        echo form_fieldset_close();
        echo form_close();
    }

}

?>
