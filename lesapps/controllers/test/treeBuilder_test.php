<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TreeBuilder
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
require_once APPPATH.'controllers/test/Toast.php';
class TreeBuilder_test extends Toast {

    public function TreeBuilder_test() {
        parent::Toast(__FILE__);
        // Load any models, libraries etc. you need here
        $this->load->orm();
    }

    /**
     * OPTIONAL; Anything in this function will be run before each test
     * Good for doing cleanup: resetting sessions, renewing objects, etc.
     */
    public function _pre() {
        $this->query->setLanguage(1)
                ->setTableName('domain');
    }

    /**
     * OPTIONAL; Anything in this function will be run after each test
     * I use it for setting $this->message = $this->My_model->getError();
     */
    public function _post() {
        
    }
    
    /**
     * 
     */
    public function test_getTreeWithAssoc() {
        $res = $this->tree->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Tester la récupération d'un arbre entier en tableau associatif<br>avec la table domain";
    }
    
    public function test_getTreeWithoutTranslatedEntity() {
        $this->query->setLanguage(0)
                ->setTableName('file');
        $res = $this->tree->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Tester getTree avec une entité traduite sans traduction";
    }


    public function test_getTreeWithObjects() {
        $this->load->entity('domain/domain', 'domain');
        $res = $this->tree->setObject($this->domain)->getTree();
        $this->_assert_not_empty($res);
        $this->message="Tester la récupération d'un arbre entier en tableau d'objets<br>avec l'entité domain";
    }
    
    public function test_getLeaves() {
        $this->_assert_not_empty($this->tree->getLeaves());
    }
    
    public function test_getNodes() {
        $this->_assert_not_empty($this->tree->getNodes());
    }
}
?>
