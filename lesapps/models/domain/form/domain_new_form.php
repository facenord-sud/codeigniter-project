<?php

/**
 * Contient le formulaire qui permet d'ajouter un nouveau domaine
 *
 * @author yves
 */
class Domain_New_form extends CI_Model {

    /**
     * Ecrit le nouveau formulaire 
     *
     * @author yves
     */
    public function printForm() {

        // On récupère tous les domaines
        $idLang = $this->query->getLangId($this->lang->getTagLang());
        $domains = $this->query->setLanguage($idLang)
                //->fields(array('id'))
                ->getTreeBuilder()->get('domain')
                ;


        // Afficher les erreurs 
        echo validation_errors();
        // Formulaire de création d'un domaine
        echo form_open();
        echo form_fieldset($this->lang->line('form_fieldset'));

        echo lang('form_name', 'name');
        $data = array(
            'name' => 'name',
            'id' => 'name',
            'value'       => set_value('name'),
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
            'value' => set_value('description')
        );
        echo form_textarea($data);
        echo form_error('description');
        echo '<br />';
        echo lang('form_domain', 'domain');
        
        // On prend les valeurs de domaine pour les mettres dans le tableau $options
        foreach ($domains as $domain => $value) {
            $options[$value['id']]= $value['prefix_name'];
        }
        
        echo form_dropdown('domain', $options, set_value('domain'));
        echo form_error('domain');
        echo '<br />';
        
        echo lang('form_mode', 'mode');
        $options = array(
            'ES' => $this->lang->line('form_mode_ES'),
            'YS' => $this->lang->line('form_mode_YS'),
            'BB' => $this->lang->line('form_mode_BB'),
            'LB' => $this->lang->line('form_mode_LB'),
            'F' => $this->lang->line('form_mode_F'),
        );
        echo form_dropdown('mode', $options, set_value('mode'));
        echo form_error('mode');
        echo '<br />';
        echo form_submit('submit', $this->lang->line('form_submit'));
        echo form_fieldset_close();
        echo form_close();
    }

}

?>
