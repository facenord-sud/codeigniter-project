<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Entity
 *
 * @author leo
 * @property QueryBuilder $query
 * @property Dmo $dmo
 * @property Form $formBuilder
 */
abstract class Entity {

    private $query;
    private $dmo;
    private $object;
    private $var;
    private $formBuilder;

    public function __construct() {
        $CI = & get_instance();

        $this->dmo = $CI->dmo;
        $this->query = $CI->query;
//        $this->formBuilder = $CI->formBuilder;
    }

    public function __get($name = '') {
        $entityName = $name;
        if (preg_match('`[a-z]+_as_[a-z]+`', $name)) {
            $entityName = str_replace('_as_', '', strstr($name, '_as_'));
        }
        if (preg_match('`_mto$`', $name)) {
            $entityName = str_replace('_mto', '', $entityName);
        } else {
            $entityName = preg_replace('`^_`', '', $name);
        }
        $entityName = $entityName . '_entity';
        $entity = new $entityName();
        $entity->setObject($this);
        $entity->setVar($name);
        $entity->setDmo($this->dmo);
        $entity->setQuery($this->query);
        return $entity;
    }

    public function order($field, $asc = TRUE) {
        $this->query->order($field, $asc);
        return $this;
    }

    public function limit($limit1, $limit2 = 0) {
        $this->query->limit($limit1, $limit2);
        return $this;
    }

    public function where($name, $var, $op = 'AND', $equals = '=') {
        $this->query->where($name, $var, $op, $equals);
        return $this;
    }

    public function exclude($name, $var, $op = 'AND') {
        $this->query->where($name, $var, $op, '!=');
        return $this;
    }

    public function filter($name, $var, $op = 'AND') {
        $this->query->where($name, $var, $op, '=');
        return $this;
    }

    public function what($fieldsData) {
        $this->query->fields($fieldsData);
        return $this;
    }

    public function get() {
        if (!empty($this->object) and !empty($this->var)) {
            if (preg_match('`_mto$`', $this->var)) {
//                $key = 'id_'.$this->getQuery()->getDbForge()->getTable($this->object);
//                $this->where($key, $this->object->id);
//                $this->var = str_replace('_mto', '', $this->var);
            }
            return $this->dmo->loadRelation($this->object, $this->var);
        } else {
            return $this->dmo->loadAllObject($this);
        }
    }

    public function find($var = 0, $name = 'id') {
//        $CI =& get_instance();
//        $nameEntity = str_replace('_entity', '', get_class($this));
//        if(isset($CI->$nameEntity) and) {
//            
//        }
        $this->dmo->setLoadRelation(FALSE);
        if (empty($var) and $name == 'id') {
            $this->dmo->loadObject($this, $this->id);
        } else {
            $this->query->where($name, $var);
            $this->dmo->loadObject($this);
        }
        return $this;
    }

    public function save() {
        $this->dmo->saveObject($this);
    }

    public function destroy() {
        if (!empty($this->object) and !empty($this->var)) {
            $this->dmo->deleteRealtion($this->object, $this->var);
        } else {
            $this->dmo->deleteObject($this);
        }
    }

    /**
     * permet de donner tous les champs d'un formulaire à une entitée
     * 
     * @param string $validation la règle de validation
     * @return \Entity
     */
    public function hydrate($validation = '') {
        $this->formBuilder->validate($validation);
        if ($this->formBuilder->valid) {
            $posts = $this->formBuilder->get_post();
            foreach ($posts as $key => $post) {
                $this->$key = $post;
            }
        }
        return $this;
    }

    public function count() {
        $tableName = $this->query->getDbForge()->getTable($this);
//        if (!empty($this->object) and !empty($this->var)) {
//            return $this->query->count($tableName.'_' . $this->query->getDbForge()->getTable($this->object));
//        }
        return $this->query->count($tableName);
    }

    /**
     * Ajoute une relation multiple
     * 
     * @return Entity $this
     */
    public function add() {
        //TODO. dire à dmo d'ajouter une relation multiple en fonction de var et object
        
        //permet le chaînage de méthode et gère la cas : $this->doc->add() //ne fait rien
        if (empty($this->object) and empty($this->var)) {
            return $this;
        }
        
        //écrire la suite du code ici::
        
            return $this->object;
    }

    public function getQuery() {
        return $this->query;
    }

    public function setQuery($query) {
        $this->query = $query;
    }

    public function getDmo() {
        return $this->dmo;
    }

    public function setDmo($dmo) {
        $this->dmo = $dmo;
    }

    public function getObject() {
        return $this->object;
    }

    public function setObject($object) {
        $this->object = $object;
    }

    public function getVar() {
        return $this->var;
    }

    public function setVar($var) {
        $this->var = $var;
    }

}

?>
