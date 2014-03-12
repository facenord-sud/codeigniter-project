<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Cette classe abstraite permet d'être implémentée pour créer un arbre de données intervallaires
 * et le gérer
 *
 * @property TreeBuilder $tree
 * @author yves
 */
abstract class AbstractTree extends Entity {

    /**
     * Id
     *
     * @var int
     */
    public $id;

    /**
     * Id du parent
     *
     * @var int
     */
    public $prt = 0;

    /**
     * Borne droite
     *
     * @var int
     */
    public $rgt = 2;

    /**
     * Borne gauche
     *
     * @var int
     */
    public $lft = 1;

    /**
     * Level de l'entité
     *
     * @var int
     */
    public $lvl = 0;

    private $tree = NULL;

    function __construct() {
        parent::__construct();
        $this->tree = &get_instance()->tree;
    }

    private function _prepareToGetTree($assoc = FALSE) {
        $this->getDmo()->setFieldsToQuery($this);
        $langId = $this->getDmo()->getTagLang($this);
        $this->getQuery()->setLanguage($langId);
        if ($assoc) {
            $this->query->setTableName($this);
        } else {
            $this->tree->setObject($this);
        }
    }

    public function getTree($assoc = FALSE) {
        $this->_prepareToGetTree($assoc);
        return $this->tree->getTree();
    }
    
    //getleaves etc
    //TODO compléter

}

?>
