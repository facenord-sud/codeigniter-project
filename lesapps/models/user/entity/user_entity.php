<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

require_once APPPATH . 'models/user/entity/contact_entity.php';
require_once APPPATH . 'models/user/entity/role_entity.php';
require_once APPPATH . 'models/friend/entity/friend_entity.php';
require_once APPPATH . 'models/user/entity/address_entity.php';

/**
 * La classe User qui gère n'importe quel utilisateur de base
 *
 * @table user
 * @author Numa
 * @since 22.11.2012
 * @version 0.2
 */

class User_entity extends Entity{

    /**
     * L'id du membre
     * 
     * @Type("int(11)")
     * @Key("PRIMARY KEY")
     * @NotNull(true)
     * @Extra("AUTO_INCREMENT")
     * 
     * @var int >0 index
     */
    public $id = 0;

    /**
     * Psuedo du membre
     * 
     * @Type("varchar(255)")
     * 
     * @var String lettre de a-z et A-Z chiffre de 0-9 et ._(une seule fois)
     * YVES: Es-tu certain de ça ? Pourquoi ne pas tout accépter ? 
     * Souvant des gens s'appèle "S@m?" et ça ne pose pas de problème.
     */
    public $username = '';

    /**
     * Adresse email
     * 
     * @Type("varchar(255)")
     * @var string
     */
    public $email = '';

    /**
     * Compte activé ou pas (activé l'adresse mail avec le mail de confirmation?)
     *
     * @Type("tinyint(1)")
     * 
     * @var boolean
     */
    public $enabled = false;

    /**
     * Mot de passe
     *
     * @Type("varchar(255)")
     * 
     * @var String lettre de a-z et A-Z chiffre de 0-9 et ._*:-
     * YVES : La aussi j'accépterais tout !
     */
    public $password = '';

    /**
     * Timestamp de la date d'inscription
     *
     * @Type("timestamp")
     * 
     * @var int
     */
    public $date_creation = null;

    /**
     * Timestamp de la dernière connexion
     *
     * @Type("timestamp")
     * 
     * @var int
     */
    public $last_login = null;

    /**
     * Compte blocké pour une certain temps ?
     * Yves, il faudrait donc ajouter une variable $locked_time, qui dit combien de temps il est lock ?
     *
     * @Type("tinyint(1)")
     * 
     * @var Boolean
     */
    public $locked = false;

    /**
     * Compte banni ?
     *
     * @Type("tinyint(1)")
     * 
     * @var boolean
     */
    public $banned = false;

    /**
     * le nombre de points
     *
     * @Type("int(11)")
     * 
     * @var int
     */
    public $points = 0;

    /**
     * Le tableau de tous le roles que peut a voir un utilisateur (simple utilisateur,
     * modérateur, etc)
     * 
     * @Relation("MTM")
     * 
     * @table role
     * @var array par défaut tous le monde est simple utilisateur
     */
    private $role = array();

    /**
     * Le nom et prénom de l'utilisateur
     * 
     * @Relation("MTo")
     * 
     * @table role
     * @var contact_enotity
     */
    private $contact = NULL;

    /**
     * les addresses, lieu où s'est trouvé l'utilisateur
     * @var array
     */
    private $address = array();

    /**
     * les amis de l'utilisateur
     * 
     * @Relation("MTo")
     * 
     * @table friend
     * @var array
     */
    private $friend = array();
    
    private $group_member_mto = array();



    public function __construct() {
        parent::__construct();
        $this->contact = (object) $this->contact;
    }

    public function setPassword($password) {
        $this->password = hash('sha512', $password);
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function addRole($role) {
        array_push($this->role, $role);
    }

    public function removeRole($role) {
        array_pop($this->role, $role);
    }

    public function getContact() {
        return $this->contact;
    }

    public function setContact($contact) {
        $this->contact = $contact;
    }

    public function getFriend() {
        if (isset($this->friend[0]) and !$this->friend[0]) {
            $this->friend = array();
        }
        return $this->friend;
    }

    public function setFriend($friend) {
        $this->friend = $friend;
    }

    public function getAddress() {
        return $this->address;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    public function addAddress($address) {
        array_push($this->address, $address);
    }

    public function getGroup_member_mto() {
        return $this->group_member_mto;
    }

    public function setGroup_member_mto($group_member_mto) {
        $this->group_member_mto = $group_member_mto;
    }

}

/* end of class User */
?>
