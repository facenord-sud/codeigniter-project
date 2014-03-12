<?php

/**
 * retourne tous les formulaires utilisés dans le projet.
 *
 * @author Yves
 */
class Form_factory {
    
    public function getDomainForm() {
        return 'domain/form/';
    }
    
    public function getUserForm() {
        return 'user/form/';
    }

        public function getDomainNewForm() {
        return $this->getDomainForm().'domain_new_form';
    }
    
    public function getDomainDeleteForm() {
        return $this->getDomainForm().'domain_delete_form';
    }
    
    public function getDomainEditForm() {
        return $this->getDomainForm().'domain_edit_form';
    }
    
    public function getUserNewForm() {
        return $this->getUserForm().'user_new_form';
    }
    
    public function getUserConnectForm() {
        return $this->getUserForm().'user_connect_form';
    }
}

?>
