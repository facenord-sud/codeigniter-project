<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Cette entité est le degré d'étude. (primaire, secondaire, universitaire, etc..)
 *
 * @author yves
 */
class Study_level_entity extends Entity {
    
    /**
     * Id
     *
     * @var int
     */
    public $id;
    
    /**
     * Nom du dégré
     *
     * @var vachar(255)
     */
    public $name;
    
    
    /**
     * Description niveau d'étude
     *
     * @var text
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
