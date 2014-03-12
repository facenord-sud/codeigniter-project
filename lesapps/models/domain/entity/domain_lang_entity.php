<?php

/**
 * Contient les domaines d'études
 * Base de données traduite étendue de la classe abstraite AbstractTranslation.
 *
 * @author yves
 */

require_once APPPATH.'models/language/abstractTranslation.php';

class Domain_lang_entity extends AbstractTranslation {
    
    /**
     * Nom du domaine
     *
     * @var string : varchar(255)
     */
    public $name;
    
    /**
     * Description du domaine
     *
     * @var text
     */
    public $description;
}

?>
