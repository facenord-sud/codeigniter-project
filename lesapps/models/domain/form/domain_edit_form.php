<?php

/**
 * Contient le formulaire qui permet de modifier un domaine
 *
 * @author yves
 */
class Domain_edit_form extends CI_Model {

    /**
     * Ecrit le nouveau formulaire 
     *
     * @param $domain Object domain
     * @author yves
     */
    public function printForm($domain) {
        
        // Afficher les erreurs 
        echo validation_errors();
        // Formulaire de création d'un domaine
        echo form_open();
        echo form_fieldset($this->lang->line('form_fieldset'));

        echo lang('form_name', 'name');
        $data = array(
            'name' => 'name',
            'id' => 'name',
            'value'       => set_value('name', $domain->name),
            'maxlength' => '255',
                //'size'        => '50',
                //'style'       => 'width:50%',
        );
        echo form_input($data);
        echo form_error('name');
        echo '<br />';
        
        echo lang('form_description', 'description');
        $data = array(
            'name' => 'description',
            'id' => 'description',
            'value' => set_value('description', $domain->description)
        );
        echo form_textarea($data);
        echo form_error('description');
        echo '<br />';
        
        echo form_submit('submit', $this->lang->line('form_submit'));
        echo form_fieldset_close();
        echo form_close();
    }

}

?>
