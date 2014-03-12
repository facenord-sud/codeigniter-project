<?php
/**
 * Description of user_contact_form
 *
 * @author leo
 * @property Contact_entity $contact
 */
class user_contact_form extends AbstractFormular {

    private $contact;


    protected function form() {
        my_form('f_name', 'f_name', '', $this->contact->f_name);
        my_form('l_name', 'l_name', '', $this->contact->last_name);
    }

    public function getContact() {
        return $this->contact;
    }

    public function setContact($contact) {
        $this->contact = $contact;
    }
}

?>
