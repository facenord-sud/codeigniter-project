<?php

/**
 * retourne tous les formulaires utilisés dans le projet.
 *
 * @author Yves
 */
class Handler_factory {
    
    public function getDomainHandler() {
        return 'domain/handler/';
    }
    
    public function getDomainNewHandler() {
        return $this->getDomainHandler().'domain_new_handler';
    }
}

?>
