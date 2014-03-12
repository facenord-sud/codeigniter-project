<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of File_info_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class File_info_entity extends Entity {

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

    private $file = array();
    
    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }


}
