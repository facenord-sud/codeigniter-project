<?php

loadEntity('user');
loadEntity('group');

/**
 * Description of invitation_entity
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class invitation_entity extends Entity {

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

    /**
     * depuis quand l'inviation à été faite
     * 
     * @var int unix time stamp 
     */
    public $date_of_invit = 0;

    /**
     * l'id de la personne invitée
     * 
     * @var int 
     */
    public $id_invinting = 0;

    /**
     * le mail de la personne invitée au cas où elle n'es pas sur collaide
     * @var string
     */
    public $mail_invited = '';
    
    /**
     * le text que peut écrire quelqu'un en plus de l'invitation
     * @var string 
     */
    public $text = '';


    /**
     * le groupe au quel l'utilisateur est invité
     * 
     * @var Group_entity
     */
    private $group = NULL;

    /**
     * l'utilisateur qui est invité
     * 
     * @var User_entity 
     */
    private $user = NULL;

    

    public function __construct() {
        parent::__construct();
        $this->user = (object) $this->user;
        $this->group = (object) $this->group;
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

    public function setGroup(Group_entity $group) {
        $this->group = $group;
    }

}

?>
