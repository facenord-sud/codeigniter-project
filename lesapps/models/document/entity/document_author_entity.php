<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Auteur ou pseudo des docs
 *
 * @relations : 
 * one to one avec user, 
 * 
 * @author yves
 */
loadEntity('user');

class Document_author_entity extends Entity {

    /**
     * Id
     *
     * @var int
     */
    public $id;

    /**
     * Nom ou pseudo de l'auteur
     *
     * @var varchar(255)
     */
    public $name;

    /**
     * Utilisateur lié à ce nom
     *
     * @var text
     */
    private $user = NULL;

    function __construct() {
        $this->user = (object) $this->user;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

}
