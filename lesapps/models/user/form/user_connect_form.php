<?php

/**
 * La classe User_connect_form permet l'affichage du formulair de connection
 *
 * @author leo
 */
class User_connect_form extends CI_Model{
    
    public function __construct() {
        parent::__construct();
        $this->lang->loads('user/form/form_connect');
    }

    public function printForm() {

        echo validation_errors();

        echo form_open(find_uri('user', 'connect'));


        echo form_fieldset($this->lang->line('form_fieldset'));

        echo my_form('username', 'form_connect_pseudo');
        echo my_form('password', 'form_connect_password', 'password');
        
        echo $this->lang->line('form_connect_remeber_me');
        echo form_checkbox('remeber_me', "yes", TRUE);
        echo '<br/>';

        echo form_submit('submit', $this->lang->line('form_connect_submit'));
        echo form_fieldset_close();
        echo form_close();
    }

}

?>
