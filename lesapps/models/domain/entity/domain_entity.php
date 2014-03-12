<?php

/**
 * Contient les domaines d'études
 * Base de données intervallaires étendue de la classe abstraite AbstractTree.
 *
 * @author yves
 */

require_once APPPATH.'models/tree/abstractTree.php';

class Domain_entity extends AbstractTree {
    /**
     * Nom du domaine (a traduire)
     *
     * @var string : varchar(255)
     */
    public $name;
    
    /**
     * Description du domaine (a traduire)
     *
     * @var text
     */
    public $description;
    
    /**
     * Id de la langue de l'objet en question
     *
     * @var int
     */
    protected $language = '';
    
    public function __construct() {
        parent::__construct();
    }


    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }
}

?>
