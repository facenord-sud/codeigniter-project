<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Entité des documents, le fichier relier au document est dans une autre entité, car il peut y en avoir plusieur (versioning).
 *
 * @relations : 
 * many to many avec domain, 
 * one to one avec user, 
 * many to many avec les id des membres qui ont sont propriétaire, 
 * one to one avec le type (dissert, résumé, etc..),
 * one to one avec le degrée d'étude du doc
 * many to many a groupe
 * many to one avec les commentaires (+évaluation)
 * 
 * @author yves
 */
loadEntity('file');
loadEntity('document', 'document_author');
loadEntity('user');
loadEntity('language');
loadEntity('document', 'document_type');
loadEntity('domain');
loadEntity('study', 'study_level');
loadEntity('file');

class Document_entity extends Entity {

    /**
     * id du document
     *
     * @var int
     */
    public $id;

    /**
     * Titre du document
     *
     * @var string : varchar(255)
     */
    public $title;

    /**
     * Description du document
     *
     * @var text
     */
    public $description = '';

    /**
     * Date d'envoi du document
     *
     * @var int
     */
    public $date;

    /**
     * Nombre de fois que le document a été modifié (pas le fichier)
     *
     * @var int
     */
    public $edit = 0;

    /**
     * Date de la dernière modification
     *
     * @var int
     */
    public $date_edit = 0;

    /**
     * Année de réalisation du document
     *
     * @var int(5)
     */
    public $date_realisation = 0;

    /**
     * Nombre de fois qu'on a vu ce document
     *
     * @var int
     */
    public $see = 0;

    /**
     * Nombre de fois qu'on a téléchargé le fichier relier au document 
     * (l'addition des hits de chaque file relié à ce document).
     *
     * @var int
     */
    public $hit = 0;
    public $page = 0;
    
    /**
     * Fichier lié au document (versionning)
     *
     * @var 
     */
    private $file = array();

    /**
     * le membre qui l'a envoyé au début
     *
     * @var lié à user_entity
     */
    private $user = NULL;

    /**
     * degré d'étude auquel ce document correspond.
     *
     * @var int
     */
    private $study_level = NULL;

    /**
     * Auteur du document (noms ou pseudo des auteurs)
     *
     * @var 
     */
    private $author = array();

    /**
     * Domaines lié au document
     *
     * @var 
     */
    private $domain = array();

    /**
     * La langue du document
     *
     * @var int
     */
    private $language = NULL;

    /**
     * Type de document (résumé, dissert, etc..)
     *
     * @var int
     */
    private $document_type = NULL;

    function __construct() {
        parent::__construct();
        $this->user = (object) $this->user;
        $this->language = (object) $this->language;
        $this->document_type = (object) $this->document_type;
        $this->study_level = (object) $this->study_level;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function addDomain($domain) {
        array_push($this->domain, $domain);
    }

    public function setDomain($domain) {
        $this->domain = $domain;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($author) {
        $this->author = $author;
    }

    public function addAuthor($author) {
        array_push($this->author, $author);
    }

    public function getDocument_type() {
        return $this->document_type;
    }

    public function setDocument_type($document_type) {
        $this->document_type = $document_type;
    }

    public function getStudy_level() {
        return $this->study_level;
    }

    public function setStudy_level($study_level) {
        $this->study_level = $study_level;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }

    public function addFile($file) {
        array_push($this->file, $file);
    }

}

?>
