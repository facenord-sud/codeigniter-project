<?php

/**
 * Contient le formulaire qui permet d'ajouter un nouveau domaine
 *
 * @author yves
 */
class Domain_delete_form extends CI_Model {

    /**
     * Ecrit le nouveau formulaire 
     *
     * @author yves
     */
    public function printForm() {
        
        // On récupère tous les domaines
        // On récupère tous les domaines
        $idLang = $this->query->getLangId($this->lang->getTagLang());
        $domains = $this->query->setLanguage($idLang)
                ->fields(array('id'))
                ->getTreeBuilder()->get('domain')
                ;
//        $this->query->fields(array('id'));
//        $domains = $this->tree->get('domain');

        // Afficher les erreurs 
        echo validation_errors();
        // Formulaire de création d'un domaine
        echo form_open();
        echo form_fieldset($this->lang->line('form_fieldset'));
        
        echo lang('form_domain', 'domain');
        
        // On prend les valeurs de domaine pour les mettres dans le tableau $options
        $options = array();
        foreach ($domains as $domain => $value) {
            $options[$value['id']]= $value['prefix_name'];
        }
        
        echo form_dropdown('domain', $options, set_value('domain'));
        echo form_error('domain');
        echo '<br />';
        
        echo lang('form_mode', 'mode');
        $options = array(
            0 => $this->lang->line('form_mode_0'),
            1 => $this->lang->line('form_mode_1'),
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
