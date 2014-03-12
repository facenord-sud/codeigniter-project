<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'models/user/entity/user_entity.php';

/**
 * @access public
 * @author leo
 */
class Friend_entity extends Entity{

    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var boolean
     */
    public $accepted = FALSE;
    
    /**
     *
     * @var boolean 
     */
    public $demand = TRUE;

    /**
     * @var User_entity
     */
    private $user = NULL;

    public function __construct() {
        parent::__construct();
        $this->user = (object) $this->user;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

}

?>