<?php

/**
 * Permet de créer une requête SQL en utilisant certaines méthodes
 * classe abstraite
 *
 * @property DbForge $dbForge
 * @author leo
 */
abstract class QueryBuilder {

    /**
     *
     * @var String contient la parite select de la requête
     */
    protected $select = array('main_table' => array(), 'other_tables' => array());

    /**
     *
     * @var array contient la partie where de la requête. Sous forme PDO 
     */
    protected $where = array();

    /**
     *
     * @var String toute la requête. Prête pour PDO 
     */
    protected $query = '';

    /**
     *
     * @var mixed les variables de la partie where sous forme de tableau pour PDO 
     */
    protected $whereVars = array();

    /**
     *
     * @var String le nom de la table
     */
    protected $tableName = '';

    /**
     *
     * @var string le nom des champs de la table que l'on veut insérer 
     */
    protected $insertVars = '';

    /**
     *
     * @var array les valeures à utiliser pour une requête INSERT 
     */
    protected $insertValue = array();

    /**
     *
     * @var string le nom des champs pour une requête INSERT 
     */
    protected $insertFields = '';

    /**
     *
     * @var string le nom des champs pour une requête UPDATE 
     */
    protected $update = '';

    /**
     *
     * @var array les valeures à utiliser pour une requête UPDATE 
     */
    protected $updateValue = array();

    /**
     *
     * @var DbForge la classe qui gère les rapports avec les tables 
     */
    protected $dbForge = NULL;

    /**
     *
     * @var Tree la classe qui gère les arbres à données intervallaires
     */
    protected $tree = NULL;

    /**
     *
     * @var boolean pour savoir si il faut mémoriser le nom de la table entre deux requêtes
     */
    protected $keepTableName = FALSE;

    /**
     *
     * @var string contient la partie de la requête avec des jointures 
     */
    protected $joinON = '';

    /**
     *
     * @var arrray pour si on veut utiliser des valeures peut-être corrompues dans un JOIN 
     */
    protected $rightEqualityValues = array();

    /**
     *
     * @var string définit quel champs de la table en fonction de la langue aller chercher.
     */
    protected $language = '';
    protected $allLanguage = array();
    protected $noLanguage = FALSE;
    protected $noOtherLanguage = TRUE;
    protected $fetchAll = FALSE;
    protected $limit = '0';
    protected $sameWhereKey = 0;
    protected $lastInsertID = 0;
    protected $prefix = '----';
    protected $order = '';
    protected $alias = '';
    protected $tableNameAlias = '';
    private $arrayMultipleJoin = array();
    private $stringMultipleJoin = '';
    private $separatorMultipleJoin = "-qwert-";
    private $separatorGroupMultipleJoin = "-zuiop-";

    /**
     * initialise les variables avec les bonnes valeures
     */
    public function __construct() {
        $this->flushQuery();
        $this->dbForge = NULL;
        $this->keepTableName = FALSE;
        $this->tree = NULL;
    }

    /**
     * pour enregistrer les données de la partie WHERE de la requête
     */
    public abstract function where($name, $var, $op = '', $rel = '=', $noTable = FALSE);

    /**
     * pour retourner la partie WHERE de la requête
     */
    public abstract function getWhere();

    /**
     * retourner la partie des champs séléctioné de la requête ie: ...user.id, user.username...
     */
    public abstract function getFields();

    /**
     * @return $this
     */
    public abstract function fields($fieldsData);

    public abstract function insertData($field, $var);

    public abstract function updateData($field, $var);

    public abstract function select($entity = NULL, $id = -1);

    public abstract function insert($entity = NULL);

    public abstract function delete($entity = NULL, $id = -1);

    public abstract function update($entity = NULL, $id = -1);

    public abstract function getSelect();

    public abstract function getInsert();

    public abstract function getUpdate();

    public abstract function getDelete();

    public abstract function join($tableName, $leftEquality, $rightEquality, $join = 'LEFT', $rel = '=');

    public abstract function getLangId($iso);

    public abstract function findReferenceLang();

    public abstract function limit($limit1, $limit2 = 0);

    public abstract function count($table);

    public abstract function translateSelect();

    public abstract function order($field, $desc = FALSE);

    public abstract function alias($tableName, $alias = '');

    public abstract function makeMulitpleJoin();

    /**
     * 
     * @return array les valeures de la partie where et de la partie join 
     */
    public function getWhereVars() {
        return array_merge($this->whereVars, $this->rightEqualityValues);
    }

    /**
     * 
     * @return string le nom de la table
     */
    public function getTableName() {
        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $this->tableName)) {
            return $this->dbForge->getDriver()->getPrefix() . $this->tableName;
        }
        return $this->tableName;
    }

    /**
     * 
     * défini le nom de la table pour construire la requête
     * si le nom est un objet, obtien son nom avec la méthode <code>getTable()</code>
     * 
     * @param string ou object $tableName le nom de la table ou l'entité qui s'y refère
     * @param boolean $keepIt$ pour mémoriser le nom entre deux requêtes
     */
    public function setTableName($tableName, $keepIt = FALSE) {
        if (is_object($tableName)) {
            $this->tableName = $this->getDbForge()->getTable($tableName);
        } else {
            $this->tableName = $tableName;
        }
        $this->keepTableName = $keepIt;
        if (!preg_match('`^' . $this->dbForge->getDriver()->getPrefix() . '`', $this->tableName)) {
            $this->tableName = $this->dbForge->getDriver()->getPrefix() . $this->tableName;
        }
        return $this;
    }

    /**
     * retourne toutes les valeures possibles d'une requête.
     * Même celle qui sont inutilisées
     * @return array toutes les valeures d'une requête
     */
    public function getAllValues() {
        $allValues = array();
        $allValues = array_merge($this->whereVars, $allValues);
        $allValues = array_merge($this->insertValue, $allValues);
        $allValues = array_merge($this->updateValue, $allValues);
        return $allValues;
    }

    /*
     * accesseur
     * @return la requête
     */

    public function getQuery() {
        return $this->query;
    }

    /**
     * mutateur enrgistre le requête
     * @param string $query le requête
     */
    public function setQuery($query) {
        $this->query = $query;
    }

    /**
     * mutateur renseigne la classe sur quelle DBForge utiliser
     * @param DbForge $dbForge
     */
    public function setDBForge($dbForge) {
        $this->dbForge = $dbForge;
    }

    /**
     * accesseur
     * @return DbForge
     */
    public function getDbForge() {
        return $this->dbForge;
    }

    public function getTreeBuilder() {
        return $this->tree;
    }

    public function setTreeBuilder($tree) {
        $this->tree = $tree;
    }

    /**
     * réinitialise tous les champs pour créer une nouvelle requête
     */
    public function flushQuery() {
        $this->select = array('main_table' => array(), 'other_tables' => array());
        $this->where = array();
        $this->query = '';
        $this->whereVars = array();
        $this->insertVars = '';
        $this->insertValue = array();
        $this->update = '';
        $this->updateValue = array();
        $this->query = '';
        $this->joinON = '';
        $this->insertFields = '';
        $this->rightEqualityValues = array();
        $this->language = '';
        if (!$this->isTableNameKepped()) {
            $this->tableName = '';
        }
        $this->allLanguage = array();
        $this->noLanguage = FALSE;
        $this->noOtherLanguage = FALSE;
        $this->fetchAll = FALSE;
        $this->limit = '';
        $this->sameWhereKey = 0;
        $this->prefix = '----';
        $this->order = '';
        $this->alias = '';
        $this->tableNameAlias = '';
        $this->arrayMultipleJoin = array();
        $this->stringMultipleJoin = '';
    }

    public function isTableNameKepped() {
        return $this->keepTableName;
    }

    public function setKeepTableName($keepTableName) {
        $this->keepTableName = $keepTableName;
    }

    public function getInsertValue() {
        return $this->insertValue;
    }

    public function getUpdateValue() {
        return $this->updateValue;
    }

    /**
     * Permet de savoir si il y a des champs qui ont été sélléctionés
     * 
     * @return boolean <code>FALSE</code> si il n'y a aucun champs séléctionés
     */
    public function isFields() {
        return !(empty($this->select['main_table']) and empty($this->select['other_table']));
    }

    public function isEmptyWhereClause() {
        return empty($this->whereVars);
    }

    public function getLanguage() {
        return $this->language;
    }

    public function setLanguage($language) {
        $this->language = $language;
        return $this;
    }

    public function getNoOtherLanguage() {
        return $this->noOtherLanguage;
    }

    public function setNoOtherLanguage($noOtherLanguage) {
        $this->noOtherLanguage = $noOtherLanguage;
        return $this;
        ;
    }

    public function getFetchAll() {
        return $this->fetchAll;
    }

    public function setFetchAll($fetchAll) {
        $this->fetchAll = $fetchAll;
        return $this;
    }

    public function getLastInsertId() {
        return $this->lastInsertID;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    public function getAS() {
        if (empty($this->alias)) {
            return $this->tableName;
        }
        return $this->alias;
    }

    /**
     * Ajoute une requête multiple
     * 
     * @param string $tabelName le nom de la table
     * @param array ou string $fields les champs de la table
     * @return QueryBuilder $this
     */
    public function addMultipleJointure($tabelName, $fields, $asc = FALSE, $orderBY = 'id') {
        $arrayFields = array();
        if (is_string($fields)) {
            $arrayFields[] = $fields;
        } elseif (is_array($fields)) {
            $arrayFields = $fields;
        } else {
            throw new Exception("The second parameter of the method 'addMultipleJointure' must be an array or a string");
        }
        $this->arrayMultipleJoin[$this->dbForge->addPrefix($tabelName)] =
                array('fields' => $arrayFields, 'orderBy' => $orderBY, 'asc' => !$asc ? "DESC" : "ASC");
        return $this;
    }

    public function removeMultipleJointure($tableName) {
        //pas implémenté. à faire si besoin
    }

    public function getInsertVars() {
        return $this->insertVars;
    }

    public function setInsertVars($insertVars) {
        $this->insertVars = $insertVars;
    }

    public function getInsertFields() {
        return $this->insertFields;
    }

    public function setInsertFields($insertFields) {
        $this->insertFields = $insertFields;
    }

    public function getJoinON() {
        return $this->joinON;
    }

    public function setJoinON($joinON) {
        $this->joinON = $joinON;
    }

    public function getRightEqualityValues() {
        return $this->rightEqualityValues;
    }

    public function setRightEqualityValues($rightEqualityValues) {
        $this->rightEqualityValues = $rightEqualityValues;
    }

    public function getAllLanguage() {
        return $this->allLanguage;
    }

    public function setAllLanguage($allLanguage) {
        $this->allLanguage = $allLanguage;
    }

    public function getNoLanguage() {
        return $this->noLanguage;
    }

    public function setNoLanguage($noLanguage) {
        $this->noLanguage = $noLanguage;
    }

    public function getLimit() {
        return $this->limit;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function getSameWhereKey() {
        return $this->sameWhereKey;
    }

    public function setSameWhereKey($sameWhereKey) {
        $this->sameWhereKey = $sameWhereKey;
    }

    public function getOrder() {
        return $this->order;
    }

    public function setOrder($order) {
        $this->order = $order;
    }

    public function getAlias() {
        return $this->alias;
    }

    public function setAlias($alias) {
        $this->alias = $alias;
    }

    public function getTableNameAlias() {
        return $this->tableNameAlias;
    }

    public function setTableNameAlias($tableNameAlias) {
        $this->tableNameAlias = $tableNameAlias;
    }

    public function getSeparatorMultipleJoin() {
        return $this->separatorMultipleJoin;
    }

    public function setSeparatorMultipleJoin($separatorMultipleJoin) {
        $this->separatorMultipleJoin = $separatorMultipleJoin;
    }

    public function getArrayMultipleJoin() {
        return $this->arrayMultipleJoin;
    }

    public function setArrayMultipleJoin($arrayMultipleJoin) {
        $this->arrayMultipleJoin = $arrayMultipleJoin;
    }

    public function getStringMultipleJoin() {
        return $this->stringMultipleJoin;
    }

    public function setStringMultipleJoin($stringMultipleJoin) {
        $this->stringMultipleJoin = $stringMultipleJoin;
    }
    
    public function getSeparatorGroupMultipleJoin() {
        return $this->separatorGroupMultipleJoin;
    }

    public function setSeparatorGroupMultipleJoin($separatorGroupMultipleJoin) {
        $this->separatorGroupMultipleJoin = $separatorGroupMultipleJoin;
    }

}

?>
