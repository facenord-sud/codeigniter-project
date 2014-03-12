<?php

/**
 * @property MY_Loader $load
 * @author leo
 * @property QueryBuilder $query
 */
class Dmo {
    /**
     * constante qui permet uniquement le chargement des tableaux de la classe
     * relation MANY_TO_MANY
     */

    const MANY_TO_MANY = 3;

    /**
     * constante qui permet uniquement le chargement des instances de classes de la class
     * relation MANY_TO_ONE
     */
    const MANY_TO_ONE = 4;

    /**
     * constante qui indique de charger tous les tableau ou toutes les classes d'une entité
     */
    const ALL_FIELDS = '*';

    /**
     *
     * @var QueryBuilder $query la classe qui s'occupe de la construction de la requête 
     */
    private $query;

    /**
     *
     * @var array $loadArray le nom des tableaux à charger 
     */
    private $loadArray = array();

    /**
     *
     * @var array $loadClass le nom des classes à charger 
     */
    private $loadClass = array();
    private $loadRelation = FALSE;

    private function _findIdMtO($propertyName, $idEntity, $entityName) {
        $idProperty = $this->query->setTableName($entityName)->fields('id_' . $propertyName)->where('id', $idEntity)->select();
        return $idProperty['id_' . $propertyName];
    }

    private function _instanciateEntity($entityName) {
        $newEntity = ucfirst($entityName) . '_entity';
        return new $newEntity();
    }

    /**
     * retourne un tableau de <code>ReflectionProperty</code> pour chaque variable
     * de l'entité qui corresspondent aux conventions. ie: private array pour le 
     * MANY_TO_MANY objet pour le MANY_TO_ONE
     * 
     * @param arry $loadVars les nom des tableaux ou des classes qui devraient être chargées
     * @param Object $object l'entité
     * @param boolean $array <code>TRUE si c'est les tableaux à charger</code>
     * @return array avec toutes les propietée demandées
     */
    private function _getRelation($loadVars, $object, $array = TRUE) {
        $refClass = new ReflectionClass($object);
        $properties = array();

        //si on demande de charger tous les tableaux d'une classe
        if (isset($loadVars[0]) and $loadVars[0] == self::ALL_FIELDS) {
            $allProperties = $refClass->getProperties(ReflectionProperty::IS_PRIVATE);
            foreach ($allProperties as $property) {
                $property->setAccessible(TRUE);
                //seulement les tableaux privé (convention)
                if ($array) {
                    if (is_array($property->getValue($object))) {
                        $properties[] = $property;
                    }
                } else {
                    if (is_object($property->getValue($object))) {
                        $properties[] = $property;
                    }
                }
                $property->setAccessible(FALSE);
            }
            //sinon ceux indiqués
        } else {
            foreach ($loadVars as $name) {
                $property = $refClass->getProperty($name);
                if ($property != NULL) {
                    $property->setAccessible(TRUE);
                    //comme si dessus et la proprieté doit exister
                    if ($array) {
                        if ($property->isPrivate() and is_array($property->getValue($object))) {
                            $properties[] = $property;
                        }
                    } else {
                        if ($property->isPrivate() and is_object($property->getValue($object))) {
                            $properties[] = $property;
                        }
                    }
                    $property->setAccessible(FALSE);
                }
            }
        }
        return $properties;
    }

    /**
     * 
     * @param string $fields le tableaux ou classes à charger
     * @param type $relation la relation
     */
    public function setLoadOptions($fields, $relation = '') {
        if (empty($relation)) {
            $this->setLoadOptions($fields, self::MANY_TO_MANY);
            $this->setLoadOptions($fields, self::MANY_TO_ONE);
        } elseif ($relation == self::MANY_TO_MANY) {
            if (is_array($fields)) {
                $this->loadArray = array_merge($this->loadArray, $fields);
            } else if ($fields == self::ALL_FIELDS) {
                $this->loadArray = array(self::ALL_FIELDS);
            } else {
                array_push($this->loadArray, $fields);
            }
        } elseif ($relation == self::MANY_TO_ONE) {
            if (is_array($fields)) {
                $this->loadClass = array_merge($this->loadClass, $fields);
            } elseif ($fields == self::ALL_FIELDS) {
                $this->loadClass = array(self::ALL_FIELDS);
            } else {
                array_push($this->loadClass, $fields);
            }
        }
    }

    /**
     * supprime l'entité de la bdd
     * 
     * @todo les langues
     * @param type $object
     * @param type $id
     * @author leo
     * @test seulement many to many
     */
    public function deleteObject($object, $id = 0) {
        if (!is_object($object) or $object == NULL) {
            return FALSE;
        }

        $this->query->setTableName($object);
        if (empty($id)) {
            if (empty($object->id)) {
                return FALSE;
            }
            $id = $object->id;
        }

        if ($this->query->isEmptyWhereClause()) {
            $this->query->where('id', $id);
        }

        $this->query->setLanguage($this->getTagLang($object));
        $resultat = $this->query->delete();
        $privateProperties = array_merge($this->_getRelation($this->loadArray, $object, TRUE), $this->_getRelation($this->loadArray, $object, FALSE));
        foreach ($privateProperties as $property) {
            $property->setAccessible(TRUE);
            $entityTableName = $this->query->getDbForge()->getTable($object);
            $propertyTableName = $property->getName() . '_' . $entityTableName;
            $propertyValue = $property->getValue($object);
            if (is_object($property->getValue($object)) and isset($property->getValue($object)->id)) {
                $this->query->setTableName($property->getName());
                $this->query->where($entityTableName . '_id', $id);
                $this->query->setLanguage($this->getTagLang($object));
                $resultat = $this->query->delete();
            } elseif (is_array($propertyValue) and !preg_match('`^_`', $property->getName())) {
                foreach ($propertyValue as $valueArrayProperty) {
                    if (is_object($valueArrayProperty) and isset($valueArrayProperty->id)) {
                        $idValue = $valueArrayProperty->id;
                    } elseif (is_array($valueArrayProperty) and isset($valueArrayProperty['id'])) {
                        $idValue = $valueArrayProperty['id'];
                    } else {
                        return FALSE;
                    }

                    $this->query->setTableName($propertyTableName);
                    $this->query->where('id_' . $property->getName(), $idValue);
                    $this->query->where('id_' . $entityTableName, $id);
                    $this->query->setLanguage($this->getTagLang($object));
                    $resultat = $this->query->delete();
                }
            }
            $property->setAccessible(FALSE);
        }

        return $resultat == TRUE;
    }

    /**
     * permet de charger dans l'objet les valeures corresponadantes.
     * Pour rechercher, ce base sur les <code>where</code> prédefinis. Si ce n'est 
     * pas le cas, l'id de l'objet ou l'id passé en paramètre si elle n'est pas égale
     * à 0
     * 
     * @author leo
     * @test testé, pas MANY_TO_ONE
     * @todo chargement des relations MANY_TO_ONE avec des objets et MANY_TO_MANY avec des objets
     * @todo cas de la bdd multilangues 
     * @param Object $object l'entité à utiliser
     * @param Object[optional] $id l'id de l'objet
     * @return boolean <code>TRUE</code> si la requête à été effectuée coorrectement
     *  sinon <code>FALSE</code>
     */
    public function loadObject($object, $id = 0) {
        if (!is_object($object) or $object == NULL) {
            return FALSE;
        }
        $this->query->setTableName($object);
        if (empty($id)) {
            if (!isset($object->id) and $this->query->isEmptyWhereClause()) {
                log_message('error', "l'entité doit avoir une id", TRUE);
                return FALSE;
            }
            $id = $object->id;
        }

        if ($this->query->isEmptyWhereClause()) {
            $this->query->where('id', $id);
        }
        $refClass = new ReflectionClass($object);
        $publicProperties = $refClass->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($publicProperties as $property) {
            $this->query->fields($property->getName());
        }

        $this->query->setLanguage($this->getTagLang($object));
        $select = $this->query->getSelect();
        $resLoadedObject = $this->query->getDbForge()->getDriver()->execute($select, $this->query->getWhereVars(), $object);
        $id = $object->id;

        $nameEntity = $this->query->getDbForge()->getTable($object);
        $privateProperties = $refClass->getProperties(ReflectionProperty::IS_PRIVATE);

        if (!$this->loadRelation) {
            $this->loadArray = array();
        }
        if (!empty($this->loadArray) or $this->loadRelation) {
            foreach ($privateProperties as $property) {
                //définition de qqe variables utiles
                $property->setAccessible(TRUE);
                $tableName = $property->getName();
                if (preg_match('`_mto$`', $tableName)) {
                    continue;
                }
                if (is_array($property->getValue($object)) and !preg_match('`^_`', $tableName)) {
                    $tableNameRelation = $tableName . '_' . $nameEntity;
                    $entityMtM = $this->_instanciateEntity($tableName);
                    $reflectionMtM = new ReflectionClass($entityMtM);

                    //nouvelle requête
                    $this->query->flushQuery();

                    //contruction de la requête
                    $this->query->setTableName($tableName);
                    foreach ($reflectionMtM->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                        $this->query->fields($prop->getName());
                    }

                    //première jointure
                    $leftEquality = $tableNameRelation . '.id_' . $tableName;
                    $rightEquality = $tableName . '.id';
                    $this->query->join($tableNameRelation, $leftEquality, $rightEquality);

                    //deuxième jointure
                    $leftEquality = $nameEntity . '.id';
                    $rightEquality = $tableNameRelation . '.id_' . $nameEntity;
                    $this->query->join($nameEntity, $leftEquality, $rightEquality);

                    //on recupère à partir de l'id de l'entité
                    $this->query->where('user.id', $id, 'AND', '=', TRUE);

                    //on execute la requête et on la met dans le tableau
                    $this->query->setLanguage($this->getTagLang($entityMtM));
                    $res = $this->query->select();
                    if (isset($res[0])) {
                        $property->setValue($object, $res);
                    } else {
                        $property->setValue($object, array($res));
                    }
                }

                if (is_object($property->getValue($object))) {

                    $property->setValue($object, $this->_instanciateEntity($tableName));

                    $this->query->flushQuery();
                    $this->loadObject($property->getValue($object), $this->_findIdMtO($tableName, $id, $nameEntity));
                    $this->query->flushQuery();
                }
                $resLoadedObject = TRUE;
            }
        }


        $this->query->flushQuery();
//        echo $select;
        //si true == true retourne true si false == true retourne false, je crois...
        return ($resLoadedObject == TRUE);
    }

    /**
     * insert un objet dans la bdd
     * 
     * @todo les langues
     * @param Object $object l'entité à utiliser
     * @param Object[optional] $id l'id de l'objet
     * @return boolean <code>TRUE</code> si la requête à été effectuée coorrectement
     *  sinon <code>FALSE</code>
     * @test pas fait
     */
    public function insertObject($object, $id = -1) {
        if (!is_object($object) or $object == NULL) {
            return FALSE;
        }

        $this->query->setTableName($object);
        if (empty($id)) {
            $id = $object->id;
        }
        $reflection = new ReflectionClass($object);
        $publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($publicProperties as $property) {
            $propertyValue = $property->getValue($object);
            if ($property->getName() == 'id') {
                $propertyValue = 'NULL';
            }
            if ($propertyValue == NULL) {
                $propertyValue = '';
            }
            $this->query->insertData($property->getName(), $propertyValue);
        }
        $this->query->setLanguage($this->getTagLang($object));
        $resultat = $this->query->insert();
        $id = $this->query->getLastInsertId();
        $object->id = $id;

        $entityTableName = $this->query->getDbForge()->getTable($object);
        $privateProperties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($privateProperties as $property) {
            $property->setAccessible(TRUE);
            $value = $property->getValue($object);
            if (!empty($value)) {
                $propTableName = $this->query->getDbForge()->getTable($property->getName());
                if (preg_match('`[a-z]+_as_[a-z]+`', $property->getName())) {
                    $propTableName = strstr($propTableName, '_as_', TRUE);
                }
                if (is_array($property->getValue($object)) and !preg_match('`^_`', $property->getName())) {
                    $values = $property->getValue($object);
                    $mtmTableName = $propTableName . '_' . $entityTableName;
                    $this->query->setTableName($mtmTableName);
                    $i=0;
                    foreach ($values as $value) {
                        $i++;
                        if (is_array($value) and isset($value['id'])) {
                            $idProp = $value['id'];
                        } else if (is_object($value) and isset($value->id)) {
                            $idProp = $value->id;
                        } else {
                            log_message('warning', "a value of the array of the property '$propTableName' is not an array and not an object");
                            continue;
                        }
                        entity($value, 'la valeure num '.$i);
                        debug($entityTableName, 'le nom de lentite: ');
                        debug($propTableName, 'le nom de la relation');
                        //pour chaque valeure du tableau, le premier param ajoute un champ dans la première partie
                        // de la requête insert et le deuxième para ajoute la valeure dans la deuxième partie de INSERT
                        // ne peut pas marché avec plus qu'un enregistrement. Pour l'instant, avec deux enregistrement $
                        // (ici, deux domaines pour un document), ça fait ça:
                        // INSERT INTO v2_domain_document (id_domain, id_document, id_domain, id_document) VALUES (:id_domain, :id_document, :id_domain, :id_document); 
                        // et on veut ça :
                        // INSERT INTO v2_domain_document (id_domain, id_document) VALUES (
                        // (:id_domain, :id_document),
                        // (:id_domain, :id_document)); 
                        // TODO modifier la méthode inserData et en rajouter une. Attention, aux modif pour pas devoir changer partout
                        // TODO adapter cette méthode pour un enregistrment correct
                        // TODO vérifier la méthode updateObject
                        // TODO vérifier que c'est bien deux domaines différents d'enregistré. la fonction entity, sort deux fois les 
                        // mêmes domains, il me semble. A vérifier

                        $this->query->insertData('id_' . $propTableName, $idProp);
                        $this->query->insertData('id_' . $entityTableName, $id);
                    }
                    $this->query->setLanguage($this->getTagLang($object));
                    $resultat = $this->query->insert();
                } else if (is_object($property->getValue($object)) and property_exists($property->getValue($object), 'id')) {
                    $this->query->setTableName($entityTableName)->where('id', $id)->updateData('id_' . $propTableName, $value->id);
                    $this->query->update();
                    $resultat = $this->saveObject($property->getValue($object));
                } else {
                    $nameProperty = $property->getName();
                    log_message('error', "the property $nameProperty is not an array and not an object");
                }
            }

            $property->setAccessible(FALSE);
        }

        return $resultat == TRUE;
    }

    /**
     * sauvegarde un objet dans la bdd
     * 
     * @todo les langues
     * @param Object $object l'entité à utiliser
     * @param Object[optional] $id l'id de l'objet
     * @return boolean <code>TRUE</code> si la requête à été effectuée coorrectement
     *  sinon <code>FALSE</code>
     * @test en partie
     */
    public function saveObject($object, $id = 0) {
        if (!is_object($object) or $object == NULL) {
            log_message('error', 'objet nul ou id indéfinie');
            return FALSE;
        }
        if (empty($id)) {
            $id = $object->id;
        }

        $this->query->flushQuery();
        $this->query->setTableName($object);
        $this->query->setLanguage($this->getTagLang($object));
        $this->query->fields('id');
        $this->query->where('id', $id);
        $count = $this->query->getDbForge()->getDriver()->count($this->query->getSelect(), $this->query->getWhereVars());
        $this->query->flushQuery();
        if ($count > 0) {
            return $this->updateObject($object, $id);
        } else {
            return $this->insertObject($object, $id);
        }
    }

    /**
     * met à jour un objet dans la bdd
     * 
     * @todo les langues
     * @param Object $object l'entité à utiliser
     * @param Object[optional] $id l'id de l'objet
     * @return boolean <code>TRUE</code> si la requête à été effectuée coorrectement
     *  sinon <code>FALSE</code>
     * @test qqn mais pas le MANY_TO_ONE
     */
    public function updateObject($object, $id = -1) {
        if (!is_object($object) or $object == NULL) {
            return FALSE;
        }

        $this->query->setTableName($object);
        if (empty($id)) {
            if (empty($object->id)) {
                return FALSE;
            }
            $id = $object->id;
        }
        $reflection = new ReflectionClass($object);
        $publicProperties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($publicProperties as $property) {
            $this->query->updateData($property->getName(), $property->getValue($object));
        }
        $this->query->where('id', $id);
        $this->query->setLanguage($this->getTagLang($object));
        $resultat = $this->query->update();

        $privateProperties = $reflection->getProperties(ReflectionProperty::IS_PRIVATE);
        $nameEntity = $this->query->getDbForge()->getTable($object);

        foreach ($privateProperties as $property) {
            $property->setAccessible(TRUE);

            $propertyTableName = $property->getName();
            if (preg_match('`[a-z]+_as_[a-z]+`', $property->getName())) {
                if (preg_match('`_mto$`', $propertyTableName)) {
                    continue;
                }
                $propertyTableName = strstr($propertyTableName, '_as_', TRUE);
            }
            if (preg_match('`_mto$`', $propertyTableName)) {
//                echo $propertyTableName;
//                debug(get_class($object));
                //TODO: update pany_to_one
            } elseif (is_array($property->getValue($object)) and !preg_match('`^_`', $propertyTableName)) {
                $tabElements = $property->getValue($object);
                foreach ($tabElements as $value) {
                    if (is_array($value)) {
                        $idProperty = $value['id'];
                    } elseif (is_object($value)) {
                        $idProperty = $value->id;
                    } else {
                        return FALSE;
                    }
                    $this->query->flushQuery();
                    $this->query->setTableName($propertyTableName . '_' . $nameEntity);
                    $this->query->where('id_' . $propertyTableName, $idProperty);
                    $this->query->where('id_' . $nameEntity, $id);
                    $count = $this->query->getDbForge()->getDriver()->count($this->query->getSelect(), $this->query->getWhereVars());
                    if ($count > 0) {
                        $this->query->setTableName($propertyTableName . '_' . $nameEntity);
                        $this->query->where('id_' . $propertyTableName, $idProperty);
                        $this->query->where('id_' . $nameEntity, $id);
                        $this->query->updateData('id_' . $propertyTableName, $idProperty);
                        $this->query->updateData('id_' . $nameEntity, $id);
                        $this->query->setLanguage($this->getTagLang($object));
                        $resultat = $this->query->update();
                    } else {
                        $this->query->insertData('id_' . $propertyTableName, $idProperty);
                        $this->query->insertData('id_' . $nameEntity, $id);
                        $this->query->setLanguage($this->getTagLang($object));
                        $resultat = $this->query->insert();
                    }
                }
            } elseif (is_object($property->getValue($object))) {
                if (property_exists($property->getValue($object), 'id')) {
                    $this->query->flushQuery();
                    $property->getValue($object)->id = $this->_findIdMtO($property->getName(), $id, $nameEntity);
                    $this->saveObject($property->getValue($object), $this->_findIdMtO($property->getName(), $id, $nameEntity));
                    $this->query->setTableName($object)->updateData('id_' . $property->getName(), $property->getValue($object)->id)->where('id', $id);
                    $this->query->update();
                }
            }
            $property->setAccessible(FALSE);
        }

        return $resultat == TRUE;
    }

    public function setQueryBuilder($query) {
        $this->query = $query;
    }

    /**
     * retourne le tag de la langue de l'utilisateur. '' si il n'y a pas de langue
     * l'entité doit avoir une variable language en protected ainsi que l'accesseur
     * et le mutateur de cet variable
     * 
     * @param Object $object l'entité
     * @return string le tag de langue (iso) ou ''
     * @author Numa de Montmollin
     * 
     * */
    public function getTagLang($object) {
        $refClass = new ReflectionClass($object);
        $hasLang = FALSE;
        foreach ($refClass->getProperties(ReflectionProperty::IS_PROTECTED) as $property) {
            if ($property->getName() == 'language') {
                $hasLang = TRUE;
                break;
            }
        }
        if (!$hasLang) {
            return '';
        }
        $lang = $object->getLanguage();
        if (!empty($lang)) {
            return $lang;
        }
        $CI = & get_instance();
        if (!isset($CI->lang)) {
            return '';
        }
        $lang = $CI->query->getLangId($CI->lang->getTagLang());
        $object->setLanguage($lang);
        return $lang;
    }

    public function loadRelation($object, $var) {
        $nameEntity = $this->query->getDbForge()->getTable($object);
        $property = new ReflectionProperty($object, $var);
        $property->setAccessible(TRUE);
        $tableName = $property->getName();
        $nameInstance = $tableName;
        $res = true;
        if (preg_match('`[a-z]+_as_[a-z]+`', $tableName)) {
            $tableName = str_replace('_as_', '_', $tableName);
            $nameInstance = str_replace('_as_', '', strstr($var, '_as_'));
            if (preg_match('`_mto$`', $tableName)) {
                $key = 'id_' . str_replace('_' . $nameEntity . '_mto', '', $tableName);
                $this->query->where($key, $object->id);

                $nameInstance = str_replace('_mto', '', $nameInstance);
                $entityMtO = $this->_instanciateEntity($nameInstance);
                //on execute la requête et on la met dans le tableau
                $res = $this->loadAllObject($entityMtO);

                $property->setValue($object, $res);
            } elseif (is_array($property->getValue($object))) {

                $tableNameRelation = strstr($var, '_as_', TRUE);
                $this->query->alias($nameInstance, 'v2_' . substr($nameInstance, 0, 1));
                $this->query->join($tableName, $tableName . '.id_' . $tableNameRelation, $this->query->getAS() . '.id');
                $this->query->join($nameEntity . ' AS ' . $this->query->getDbForge()->getDriver()->getPrefix() . 'entity', 'entity.id', $tableName . '.id_' . $nameEntity);
                $this->query->where('entity.id', $object->id, 'AND', '=', TRUE);
                $res = $this->loadAllObject($this->_instanciateEntity($nameInstance));
            }
        } else {
            if (preg_match('`_mto$`', $tableName)) {
                $key = 'id_' . $this->query->getDbForge()->getTable($object);
                $this->query->where($key, $object->id);
                $nameInstance = str_replace('_mto', '', $tableName);
                $entityMtO = $this->_instanciateEntity($nameInstance);
                //on execute la requête et on la met dans le tableau
                $res = $this->loadAllObject($entityMtO);

                $property->setValue($object, $res);
            } elseif (is_array($property->getValue($object))) {
                $tableNameRelation = $tableName . '_' . $nameEntity;
                if (preg_match('`^_`', $tableName)) {
                    $nameInstance = $tableName = substr($tableName, 1);
                    $tableNameRelation = $nameEntity . '_' . $tableName;
                }
                $entityMtM = $this->_instanciateEntity($nameInstance);

                //première jointure
                $leftEquality = $tableNameRelation . '.id_' . $tableName;
                $rightEquality = $tableName . '.id';
                $this->query->join($tableNameRelation, $leftEquality, $rightEquality);

                //deuxième jointure
                $leftEquality = $nameEntity . '.id';
                $rightEquality = $tableNameRelation . '.id_' . $nameEntity;
                $this->query->join($nameEntity, $leftEquality, $rightEquality);

                //on recupère à partir de l'id de l'entité
                $this->query->where($nameEntity . '.id', $object->id, 'AND', '=', TRUE);

                //on execute la requête et on la met dans le tableau
                $res = $this->loadAllObject($entityMtM);

                $property->setValue($object, $res);
            }

            if (is_object($property->getValue($object))) {
                $property->setValue($object, $this->_instanciateEntity($nameInstance));

                $idProperty = $this->_findIdMtO($tableName, $object->id, $nameEntity);
                $this->setLoadRelation(FALSE);
                $this->loadObject($property->getValue($object), $idProperty);
                $this->query->flushQuery();
                $res = $property->getValue($object);
            }
        }
        $property->setAccessible(FALSE);
        return $res;
    }

    public function loadAllObject($object) {
        $this->query->setTableName($object);
        $this->query->setLanguage($this->getTagLang($object));
        $reflectioClass = new ReflectionClass($object);
        $publicProperties = $reflectioClass->getProperties(ReflectionProperty::IS_PUBLIC);
        if (!$this->query->isFields()) {
            foreach ($publicProperties as $property) {
                $this->query->fields($property->getName());
            }
        }
        $res = $this->query->getDbForge()->getDriver()->fetchClass($this->query->getSelect(), $this->query->getAllValues(), $object);
        $this->query->flushQuery();
        return $res;
    }

    public function flush() {
        $this->loadArray = array();
        $this->loadClass = array();
    }

    public function getLoadRelation() {
        return $this->loadRelation;
    }

    public function setLoadRelation($loadRelation) {
        $this->loadRelation = $loadRelation;
    }

    public function deleteRealtion($object, $var) {
        $property = new ReflectionProperty($object, $var);
        $property->setAccessible(TRUE);
        $entityTableName = $this->query->getDbForge()->getTable($object);
        $propertyTableName = $property->getName() . '_' . $entityTableName;
        $propertyValue = $property->getValue($object);
        if (is_object($property->getValue($object)) and isset($property->getValue($object)->id)) {
            $this->query->setTableName($property->getName());
            $this->query->where($entityTableName . '_id', $object->id);
            $this->query->setLanguage($this->getTagLang($object));
            $this->query->delete();
        } elseif (is_array($propertyValue)) {
            foreach ($propertyValue as $valueArrayProperty) {
                if (is_object($valueArrayProperty) and isset($valueArrayProperty->id)) {
                    $idValue = $valueArrayProperty->id;
                } elseif (is_array($valueArrayProperty) and isset($valueArrayProperty['id'])) {
                    $idValue = $valueArrayProperty['id'];
                } else {
                    return FALSE;
                }

                $this->query->setTableName($propertyTableName);
                $this->query->where('id_' . $property->getName(), $idValue);
                $this->query->where('id_' . $entityTableName, $object->id);
                $this->query->setLanguage($this->getTagLang($object));
                $this->query->delete();
            }
        }
        $property->setAccessible(FALSE);
    }

    public function setFieldsToQuery($object, $visibility = ReflectionProperty::IS_PUBLIC) {
        $refClass = new ReflectionClass($object);
        $prop = $refClass->getProperties($visibility);
        foreach ($prop as $p) {
            $this->query->fields($p->getName());
        }
    }
    
    /**
     * Ajoute une jointure multiple en fonction de la relation.
     * Types de raltions:
     * -mtm
     * -mto
     * -otm
     * -self referrencing
     * -relation inverse mtm
     * 
     * @param Entity $entity l'entité qui a la relation
     * @param Entity $relation l'entité qui fait la realtion
     */
    public function addMultipleRelation($entity, $relation) {
        //TODO implémenté toute les relations décrites plus hauts, pour les jointures multiples
        //s'inspirer de loadObject->créer des méthodes communes plus performantes que maintenant ?
        
        //TODO, une foir le résultat obtenu, le chrger dans les entités, ou c'est une autre classe qui le fait ?
    }

}

