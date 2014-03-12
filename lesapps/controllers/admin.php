<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of admin
 *
 * @author leo
 */
class Admin extends MY_Controller {
    
    public function index() {
        $this->twiggy->title("page d'administration du site");
    }
}

?>
