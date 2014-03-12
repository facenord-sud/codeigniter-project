<?php

/**
 * Description of AbstractFormular
 *
 * @author leo
 */
abstract class AbstractFormular extends CI_Model {

    protected abstract function form();

    private $isMultiPart = FALSE;
    private $name = 'submit';

    public function printForm() {
        echo validation_errors();
        if ($this->isMultiPart) {
            echo form_open_multipart();
        } else {
            echo form_open();
        }
        echo form_fieldset($this->lang->line('form_fieldset_title'));
        $this->form();
        echo form_submit($this->name, $this->lang->line('form_submit'));
        echo form_fieldset_close();
        echo form_close();
    }

    public function requiredForm() {
        
    }

    public function optionalForm() {
        
    }

    public function getIsMultiPart() {
        return $this->isMultiPart;
    }

    public function setIsMultiPart($isMultiPart) {
        $this->isMultiPart = $isMultiPart;
    }

    public function setName($name) {
        $this->name = $name;
    }

}

?>
