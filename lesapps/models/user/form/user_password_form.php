<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_password_form
 *
 * @author leo
 */
class user_password_form extends AbstractFormular{
    protected function form() {
        my_form('old_password', 'old_password', 'password');
        my_form('password', 'password', 'password');
       my_form('password2', 'password2', 'password');
    }    
}

?>
