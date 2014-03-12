<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Role_entity extends Entity{

    const GROUP_ADMIN = 'group_admin';
    const GROUP_WRITER = 'group_writer';
    /**
     * @Type("int(11)")
     * @Key("PRIMARY KEY")
     * @NotNull(true)
     * @Extra("AUTO_INCREMENT")
     * @var int 
     */
    public $id;

    /**
     * @Type("varchar(255)")
     * 
     * @var string 
     */
    public $name;

    /**
     * @Type("varchar(255)")
     * 
     * @var string 
     */
    public $nick_name;

    /**
     * @Type("text")
     * 
     * @var string 
     */
    public $description;

    /**
     * la variable de langue
     * 
     * @var string
     */
    protected $language;
    
    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

}
