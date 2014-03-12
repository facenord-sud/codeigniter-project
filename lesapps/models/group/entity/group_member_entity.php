<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Group_member_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class group_member_entity extends Entity {

    /**
     * L'id de l'entitée
     * 
     * @Type("int(11)")
     * @Key("PRIMARY KEY")
     * @NotNull(true)
     * @Extra("AUTO_INCREMENT")
     * 
     * @var int
     */
    public $id = 0;
    
    public $since = 0;
    
    private $user = NULL;
    
    private $role = NULL;
    
    private $group = NULL;


    public function __construct() {
        parent::__construct();
        $this->user = (object) $this->user;
        $this->role = (object ) $this->role;
        $this->group = (object) $this->group;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }
    
    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }
    
    public function getGroup() {
        return $this->group;
    }

    public function setGroup($group) {
        $this->group = $group;
    }

}

?>
