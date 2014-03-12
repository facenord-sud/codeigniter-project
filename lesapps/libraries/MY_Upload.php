<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * File Uploading Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Uploads
 * @author		Yves
 * @link		http://codeigniter.com/user_guide/libraries/file_uploading.html
 */
class MY_Upload extends CI_Upload {

    // Determine si l'envoie d'un fichier est obligatoire
    protected $required_file = TRUE;
    // Determine si un file a été envoyé ou non
    protected $file_uploaded = FALSE;

    function __construct($props = array()) {
        parent::__construct($props);
    }

    /**
     * Perform the file upload
     * YVES : Mais ne fais pas la vérification !
     * Pour garder la portabilité, je laisse une variable $check qui fait la vérification si elle contien le field
     *
     * @return	bool
     */
    public function do_upload($field = FALSE) {

        if ($field) {
            $this->check_file($field);
        }

            /*
             * Move the file to the final destination
             * To deal with different server configurations
             * we'll attempt to use copy() first.  If that fails
             * we'll use move_uploaded_file().  One of the two should
             * reliably work in most environments
             */
            if (!@copy($this->file_temp, $this->upload_path . $this->file_name)) {
                if (!@move_uploaded_file($this->file_temp, $this->upload_path . $this->file_name)) {
                    $this->set_error('upload_destination_error');
                    return FALSE;
                }
            }

            /*
             * Set the finalized image dimensions
             * This sets the image width/height (assuming the
             * file was an image).  We use this information
             * in the "data" function.
             */
            $this->set_image_properties($this->upload_path . $this->file_name);

        return TRUE;
    }

    /**
     * Fait la vérification que le fichier peut être envoyé, si la deuxième variable est true, il envoie le file
     * Yves : J'ai repris la partie de vérification du do_upload de la librairie de CI
     * 
     * $field détemine le nom du field sur le formulaire
     * $required bool determine si le file est obligatoir ou pas
     * $upload détermine si ça envoie le file après vérification
     * 
     * @return bool
     */
    public function check_file($field = 'userfile', $required_file = TRUE, $upload = FALSE) {

        $this->setRequired_file($required_file);
        $this->setFile_uploaded(!($_FILES[$field]['error'] == 4));

        // SI le file n'est pas requis, et qu'il n'y en a pas, on return true
        if (($this->required_file == FALSE) && ($this->file_uploaded == FALSE)) {
            return TRUE;
        }


        // Is $_FILES[$field] set et file required? If not, no reason to continue.
        if (!isset($_FILES[$field])) {
            $this->set_error('upload_no_file_selected');
            return FALSE;
        }


        // Is the upload path valid?
        if (!$this->validate_upload_path()) {
            // errors will already be set by validate_upload_path() so just return FALSE
            return FALSE;
        }

        // Was the file able to be uploaded? If not, determine the reason why.
        if (!is_uploaded_file($_FILES[$field]['tmp_name'])) {
            $error = (!isset($_FILES[$field]['error'])) ? 4 : $_FILES[$field]['error'];

            switch ($error) {
                case 1: // UPLOAD_ERR_INI_SIZE
                    $this->set_error('upload_file_exceeds_limit');
                    break;
                case 2: // UPLOAD_ERR_FORM_SIZE
                    $this->set_error('upload_file_exceeds_form_limit');
                    break;
                case 3: // UPLOAD_ERR_PARTIAL
                    $this->set_error('upload_file_partial');
                    break;
                case 4: // UPLOAD_ERR_NO_FILE
                    $this->set_error('upload_no_file_selected');
                    break;
                case 6: // UPLOAD_ERR_NO_TMP_DIR
                    $this->set_error('upload_no_temp_directory');
                    break;
                case 7: // UPLOAD_ERR_CANT_WRITE
                    $this->set_error('upload_unable_to_write_file');
                    break;
                case 8: // UPLOAD_ERR_EXTENSION
                    $this->set_error('upload_stopped_by_extension');
                    break;
                default : $this->set_error('upload_no_file_selected');
                    break;
            }

            return FALSE;
        }


        // Set the uploaded data as class variables
        $this->file_temp = $_FILES[$field]['tmp_name'];
        $this->file_size = $_FILES[$field]['size'];
        $this->_file_mime_type($_FILES[$field]);
        $this->file_type = preg_replace("/^(.+?);.*$/", "\\1", $this->file_type);
        $this->file_type = strtolower(trim(stripslashes($this->file_type), '"'));
        $this->file_name = $this->_prep_filename($_FILES[$field]['name']);
        $this->file_ext = $this->get_extension($this->file_name);
        $this->client_name = $this->file_name;

        // Is the file type allowed to be uploaded?
        if (!$this->is_allowed_filetype()) {
            $this->set_error('upload_invalid_filetype');
            return FALSE;
        }

        // if we're overriding, let's now make sure the new name and type is allowed
        if ($this->_file_name_override != '') {
            $this->file_name = $this->_prep_filename($this->_file_name_override);

            // If no extension was provided in the file_name config item, use the uploaded one
            if (strpos($this->_file_name_override, '.') === FALSE) {
                $this->file_name .= $this->file_ext;
            }

            // An extension was provided, lets have it!
            else {
                $this->file_ext = $this->get_extension($this->_file_name_override);
            }

            if (!$this->is_allowed_filetype(TRUE)) {
                $this->set_error('upload_invalid_filetype');
                return FALSE;
            }
        }

        // Convert the file size to kilobytes
        if ($this->file_size > 0) {
            $this->file_size = round($this->file_size / 1024, 2);
        }

        // Is the file size within the allowed maximum?
        if (!$this->is_allowed_filesize()) {
            $this->set_error('upload_invalid_filesize');
            return FALSE;
        }

        // Are the image dimensions within the allowed size?
        // Note: This can fail if the server has an open_basdir restriction.
        if (!$this->is_allowed_dimensions()) {
            $this->set_error('upload_invalid_dimensions');
            return FALSE;
        }

        // Sanitize the file name for security
        $this->file_name = $this->clean_file_name($this->file_name);

        // Truncate the file name if it's too long
        if ($this->max_filename > 0) {
            $this->file_name = $this->limit_filename_length($this->file_name, $this->max_filename);
        }

        // Remove white spaces in the name
        if ($this->remove_spaces == TRUE) {
            $this->file_name = preg_replace("/\s+/", "-", $this->file_name);
        }

        /*
         * Validate the file name
         * This function appends an number onto the end of
         * the file if one with the same name already exists.
         * If it returns false there was a problem.
         */
        $this->orig_name = $this->file_name;

        if ($this->overwrite == FALSE) {
            $this->file_name = $this->set_filename($this->upload_path, $this->file_name);

            if ($this->file_name === FALSE) {
                return FALSE;
            }
        }

        /*
         * Run the file through the XSS hacking filter
         * This helps prevent malicious code from being
         * embedded within a file.  Scripts can easily
         * be disguised as images or other file types.
         */
        if ($this->xss_clean) {
            if ($this->do_xss_clean() === FALSE) {
                $this->set_error('upload_unable_to_write_file');
                return FALSE;
            }
        }

        if ($upload) {
            return $this->do_upload();
        } else {
            return TRUE;
        }
    }

    public function getRequired_file() {
        return $this->required_file;
    }

    public function setRequired_file($required_file) {
        $this->required_file = (is_bool($required_file)) ? $required_file : TRUE;
    }

    public function getFile_uploaded() {
        return $this->file_uploaded;
    }

    public function setFile_uploaded($file_uploaded) {
        $this->file_uploaded = (is_bool($file_uploaded)) ? $file_uploaded : FALSE;
    }

}

?>
