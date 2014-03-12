<?php

loadEntity('user');
loadEntity('language');
loadEntity('document');
loadEntity('group', 'group_member');
/**
 * Ceci est l'entité des groupes, groupe comprend tout les types de groupes
 * cad : Université, groupes d'étudiants, etc...
 *
 * @author Yves+numa
 * @since 12.12.2012
 * @version 0.2
 */
class Group_entity extends Entity {
    /**
     * le dossier parent de tous les dossiers de groupes
     */

    const ID_FIELD_GROUP = 1;

    /**
     * ID
     *
     * @var int
     */
    public $id = 0;

    /**
     * Name of the groupe
     *
     * @var String
     */
    public $name = '';

    /**
     * Description of the groupe
     *
     * @var string
     */
    public $description = '';

    /**
     * Password si necessaire
     *
     * @access private
     * @var String
     */
    public $password = '';

    /**
     * Date de création du groupe
     *
     * @var int
     */
    public $date_creation = 0;

    /**
     * Website du groupe
     *
     * @var String
     */
    public $website = '';

    /**
     * le slug pour rechercher par le nom
     * @var String
     */
    public $slug = '';

    /**
     * Les langues que les membres du groupe parlent
     * @var array Language_entity
     */
    private $language = array();
    
    /**
     *
     * @var File_entity le dossier de base du groupe 
     */
    private $file = NULL;
    
    private $group_member_mto = array();
    
    private $minimess_mto = array();


    public function __construct() {
        parent::__construct();
        $this->file = (object) $this->file;
    }
    
    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }
    
    public function getFile() {
        return $this->file;
    }

    public function setFile(File_entity $file) {
        $this->file = $file;
    }
    
    public function getGroup_member_mto() {
        return $this->group_member_mto;
    }

    public function setGroup_member_mto($group_member_mto) {
        $this->group_member_mto = $group_member_mto;
    }

    public function getMinimess_mto() {
        return $this->minimess_mto;
    }

    public function setMinimess_mto($minimess_mto) {
        $this->minimess_mto = $minimess_mto;
    }


}

?>
