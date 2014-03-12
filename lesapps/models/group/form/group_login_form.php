<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of group_login_form
 *
 * @author leo
 */
class group_login_form extends AbstractFormular{
    protected function form() {
        my_form('password', 'password', 'password');
    }    
}

?>
