<?php

/**
 * Description of group_creat_form
 *
 * @author leo
 */
class group_creat_form extends AbstractFormular{
    
    protected function form() {
        $this->requiredForm();
        $this->optionalForm();
    }
    
    public function requiredForm() {
         my_form('name');
        my_form('description', '', 'text');
    }

    public function optionalForm() {
        my_form('is_private', '', 'cb');
        echo '<div id="private-groupe">';
        my_form('password', '', 'password');
        my_form('password2', '', 'password');
        echo "</div>";
        my_form('website', '', 'url');
    }

}

?>
