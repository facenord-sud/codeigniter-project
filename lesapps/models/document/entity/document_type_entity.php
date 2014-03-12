<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Le type de document (dissertation, résumé, lettre de motivation, etc...)
 *
 * @relations : 
 * one to one avec document, 
 * 
 * @author yves
 */

class Document_type_entity extends Entity {
    
    /**
     * Id du type
     *
     * @var int
     */
    public $id;
    
    /**
     * Nom du type
     *
     * @var varchar(100)
     */
    public $name;
    
    /**
     * Description du type
     *
     * @var text
     */
    public $description = '';
    
    /**
     * La langue
     *
     * @var int
     */
    protected $language = '';
    
    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
    }
}
