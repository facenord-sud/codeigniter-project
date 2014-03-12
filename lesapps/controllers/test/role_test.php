<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of role_test
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
require_once APPPATH.'controllers/test/Toast.php';
class Role_test extends Toast {

    public function Role_test() {
        parent::Toast(__FILE__);
        // Load any models, libraries etc. you need here
        $this->load->entity('user/role', 'role');
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
    
    public function test_traduction() {
        $rolesDt = $this->query->setLanguage('dt')->select('role');
        $rolesDe = $this->query->setLanguage('de')->select('role');
        $this->message = "Test si les rôles dans différentes langues sont toujours trouvés";
        $this->_assert_equals($rolesDe, $rolesDt);
    }
    
    public function test_traduction2() {
        $this->compare('', '');
    }

}

?>
