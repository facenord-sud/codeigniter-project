<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Owner_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class Owner_entity extends Entity {

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

    private $user = array();
    
    private $group = array();
    
    private $right = array();
    
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

    public function getRight() {
        return $this->right;
    }

    public function setRight($right) {
        $this->right = $right;
    }

}

?>
