<?php

/**
 * Crée une requête et gère SQL
 *
 * @test fait en majeure parite. encore tester tous les cas possible des méthodes fields et join
 * @see QueryBuilder
 * @author leo
 */
class Pdo_query_builder extends QueryBuilder {

    /**
     * indique quels champs de la tables on veut afficher
     * 
     * @return $this
     * @param string $fieldsData le nom des champs de la table
     */
    public function fields($fieldsData, $otherTableName = FALSE) {
        if (is_array($fieldsData)) {
            if (!$otherTableName) {
                $this->select['main_table'] = array_merge($this->select['main_table'], $fieldsData);
            } else {
                $this->select['other_tables'] = array_merge($this->select['other_tables'], $fieldsData);
            }
        } else {
            if (!$otherTableName) {
                array_push($this->select['main_table'], $fieldsData);
            } else {
                array_push($this->select['other_tables'], $fieldsData);
            }
        }
        return $this;
    }

    /**
     * enregistre les données pour la création de la partie where de la requête
     * 
     * @param String $name le nom du champ de la table
     * @param var $var La valeure du champ
     * @param String[optional] $op L'opéreande i.e: OR ou AND AND par défaut
     * @param String[optional] $rel La relation entre le champ de la table et sa valeure i.e: =, <=, etc
     * @param boolean[optional] $noTable si on veut utiliser un champ d'une autre table
     * @return $this
     */
    public function where($name, $var, $op = 'AND', $rel = '=', $noTable = FALSE) {
        if ($noTable) {
            $tempName = str_replace('.', '_', $name);
        } else {
            $tempName = $name;
        }
        $varName = $name;
        if (isset($this->whereVars[$tempName])) {
            $this->sameWhereKey++;
            $tempName = $varName = $tempName . $this->sameWhereKey;
        }
        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $name)) {
//            $name = $this->dbForge->getDriver()->getPrefix().$name;
//            $varName = $this->dbForge->getDriver()->getPrefix().$varName;
//            $tempName = $this->dbForge->getDriver()->getPrefix().$tempName;
        }
        $this->whereVars[$tempName] = $var;
        array_push($this->where, array('name' => $name, 'var_name' => $varName, 'var' => $var, 'op' => $op, 'rel' => $rel, 'no_table' => $noTable));
//        print_r($this->where);
        return $this;
    }

    /**
     * crée la partie JOIN de la requête.
     * Quand le paramètre <code>$leftEquality</code> est un tableau. Utilise une requêtze préparé avec
     * la valeure de ce paramètre 
     * 
     * @param string $tableName le nom de la table dans la partie ... JOIN nom_de_la_table ON ...
     * @param string $leftEquality la partie gauche de la compraison
     * @param string ou mixed $rightEquality la partie droite de la comparaiso 
     * @param string $join le type de jointure (INNER, LEFTm ...)
     * @param string $rel la relation (= >= etc)
     * @return QueryBuilder $this
     */
    public function join($tableName, $leftEquality, $rightEquality, $join = 'LEFT', $rel = '=') {
        if (is_array($rightEquality)) {
            $this->rightEqualityValues[$rightEquality] = $rightEquality;
            $rightEquality = ':' . $rightEquality;
        }
        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $tableName)) {
            $tableName = $this->dbForge->getDriver()->getPrefix() . $tableName;
        }
        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $leftEquality)) {
            $leftEquality = $this->dbForge->getDriver()->getPrefix() . $leftEquality;
        }

        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $rightEquality) and is_string($rightEquality)) {
            $rightEquality = $this->dbForge->getDriver()->getPrefix() . $rightEquality;
        }
        $this->joinON.="$join JOIN $tableName ON $leftEquality $rel $rightEquality ";
        return $this;
    }

    /**
     * Pour créer une requête de type INSERT
     * quand $field et $var sont des tableaux c'est équivalent à appeler plusieurs fois la requête
     * @param string ou mixed $field le ou les champs dans les quels on veut insérer une
     * nouvelle valeure
     * @param string ou mixed $var la valeure du champ inséré
     */
    public function insertData($field, $var) {
        if (is_array($field) and is_array($var)) {
            foreach ($field as $key => $f) {
                $this->insertFields.=$f . ', ';
                $this->insertVars.= ":$f, ";
                $this->insertValue[$field] = $var[$key];
            }
        } else {
            $this->insertFields.=$field . ', ';
            $this->insertVars.=":$field, ";
            $this->insertValue[$field] = $var;
        }
        return $this;
    }

    /**
     * Crée la partie UPDATE de la requête. Si des tableaux sont passés en paramètre,
     * équivalent à plusieurs appels de cet méthode
     * @todo le nom de la table devant chaque champs ?
     * @param string ou mixed $field le nom du champ à mettre à jour
     * @param string ou array $var la valeure du champ à mettre à jour
     */
    public function updateData($field, $var) {
        if (is_array($field) and is_array($var)) {
            foreach ($field as $key => $f) {
                $this->update.="$f=:$f, ";
                $this->updateValue[$f] = $var[$key];
            }
        }
        $this->update.="$field=:$field, ";
        $this->updateValue[$field] = $var;
        return $this;
    }

    /**
     * exécute une requête de type DELETE
     * 
     * @param Object[optional] $entity l'entité à supprimer dans la table
     * @param int[optional] $id l'id de l'entité
     * @return mixed le résultat de la requête
     */
    public function delete($entity = NULL, $id = -1) {
        $this->_useEntity($entity, $id);
        if (is_string($entity)) {
            $this->setTableName($entity);
        }
        $this->getDelete();
        $deleteRes = $this->dbForge->getDriver()->execute($this->query, $this->whereVars);
        $this->flushQuery();
        return $deleteRes;
    }

    /**
     * exécute une requête de type INSERT
     * 
     * @param Object[optional] $entity l'entité à insérer dans la table
     * @return mixed le résultat de la requête
     */
    public function insert($entity = NULL) {
        if ($entity != NULL and is_object($entity)) {
            $this->setTableName($this->dbForge->getTable($entity));
        }
        if (is_string($entity)) {
            $this->setTableName($entity);
        }
        if (!empty($this->language) and $this->dbForge->isTable($this->dbForge->getTableLang($this->tableName))) {
            $tableLang = $this->dbForge->getTableLang($this->tableName);
            $fieldsTableLang = $this->dbForge->getFields($tableLang);
            $fields = '';
            $insertFieldsLang = array();
            $vars = '';
            $tempFields = explode(', ', $this->insertFields);
            $tempVars = explode(', ', $this->insertVars);
            foreach ($this->insertValue as $nameField => $valueField) {
                if (in_array($nameField, $fieldsTableLang)) {
                    $fields.="$nameField, ";
                    $vars.=":$nameField, ";
                    $insertFieldsLang[$nameField] = $valueField;
                    unset($this->insertValue[$nameField]);
                    unset($tempFields[array_keys($tempFields, $nameField)[0]]);
                    unset($tempVars[array_keys($tempVars, ":$nameField")[0]]);
                }
            }
            $this->insertVars = implode(', ', $tempVars);
            $this->insertFields = implode(', ', $tempFields);
        }

        $this->getInsert();
        $insertRes = $this->dbForge->getDriver()->execute($this->query, $this->insertValue);
        $this->lastInsertID = $this->dbForge->getDriver()->getBdd()->lastInsertId();

        if (isset($fields)) {
            $fields .= 'language, reference, ';
            $insertFieldsLang['reference'] = $this->dbForge->getDriver()->getBdd()->lastInsertId();
            $query = '';
            foreach ($this->dbForge->getAllLanguage() as $language) {
                $insertFieldsLang['language_' . $language['id']] = $language['id'];
                $query .= "INSERT INTO $tableLang (" . substr($fields, 0, -2) . ") VALUES (" . substr($vars . ':language_' . $language['id'] . ', :reference, ', 0, -2) . ");";
            }
            $this->dbForge->getDriver()->execute($query, $insertFieldsLang);
        }
        $this->flushQuery();
        return $insertRes;
    }

    /**
     * exécute une requête de type SELECT
     * 
     * @param Object[optional] $entity l'entité à rechercher dans la table
     * @param int[optional] $id l'id de l'entité
     * @return mixed le résultat de la requête
     */
    public function select($entity = NULL, $id = -1) {
        $this->_useEntity($entity, $id);
        if (is_string($entity)) {
            $this->setTableName($entity);
            $entity = NULL;
        }
        $this->getSelect();
        $selectRes = $this->dbForge->getDriver()->execute($this->query, $this->whereVars, $entity, $this->fetchAll);
        if (!$selectRes and !empty($this->language)) {
            log_message('debug', 'tradution \'' . $this->language . '\' inexistante');
            if (!$this->noLanguage) {
                $this->allLanguage = $this->dbForge->getAllLanguage();
                $this->noLanguage = TRUE;
            }
            $this->removeSelectLang();
            if (empty($this->allLanguage) or $this->noOtherLanguage) {
                $this->language = 0;
                $this->select['other_tables'] = array();
            } else {
                $this->language = $this->allLanguage[count($this->allLanguage) - 1]['id'];
                unset($this->allLanguage[count($this->allLanguage) - 1]);
            }
            $selectRes = $this->select();
        }
        $this->flushQuery();
        return $selectRes;
    }

    /**
     * exécute une requête de type UPDATE
     * 
     * @param Object[optional] $entity l'entité à mettre à jour dans la table
     * @param int[optional] $id l'id de l'entité
     * @return mixed le résultat de la requête
     */
    public function update($entity = NULL, $id = -1) {
        $this->_useEntity($entity, $id);
        if (is_string($entity)) {
            $this->setTableName($entity);
        }
        $this->getUpdate();
        $updateRes = $this->dbForge->getDriver()->execute($this->query, array_merge($this->whereVars, $this->updateValue));
        $this->flushQuery();
        return $updateRes;
    }

    /**
     * retourne une requête SQL de type DELETE
     * 
     * @return string une requête SQL
     */
    public function getDelete() {
        $this->query = '';
        if (!empty($this->language)) {
            if ($this->dbForge->isTable($this->dbForge->getTableLang($this->tableName))) {
                if (empty($this->whereVars)) {
                    $this->query = "DELETE FROM " . $this->dbForge->getTableLang($this->tableName) . ";";
                } else {
                    $this->query = "DELETE FROM " . $this->dbForge->getTableLang($this->tableName) . " WHERE reference = :reference;";
                    $this->whereVars['reference'] = $this->findReferenceLang();
                }
            }
        }
        if (empty($this->whereVars)) {
            $this->query .= "DELETE FROM " . $this->getAlias() . ";";
        } else {
            $this->query .= "DELETE FROM " . $this->getAlias() . " WHERE " . $this->getWhere() . ';';
        }
        return $this->query;
    }

    /**
     * retourne une requête SQL de type INSERT
     * 
     * @return string une requête SQL
     */
    public function getInsert() {
        $this->query = "INSERT INTO " . $this->getAlias() . " (" . substr($this->insertFields, 0, -2) . ") VALUES (" . substr($this->insertVars, 0, -2) . ');';
        return $this->query;
    }

    /**
     * retourne une requête SQL de type SELECT
     * 
     * @return string une requête SQL
     */
    public function getSelect() {
        $this->translateSelect();
        if (empty($this->whereVars)) {
            $this->query = "SELECT " . $this->getFields()  . $this->makeMulitpleJoin() . " FROM " . $this->getAlias() . " " . $this->joinON . ' ' . $this->order . ' ' . $this->limit;
        } else {
            $this->query = "SELECT " . $this->getFields()  . $this->makeMulitpleJoin() . " FROM " . $this->getAlias() . " " . $this->joinON . " WHERE " . $this->getWhere() . ' ' . $this->order . ' ' . $this->limit;
        }
        return $this->query;
    }

    /**
     * retourne une requête SQL de type UPDATE
     * 
     * @return string une requête SQL
     */
    public function getUpdate() {
        $this->query = '';
        if (!empty($this->language)) {
            $tableLang = $this->dbForge->getTableLang($this->tableName);

            $fieldsTableLang = $this->dbForge->getFields($tableLang);
            $values = array();
            $set = '';
            $tempFields = explode(', ', $this->update);
            foreach ($this->updateValue as $fieldName => $fieldValue) {
                if (in_array($fieldName, $fieldsTableLang)) {
                    if ($fieldName == 'id') {
                        continue;
                    }
                    $values[$tableLang . '_' . $fieldName] = $fieldValue;
                    $set.="$tableLang.$fieldName=:$tableLang" . "_" . "$fieldName, ";
                    unset($this->updateValue[$fieldName]);
                    unset($tempFields[array_keys($tempFields, "$fieldName=:$fieldName")[0]]);
                }
            }
            $this->update = implode(', ', $tempFields);
            $join = "LEFT JOIN " . $this->getAlias() . " ON " . $this->getAS() . ".id = $tableLang.reference LEFT JOIN " . $this->dbForge->getNameTableLanguage() . " ON $tableLang.language = " . $this->dbForge->getNameTableLanguage() . '.id';
            $values[$this->dbForge->getNameTableLanguage() . '_id'] = $this->language;
            $this->query = "UPDATE $tableLang $join SET " . substr($set, 0, -2);
            if (!empty($this->whereVars)) {
                $this->query.=" WHERE " . $this->getWhere() . 'AND ' . $this->dbForge->getNameTableLanguage() . '.id' . ' = :' . $this->dbForge->getNameTableLanguage() . '_id' . ';';
            }
            $this->updateValue = array_merge($this->updateValue, $values);
        }
        if (empty($this->whereVars)) {
            $this->query .= "UPDATE " . $this->getAlias() . " $this->joinON SET " . substr($this->update, 0, -2) . ';';
        } else {
            $this->query .= "UPDATE " . $this->getAlias() . " $this->joinON SET " . substr($this->update, 0, -2) . " WHERE " . $this->getWhere() . ';';
        }
        return $this->query;
    }

    /**
     * contrôle si l'entité peut vraiment être utilisé
     * 
     * @param Object $entity une entité
     * @param int $id l'id de l'entité
     */
    private function _useEntity($entity, $id) {
        if ($entity != NULL and is_object($entity)) {
            $this->setTableName($this->dbForge->getTable($entity));
            if ($id == -1) {
                if (!isset($entity->id)) {
                    log_message('error', 'Une entité à besoin d\'un id', TRUE);
                }
                $id = $entity->id;
            }
            $this->where('id', $id);
        }
    }

    /**
     * renvoie la partie WHERE de la requête
     * 
     * @return string la clause where d'une requête
     */
    public function getWhere() {
        $whereClause = "";
        foreach ($this->where as $where) {
            if ($where['no_table'] == TRUE) {
                if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $where['name'])) {
                    $where['name'] = $this->dbForge->getDriver()->getPrefix() . $where['name'];
                }
                $whereTableName = $where['name'];
                $relRight = str_replace('.', '_', $where['var_name']);
            } else {
                $whereTableName = $this->getAS() . "." . $where['name'];
                $relRight = $where['var_name'];
            }
            $whereClause.=$whereTableName . " " . $where['rel'] . " :" . $relRight . " " . $where['op'] . " ";
        };
        return substr($whereClause, 0, -4);
    }

    /**
     * permet de construire la clause de séléction.
     * i.e: user.username, user.id ->la requête va retourner le username et l'id 
     * de la table user
     * 
     * @author leo
     * @test fait
     * @return string le ou les fields
     */
    public function getFields() {
        $fields = '';
        foreach ($this->select['main_table'] as $field) {
            if ($field == '*') {
                return '*';
                break;
            } else {
                $fields .= $this->getAS() . ".$field, ";
            }
        }
        foreach ($this->select['other_tables'] as $field) {
            if ($field == '*') {
                return '*';
                break;
            } else {
                $fields .= "$field, ";
            }
        }
        if (empty($fields)) {
            return '*';
        }

        return substr($fields, 0, -2);
    }

    /**
     * Permet de récupérer l'id de la langue grâce à l'iso de celle-ci
     * 
     * @param $iso iso de la langue
     *
     * @return int
     */
    public function getLangId($iso) {
        $sql = 'SELECT id FROM ' . $this->dbForge->getNameTableLanguage() . ' WHERE lang = :iso';
        $var = array('iso' => $iso);
        $req = $this->dbForge->getDriver()->execute($sql, $var);
        return $req['id'];
    }

    public function findReferenceLang() {
        $query = "SELECT $this->tableName.id FROM $this->tableName WHERE " . $this->getWhere();
        return $this->dbForge->getDriver()->execute($query, $this->getWhereVars())['id'];
    }

    public function getIdiom($id) {
        $sql = 'SELECT * FROM ' . $this->dbForge->getNameTableLanguage() . ' WHERE id = :id';
        $var = array('id' => $id);
        $req = $this->dbForge->getDriver()->execute($sql, $var);
        return $req['language'];
    }

    public function count($table = '') {
        if (!empty($table)) {
            $this->setTableName($table);
        }
        $res = $this->dbForge->getDriver()->count($this->getSelect(), $this->getAllValues());
        $this->flushQuery();
        return $res;
    }

    public function removeSelectLang() {

        $tempJoin = explode(' JOIN', $this->joinON);
        unset($tempJoin[count($tempJoin) - 1]);
        unset($tempJoin[count($tempJoin) - 1]);
        if (($tempJoin[0] == 'LEFT' or $tempJoin[0] == 'RIGHT' or $tempJoin[0] == 'INNER') and count($tempJoin) == 1) {
            $this->joinON = '';
        } else {
            $tempJoin[count($tempJoin) - 1] = preg_replace('`LEFT`i', '', $tempJoin[count($tempJoin) - 1]);
            $this->joinON = implode(' JOIN ', $tempJoin);
        }
        array_pop($this->where);
        unset($this->whereVars[$this->dbForge->getNameTableLanguage() . '_id']);
    }

    public function limit($limit1 = 0, $limit2 = 0) {
        if (empty($limit2)) {
            $limit2 = DATA_PER_PAGE;
        }
        $this->limit = "LIMIT $limit1, $limit2";
        return $this;
    }

    public function order($field, $asc = TRUE) {
        $by = 'ASC';
        if (!$asc) {
            $by = "DESC";
        }
        $this->order = "ORDER BY $field $by";
    }

    public function translateSelect() {
        if (!empty($this->language)) {
            $tableLang = $this->dbForge->getTableLang($this->tableName);
            $fieldsTableLang = $this->dbForge->getFields($tableLang);

            if ($this->getFields() == '*') {
                $this->select = array('main_table' => array(), 'other_tables' => array());

                $this->fields($this->dbForge->getFieldsTableLang($this->tableName), TRUE);
                $this->fields($this->dbForge->getFields($this->tableName));
            }

//@TODO: réfléchir qu'est-ce qui prend le moins de temps ?
            foreach ($this->select['main_table'] as $field) {
                if ($field == 'id') {
                    continue;
                }
                if (in_array($field, $fieldsTableLang)) {
                    $this->fields("$tableLang.$field", TRUE);
                    $key = array_search($field, $this->select['main_table']);
                    unset($this->select['main_table'][$key]);
                }
            }

            $this->join($tableLang, $this->getAS() . '.id', "$tableLang.reference");
            $this->join($this->dbForge->getNameTableLanguage(), "$tableLang.language", $this->dbForge->getNameTableLanguage() . '.id');
            $this->where($this->dbForge->getNameTableLanguage() . '.id', $this->language, 'AND', '=', TRUE);
        }
    }

    public function alias($tableName, $alias = '') {
        if (empty($alias)) {
            $alias = $tableName;
        }
        $this->tableName = $tableName;
        $this->alias = $alias;
    }

    public function getAlias() {
        if (!empty($this->alias)) {
            return $this->tableName . ' AS ' . $this->alias;
        }
        return $this->tableName;
    }

    /*
     * Je te laisse regarder et dis moi si t'as des questions
     */

    public function addMultipleJoin($tableName, $fields) {
        // LA methode qu'on veut
        // 
//        SELECT v2_document.title, 
//        GROUP_CONCAT(DISTINCT CONCAT(v2_domain.id, ' ',v2_domain_lang.name)) AS domain_name, 
//        GROUP_CONCAT(DISTINCT CAST(v2_file.name AS CHAR)) AS file_name
//FROM v2_document
//LEFT JOIN v2_domain_document ON v2_document.id = v2_domain_document.id_document
//LEFT JOIN v2_domain ON v2_domain_document.id_domain = v2_domain.id
//LEFT JOIN v2_domain_lang ON v2_domain.id = v2_domain_lang.reference
//LEFT JOIN v2_language ON v2_domain_lang.LANGUAGE = v2_language.id
//LEFT JOIN v2_file_document ON v2_document.id = v2_file_document.id_document
//LEFT JOIN v2_file ON v2_file_document.id_file = v2_file.id
//WHERE v2_language.id = 1
//
        //$table1 = Le nom de la table à joindre (par exemple "domain")
        //$field1 et field2 sont les champs de la table $table1
        // $table2 est le nom de la deuxième table à joindre
        //$field2_1 et filde2_2 sont les champs de la deuxième table
        //Prendre un séparateur qui soit sur (pas qu'il se retrouve dans une description ou un nom et 
        // Qu'il face conflit.
        // TODO : Trouver une solution pour être sur qu'il n'y ai pas du tout de conflit (que ces caract ne se trouvent jamais dans le text)
//        $separator = '-,.,-';
//        // Sert de séparateur pour les groupes
//        $separatorGROUPE = '-AAAAA-';
//        $query = 'SELECT  truc1, truc2, etc, etc,
//            
//           GROUP_CONCAT(DISTINCT CONCAT_WS(' . $separator . ', CAST(' . $table1 . '.' . $field1 . ' AS CHAR), CAST(' . $table1 . '.' . $field2 . ' AS CHAR)) AS ' . $table1 .
//                'ORDER_BY ' . $table1 . '.name ASC SEPARATOR \'' . $separatorGROUPE . '\','; // Voilà c'est une liaison ici, si on en veut une deuxième, on refait la même chose comme ci dessous:
//
//        $query .=
//                'GROUP_CONCAT(DISTINCT CONCAT_WS(' . $separator . ', CAST(' . $table2 . '.' . $field2_1 . ' AS CHAR), CAST(' . $table2 . '.' . $field2_2 . ' AS CHAR)) AS ' . $table2 . ' ORDER_BY ce_que_tu_veux DESC SEPARATOR \'' . $separatorGROUPE . '\''; // Order by est bien sur pas obligé, mais c'est cool 
//        // Pour les jointure tu devrais t'en sortir...
//        //LEFT JOIN v2_domain_document ON v2_document.id = v2_domain_document.id_document
//        //LEFT JOIN v2_domain ON v2_domain_document.id_domain = v2_domain.id
//        // Et si il y a une langue :
//        //LEFT JOIN v2_domain_lang ON v2_domain.id = v2_domain_lang.reference
//        // ENSUITE POUR RECUPERER LES DONNEES:
//        // TU as deux separateur bien distint, les infos seront donc comme ça :
//        // POur la table 1 :
//        $donnees = array('truc1' => 'truc1', 'truc2' => 'truc2', 'etc' => 'etc', 'etc' => 'etc', $table1 => 'field1-,.,-field2-AAAAA-field1-,.,-field2-AAAAA-field1-,.,-field2');
//        // Donc tu fait :
//        $donneesDeLaJointure = explode($donnees[$table1], $separatorGROUPE); // retourn tout les bloque de domain lié
//        // On a tout les groupes de domain, on veut leur info maintenant
//        foreach ($donneesDeLaJointure AS $domainBlock)
//            $infosDuDomain = explode($domainBlock, $separator); // Retourn toutes les infos sur le domain séléctionnée
//        $this->setTableNameMultipleJoin($tableName);
    }

    /**
     * crée la partie de la requête pour les jointures multiples
     */
    public function makeMulitpleJoin() {
        //si il n'ya pas de jointure en evoir u string vide
        $jointures = $this->getArrayMultipleJoin();
        if (empty($jointures)) {
            return "";
        }
        $group = "";
        $fields = "";
        //pour chaque jointure
        foreach ($jointures as $tableName => $tableMJoin) {
            //le début de la requête pour groupe
            $group = ", GROUP_CONCAT(DISTINCT CONCAT_WS(\"" . $this->getSeparatorMultipleJoin() . "\", ";
            $fields = "";
            //pour chaque champs de la table
            foreach ($tableMJoin['fields'] as $field) {
                $fields .= "CAST($tableName.$field AS CHAR), ";
            }
            //on crée les jointures, ne pas les faires là, mais dans dmo
//            $this->join($tableName, $this->getTableName() . ".id", "$tableMJoin.id");
            //on ajoute le résultats pour chaque champs de la table au group_concat
            $group .= substr($fields, 0, -2) . ")ORDER BY $tableName." . $tableMJoin['orderBy'] . " " . $tableMJoin['asc'] .
                    " SEPARATOR '" . $this->getSeparatorGroupMultipleJoin() . "') AS $tableName";
        }
        $this->setStringMultipleJoin($group);
        return $group;
    }

}

?>
