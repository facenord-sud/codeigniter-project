<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Form Class
 *
 */
class MY_Form extends Form {

    function __construct() {
        parent::__construct();
    }

    /**
     * YVES: J'ajoute le fait que ça mette auto un label si il est sous TRUE
     * 
     * @param type $nameid
     * @param type $label
     * @param type $rules
     * @param type $value
     * @param type $atts
     * @return \MY_Form
     */
    public function text($nameid, $label = TRUE, $rules = '', $value = '', $atts = array()) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_' . $nameid);
        }
        parent::text($nameid, $label, $rules, $value, $atts);
        return $this;
    }

    public function textarea($nameid, $label = TRUE, $rules = '', $value = '', $atts = array()) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_' . $nameid);
        }
        parent::textarea($nameid, $label, $rules, $value, $atts);
        return $this;
    }

    function select($nameid, $options = array(), $label = TRUE, $selected = '', $rules = '', $atts = array()) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_' . $nameid);
        }
        parent::select($nameid, $options, $label, $selected, $rules, $atts);
        return $this;
    }

    // J'ai changé comme les autres et ajouter le required sous TRUE
    public function upload($nameid, $label = TRUE, $required = FALSE, $atts = array()) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_' . $nameid);
        }
        parent::upload($nameid, $label, $required, $atts);
        return $this;
    }

    public function submit($value = TRUE, $nameid = 'submit', $atts = array()) {
        if ($value === TRUE) {
            $value = $this->CI->lang->line('form_' . $nameid);
        }
        parent::submit($value, $nameid, $atts);
        return $this;
    }
    
    public function recaptcha($label = TRUE) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_recaptcha');
        }
        parent::recaptcha($label);
        return $this;
    }

    /*
     * J'écrit mes propres fonction html5
     * number : Input type number
     * 
     */

    public function number($nameid, $label = TRUE, $rules = '', $value = '', $atts = array()) {
        if ($label === TRUE) {
            $label = $this->CI->lang->line('form_' . $nameid);
        }

        $info = $this->_make_info($atts);
        $this->_make_nameid($nameid, $info);
        $this->_check_name($info['name'], 'number');
        $info['type'] = 'number';
        $info['label'] = $this->_make_info($atts, 'label');
        $info['label_text'] = $label;
        $info['rules'] = $rules;
        $info['value'] = $value;
        $this->add($info);
        return $this;
    }

    public function makeDropDown($pdoArray, $view = 'name', $select = 'id') {
        $dropDownArray = array();
        foreach ($pdoArray as $value) {
            if (is_object($value)) {
                $dropDownArray[$value->$select] = $value->$view;
            } elseif (is_array($value)) {
                $dropDownArray[$value[$select]] = $value[$view];
            } else {
                throw new Exception('The first parameter must be an array or an object');
            }
        }
        return $dropDownArray;
    }
    
    /**
     * Validate
     * 
     * Validates the form
     */
    function validate($url = '') {
        $this->_check_post();

        if ($this->_posted) {
            $this->CI->load->library('form_validation');

            // set validation rules for elements
            foreach ($this->_elements as $el) {
                if ($el['name']) {
                    $name = $el['name'];
                    $element = $el['unique'];
                    $type = $el['type'];

                    $label = (!empty($this->$element->label_text)) ? $this->$element->label_text : ucfirst($element);

                    if (!empty($this->$element->rules)) {
                        if (!empty($this->$element->group_label))
                            $label = $this->$element->group_label;
                        if (!empty($this->$element->err_label))
                            $label = $this->$element->err_label;
                        // YVES: On interdi qu'il mette des rules en dur
                        //$this->CI->form_validation->set_rules($name, $label, $this->$element->rules);
                    }
                    else {
                        // YVES : J'ai viré le fait que ça ajoute une règle si il n'y en a pas.
                        //$this->CI->form_validation->set_rules($name, $label);
                    }
                }
            }

            // run validation
            if ($this->CI->form_validation->run($url) == FALSE)
                $this->valid = FALSE;

            // re-populated form even though the validation might have passed above, we
            // need to re-populate the data if captchas, uploads or models fail below
            $errors = array();
            foreach ($this->_elements as $el) {
                if ($el['name']) {
                    $name = $el['name'];
                    $element = $el['unique'];
                    $type = $el['type'];

                    switch ($type) {
                        case 'select':
                            $selected = array();
                            // loop through all options to get selected value(s)
                            foreach ($this->$element->options as $key => $val) {
                                if (is_array($val)) {
                                    // loop through optgroup
                                    foreach ($val as $option_key => $val) {
                                        if (set_select($name, $option_key))
                                            $selected[] = $option_key;
                                    }
                                }
                                else {
                                    if (set_select($name, $key))
                                        $selected[] = $key;
                                }
                            }
                            $this->$element->selected = $selected;
                            break;

                        case 'checkbox':
                            $checked = set_checkbox($name, $this->$element->value);
                            $this->$element->atts['checked'] = ($checked) ? TRUE : FALSE;
                            //old: $this->$element->atts['checked'] = (is_array($checked)) ? in_array($this->$element->value, $checked) : $this->$element->value == $checked;
                            break;

                        case 'radio':
                            $checked = set_radio($name, $this->$element->value);
                            $this->$element->atts['checked'] = ($checked) ? TRUE : FALSE;
                            break;

                        case 'submit':
                        case 'reset':
                            $this->$element->atts['value'] = $this->$element->value;
                            break;

                        case 'password':
                            // clear password field
                            $this->$element->atts['value'] = '';
                            break;

                        case 'hidden':
                            // Yves : Changer area en textarea
                        case 'textarea':
                            $this->$element->value = set_value($name);
                            break;

                        default:
                            $this->$element->atts['value'] = set_value($name);
                    }
                                            


                    $error = form_error($name, $this->error_open, $this->error_close);
                    if ($error)
                        $errors[$name] = array($element, $error);
                }
            }

            // display element errors if $this->form_validation->run() failed
            if ($this->valid == FALSE) {
                foreach ($errors as $element => $error) {
                    $element = $error[0];
                    $error = $error[1];
                    $type = $this->$element->type;

                    // replace RULE specific error message with ELEMENT specific error message
                    if (!empty($this->$element->err_message))
                        $error = $this->error_open . $this->$element->err_message . $this->error_close;

                    $this->$element->error = $error; // this adds the full error string (including error_open and error_close) to the element
                    $message = str_replace($this->error_open, '', $error);
                    $message = str_replace($this->error_close, '', $message);
                    $this->$element->error_message = $message; // this adds the inline error to the element
                    if ($type != 'checkbox' && $type != 'radio')
                        $this->$element->atts['class'] = (isset($this->$element->atts['class'])) ? $this->$element->atts['class'] . ' ' . $this->config['error_class'] : $this->config['error_class'];
                    $this->error[] = $message;

                    $this->error_array[$element] = $message;
                    $this->error_string .= $error;
                }
            }

            //YVES : J'ai viré car le captcha est vérifier par form validation
            // validate captcha
//            if ($this->captcha && !$this->_valid_captcha($this->CI->input->post('captcha'))) {
//                $this->CI->lang->load($this->lang_file, $this->lang);
//                $this->add_error('captcha', $this->CI->lang->line('incorrect-captcha-sol'));
//                $this->valid = FALSE;
//            }
//
//            // validate recaptcha
//            if ($this->recaptcha) {
//                if (!$this->_valid_recaptcha($this->recaptcha)) {
//                    $this->CI->lang->load($this->lang_file, $this->lang);
//
//                    if ($this->recaptcha_error) {
//                        $this->recaptcha_response_field->recaptcha_error = $this->recaptcha_error;
//                        $this->add_error('recaptcha_response_field', $this->CI->lang->line($this->recaptcha_error));
//                        $this->valid = FALSE;
//                    }
//                }
//            }
            
            // YVES : Je vire pour gérer seul
            // upload file data
            //$this->_upload_files();

            // only load models if all validation above passed
            if ($this->valid) {
                $this->_load_model();
            }

            // is form still valid after models?
            if (!$this->valid) {
                // unlink uploaded files if validation failed
                foreach ($this->_data as $el) {
                    unlink($el['full_path']);
                }
            }

            // validation complete			
            $this->validated = TRUE;
        } else {
            $this->valid = FALSE;
        }

        return $this;
    }
}

// END Form Class

/* End of file Form.php */
/* Location: ./system/libraries/Form.php */
