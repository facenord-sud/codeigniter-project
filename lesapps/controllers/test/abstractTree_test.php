<?php

/**
 * Description of AbstractTree_test
 *
 * @property Domain_entity $domain
 * @property File_entity $file
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
require_once APPPATH . 'controllers/test/Toast.php';

class AbstractTree_test extends Toast {

    public function AbstractTree_test() {
        parent::Toast(__FILE__);
        // Load any models, libraries etc. you need here
        $this->load->entity('domain/domain', 'domain');
        $this->load->entity('file/file', 'file');
    }

    /**
     * OPTIONAL; Anything in this function will be run before each test
     * Good for doing cleanup: resetting sessions, renewing objects, etc.
     */
    public function _pre() {
        $this->domain = new Domain_entity();
    }

    /**
     * OPTIONAL; Anything in this function will be run after each test
     * I use it for setting $this->message = $this->My_model->getError();
     */
    public function _post() {
        
    }

    /**
     * Test la méthode getTree de AbstractTree avec une langue spécifiée existante 
     */
    public function test_getTreeWithSpecifiedLanguage() {
        $this->domain->setLanguage(3);
        $res = $this->domain->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Test la méthode getTree de AbstractTree avec une langue spécifiée traduite";
    }

    /**
     * Test la méthode getTree de AbstractTree avec une langue spécifiée inexistante 
     */
    public function test_getTreeWithUntranslatedLanguage() {
        $this->domain->setLanguage(2);
        $res = $this->domain->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Test la méthode getTree de AbstractTree avec une 
            langue spécifiée non-traduite";
    }

    /**
     * Test la méthode getTree de AbstractTree avec la langue de l'utilisateur
     */
    public function test_getTreeWithTraduction() {
        $res = $this->domain->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Test la méthode getTree de AbstractTree avec la langue de l'utilisateur";
    }

    /**
     * Test la méthode getTree de AbstractTree avec une entitée pas traduite
     */
    public function test_getTreeWithoutTraduction() {
        $res = $this->file->getTree();
        $this->_assert_not_empty($res);
        $this->message = "Test la méthode getTree de AbstractTree avec une entitée pas traduite";
    }

}

?>
