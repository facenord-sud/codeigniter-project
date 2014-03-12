<?php

/**
 * La classe User_new_form permet l'affichage du formulair d'inscription d'un 
 * nouveau membre
 *
 * @author leo
 */
class User_new_form extends CI_Model{
    
    public function printForm() {
        echo validation_errors();
        
        echo form_open();
        
        echo form_fieldset($this->lang->line('title_page'));
        
        echo my_form('username', 'pseudo');
        echo my_form('email', 'email');
        echo my_form('password', 'password', 'password');
        echo my_form('password2', 'password2', 'password');
        
        echo $this->lang->line('remeber_me');
        echo form_checkbox('remeber_me', "asda", TRUE);
        echo '<br/>';
        
        echo form_submit('submit', $this->lang->line('submit'));
        echo form_fieldset_close();
        echo form_close();
    }
}

?>
