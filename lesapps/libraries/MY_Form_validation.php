<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * Form Validation Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Validation
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class MY_Form_validation extends CI_Form_validation {

    protected $_error_prefix = '<div class="form_error">';
    protected $_error_suffix = '</div>';
    //protected $recaptcha_error = '';
    protected $query;

    function __construct($rules = array()) {
        parent::__construct($rules);
        $CI = & get_instance();
        
        $this->query = $CI->query;
    }

    /**
     * Enumerate
     * Doit contenir au moins une des values énumérée
     * Par défaut, les valeur sont séparé par des ,
     * 
     * @access	public
     * @param	string
     * @param	value
     * @return	bool
     */
    public function enum($str, $val) {
        $enums = explode(',', $val);
        return in_array($str, $enums);
    }

    /**
     * is_field
     * return un boolean qui dit si l'id entré est un champs dans la table
     * 
     * @access	public
     * @param	string
     * @return	bool
     */
    public function is_field($str, $entity) {
        $return = TRUE;
        // Si c'est un array, on verifie que chaque champ est une entité
        if (is_array($str)) {
            foreach ($str as $value) {
                if ($this->query->where('id', $value)->count($entity)!=1) {
                    // Si un est différent de 1, c'est que ce n'est pas une entité : FALSE
                    $return = FALSE;
                }
            }
        }
        // SInon, si c'est un entier, on vérifie qu'il est une entité
        elseif (is_numeric($str)) {
            $return = ($this->query->where('id', $str)->count($entity)==1);
        } else {
            // Sinon on returne false
            $return = FALSE;
        }
        return $return;
    }
    
//    /**
//	 * Run the Validator
//	 *
//	 * This function does all the work.
//	 *
//	 * @access	public
//	 * @return	bool
//	 */
//	public function run($group = '')
//	{
//
//		// Do we even have any data to process?  Mm?
//		if (count($_POST) == 0)
//		{
//			return FALSE;
//		}
//                
//                // Does the _field_data array containing the validation rules exist?
//		// If not, we look to see if they were assigned via a config file
//                // 
//                // YVES: J'ai tout simplement virer cette condition pour que la librairie
//                // prenne les rules du fichier config et pas celles en dure.
//		if (count($this->_field_data) == 0)
//		{
//			// No validation rules?  We're done...
//			if (count($this->_config_rules) == 0)
//			{
//				return FALSE;
//			}
//
//			// Is there a validation rule for the particular URI being accessed?
//			$uri = ($group == '') ? trim($this->CI->uri->ruri_string(), '/') : $group;
//
//			if ($uri != '' AND isset($this->_config_rules[$uri]))
//			{
//				$this->set_rules($this->_config_rules[$uri]);
//			}
//			else
//			{
//				$this->set_rules($this->_config_rules);
//			}
//
//			// We're we able to set the rules correctly?
//			if (count($this->_field_data) == 0)
//			{
//				log_message('debug', "Unable to find validation rules");
//				return FALSE;
//			}
//		}
//
//		// Load the language file containing error messages
//		$this->CI->lang->load('form_validation');
//
//		// Cycle through the rules for each field, match the
//		// corresponding $_POST item and test for errors
//		foreach ($this->_field_data as $field => $row)
//		{   
//			// Fetch the data from the corresponding $_POST array and cache it in the _field_data array.
//			// Depending on whether the field name is an array or a string will determine where we get it from.
//
//			if ($row['is_array'] == TRUE)
//			{
//				$this->_field_data[$field]['postdata'] = $this->_reduce_array($_POST, $row['keys']);
//			}
//			else
//			{
//				if (isset($_POST[$field]) AND $_POST[$field] != "")
//				{
//					$this->_field_data[$field]['postdata'] = $_POST[$field];
//				}
//			}
//
//			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
//		}
//
//		// Did we end up with any errors?
//		$total_errors = count($this->_error_array);
//
//		if ($total_errors > 0)
//		{
//			$this->_safe_form_data = TRUE;
//		}
//
//		// Now we need to re-set the POST data with the new, processed data
//		$this->_reset_post_array();
//
//		// No errors, validation passes!
//		if ($total_errors == 0)
//		{
//			return TRUE;
//		}
//
//		// Validation fails
//		return FALSE;
//	}
        
    /*
     * copier/coller de : http://ellislab.com/forums/viewthread/94299/
     */
    function recaptcha_matches()
    {
        $CI =& get_instance();
        $CI->config->load('form');
        $public_key = $CI->config->item('recaptcha_key_public', 'form');
        $private_key = $CI->config->item('recaptcha_key_private', 'form');
        $response_field = $CI->input->post('recaptcha_response_field');
        $challenge_field = $CI->input->post('recaptcha_challenge_field');
        $response = recaptcha_check_answer($private_key,
                                           $_SERVER['REMOTE_ADDR'],
                                           $challenge_field,
                                           $response_field);

        if ($response->is_valid)
        {
            return TRUE;
        }
        else 
        {
            //$CI->form_validation->recaptcha_error = $response->error;
            //$CI->form_validation->set_message('recaptcha_matches', $this->CI->lang->line('incorrect-captcha-sol'));
            return FALSE;
        }
    }
}

// END Form Validation Class

/* End of file Form_validation.php */
/* Location: ./system/libraries/Form_validation.php */
