<?php

/**
 * retourne tous les models utilisé dans le projet.
 *
 * @author leo
 */
class Dao_mysql_factory {
    
    public function getUserModel() {
        return 'user/user_model';
    }
    public function getDomainModel() {
        return 'domain/domain_model';
    }
    public function getDocumentModel() {
        return 'document/document_model';
    }
    public function getLanguageModel() {
        return 'language/language_model';
    }
}

?>
