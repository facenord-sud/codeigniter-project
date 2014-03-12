<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_email_form
 *
 * @author leo
 */
class user_email_form extends AbstractFormular{


    public function form() {
        echo my_form('email', 'email', '', $this->user->email);
    }
}

?>
