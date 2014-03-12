<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of user_address_form
 *
 * @author leo
 * @property Address_entity $address
 */
class user_address_form extends AbstractFormular {

    private $address;


    protected function form() {
        my_form('name', 'name', '', $this->address->name);
        my_form('country', 'country', '', $this->address->country);
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }


}

?>
