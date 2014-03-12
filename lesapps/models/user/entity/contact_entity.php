<?php

/**
 * Contient les coordonnées du membres si il les a mises 
 * (relié à la table Member grâce à l'id du membre).
 *
 * @author yves
 */

class Contact_entity extends Entity{
    
    /**
     * @Type("int(11)")
     * @Key("PRIMARY KEY")
     * @NotNull(true)
     * @Extra("AUTO_INCREMENT")
     * @var int 
     */
    public $id;
    
    /**
     *
     * @Type("varchar(255)")
     */
    public $f_name;
    
    /**
     * @Type("varchar(255)")
     * @var type String
     */
    public $last_name;
}

?>
