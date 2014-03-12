<?php

/**
 * S'occupe de valider ou pas le formulaire pour créer un nouveau domaine
 *
 * @author yves
 */
class Domain_new_handler extends MY_Model {

    /**
     * Gère le formulaire pour un nouveau domaine
     *
     * @author yves
     */
    public function handleForm() {
        $config = array(
            array(
                'field' => 'name',
                'label' => 'lang:form_name',
                'rules' => 'required|min_length[3]|max_length[255]'
            ),
            array(
                'field' => 'description',
                'label' => 'lang:form_description',
                'rules' => ''
            ),
            array(
                'field' => 'domain',
                'label' => 'lang:form_domain',
                //@todo Il faut ajouter une règle pour voir si l'id fait parti d'un champ dans la table
                'rules' => 'required|is_natural'
            ),
            array(
                'field' => 'mode',
                'label' => 'lang:form_mode',
                'rules' => 'required'
            )
        );


        $this->form_validation->set_rules($config);
    }

}

?>
