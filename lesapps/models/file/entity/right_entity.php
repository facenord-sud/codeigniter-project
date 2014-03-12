<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Right_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class Right_entity extends Entity {

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

    public $name = '';
    
    public $description = '';
    
    protected $language =0;
    
    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }
}

?>
