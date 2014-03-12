<?php

/**
 * permet de tester les requêtes multiples
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
require_once APPPATH . 'controllers/test/Toast.php';

class multiple_join_test extends Toast {

    public function multiple_join_test() {
        parent::Toast(__FILE__);
        $this->load->entity('document/document');
    }

    /**
     * OPTIONAL; Anything in this function will be run before each test
     * Good for doing cleanup: resetting sessions, renewing objects, etc.
     */
    public function _pre() {
        
    }

    /**
     * OPTIONAL; Anything in this function will be run after each test
     * I use it for setting $this->message = $this->My_model->getError();
     */
    public function _post() {
        
    }

    /**
     * Test si la requête fonction
     */
    public function test_query_run() {
        $this->query
                ->addMultipleJointure('document_author', array('name', 'id_user'))
                ->join('document_author', "document.id_document_author", "document_author.id")
                ->fields (array('title', 'description', 'date'))
                ->select('document');
    }

}

?>
