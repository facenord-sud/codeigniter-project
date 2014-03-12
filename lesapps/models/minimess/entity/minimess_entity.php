<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of minimess_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class minimess_entity extends Entity {

    public $id = 0;
    public $text = '';
    public $publish_at = 0;
    public $create_at = 0;
    private $reply_as_minimess = NULL;
    private $reply_as_minimess_mto = array();
    private $user = NULL;
    private $group = NULL;

    public function __construct() {
        parent::__construct();
        $this->user = (object) $this->user;
        $this->group = (object) $this->group;
        $this->reply_as_minimess = (object) $this->reply_as_minimess;
    }

    public function save() {
        $this->publish_at = mktime();
        parent::save();
    }

    public function getReply_as_minimess() {
        return $this->reply_as_minimess;
    }

    public function setReply_as_minimess($reply_as_minimess) {
        $this->reply_as_minimess = $reply_as_minimess;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

    public function getReply_as_minimess_mto() {
        return $this->reply_as_minimess_mto;
    }

    public function setReply_as_minimess_mto($reply_as_minimess_mto) {
        $this->reply_as_minimess_mto = $reply_as_minimess_mto;
    }

}

?>
