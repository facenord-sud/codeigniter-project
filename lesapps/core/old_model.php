<?php

/**
 * le model qui utilise pdo.
 * Idéalement, il devrait avoir une instance de la classe MY_Pdo, mais c'est pas
 * encore fait
 * 
 * Les objets doivent avoir:
 * -les mêmes noms de variables que les champs de la table
 * -les variables qui corresspondent à la table doivent être publiques
 * -les autres varaiables DOIVENT être private
 *
 * @todo utilisation de la classe MY_Pdo à la place des méthodes internes
 * @author leo+yves
 */
require_once APPPATH . 'models/dao.php';

class MY_Model extends CI_Model {

    private $bdd;
    private $host;
    private $bddName;
    private $user;
    private $mdp;

    const MANY_TO_MANY = '1';

    public function __construct() {
        parent::__construct();
        $this->host = $this->db->hostname;
        $this->bddName = $this->db->database;
        $this->user = $this->db->username;
        $this->mdp = $this->db->password;
        $this->connectDB();
    }

//------------------------dans MY_Pdo à partir d'ici-----
    public function connectDB() {
        try {
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $bdd = new PDO('mysql:host=' . $this->getHost() . ';dbname=' . $this->getBddName() . '',
                            $this->getUser(), $this->getMdp(), $pdo_options);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage() . '<br />
                N° : ' . $e->getCode());
        }
        $bdd->exec('SET NAMES utf8');
        $this->setBdd($bdd);
    }

    /**
     * Pour les méthodes ci-dessous. CF doc sur PDO
     */
    public function prepare($query = '') {
        return $this->getBdd()->prepare($query);
    }

    public function execute($query = '') {
        return $this->getBdd()->execute($query);
    }

    public function query($query = '') {
        return $this->getBdd()->query($query);
    }

    public function exec($query = '') {
        return $this->getBdd()->exec($query);
    }

    /**
     * Fetch sur une requête en dur (pour afficher des données)
     * 
     * @return array or FALSE
     */
    public function fetchQuery($query, $fetchAll = false, $fetchMethode = PDO::FETCH_ASSOC) {
        $req = $this->query($query);
        $req->setFetchMode($fetchMethode);
        if ($fetchAll) {
            $result = $req->fetchAll();
        } else {
            $result->fetch();
        }
        $req->closeCursor();
        return $result;
    }

    /**
     * Fetch sur une requête préparée (pour afficher des données)
     * @Testé FONCTIONNE
     * 
     * @return array or FALSE
     */
    public function fetchPreparedQuery($query, $data, $fetchAll = false, $fetchMethode = PDO::FETCH_ASSOC) {
        $req = $this->prepare($query);
        $req->execute($data);
        $req->setFetchMode($fetchMethode);
        if ($fetchAll) {
            $result = $req->fetchAll();
        } else {
            $result = $req->fetch();
        }
        $req->closeCursor();
        return $result;
    }

    /**
     * Executer la requete préparée (pour des insert, edit, delete)
     * @Attention Pas pour des fetch !
     * @Testé FONCTIONNE
     * 
     * @return True or False
     */
    public function executePreparedQuery($query, $data) {
        $req = $this->prepare($query);
        $result = $req->execute($data);
        $req->closeCursor();
        return $result;
    }

    /**
     * Compte combien d'entrée à la table
     * @Testé seulement avec $searchItems rempli
     * 
     * @param $table, la table à compter
     * @param $conditions, les conditions en dur (where)
     * @param $searchItems un tableau avec tous les champs à chercher.
     *        de la forme: 'nom_du_champ'=>array('value'=>$value, 'op'=>'AND ou bien OR') 
     * 
     *  
     * @author numa j'ai ajouté un paramètre pour compter en passant des variables dans les conditions
     * @return int
     */
    public function count($table, $conditions = FALSE, $searchItems = array()) {
        $where = '';

        if ($conditions) {
            $where = ' WHERE ' . $conditions;
        } elseif (!empty($searchItems)) {
            if (isset($searchItems[count($searchItems) - 1]['op'])) {
                unset($searchItems[count($searchItems) - 1]['op']);
            }
            $where = " WHERE ";
            $vars = array();
            foreach ($searchItems as $key => $searchItem) {
                $vars[$key] = $searchItem['value'];
                $where = $where . "$key=:$key";
                if (isset($searchItem['op'])) {
                    $where.=" " . $searchItem['op'] . " ";
                }
            }
        }
        $sql = 'SELECT * FROM ' . $table . $where;
        if (!empty($searchItems)) {
            $req = $this->prepare($sql);
            $req->execute($vars);
        } else {
            $req = $this->execute($sql);
        }
        $nbr = $req->rowCount();
        $req->closeCursor();
        return $nbr;
    }

    /**
     * Trouve le champs de la table grâce à son ID
     * @Testé FONCTIONNE
     *  
     * @param $table la table dans laquel il se trouve
     * @param $fields (facultatif) array avec les champs que l'on veut récupérer (défault : tous)
     * @param $fetchMethode (facultatif) Le mode du fetch, par defaut PDO::FETCH_ASSOC (array assiotiatif)
     * 
     * @warning Cette méthode peut être réécrite dans le model de l'entité (pour enlever le param $table) 
     * @see : en bas du domain_model.php.
     * @deprecated since version 1.0
     * @numa : que l'on puisse trouver par d'autre champ que l'id
     * 
     * @return default : array
     */
    public function find($id, $table, $fields = FALSE, $lang = 0, $fetchMethode = PDO::FETCH_ASSOC) {
        // Par défaut on séléctionne tous les champs
        $select = '*';

        // On vérifie les fields
        if ($fields) {
            $select = '';
            foreach ($fields as $field) {
                $select .= $field . ', ';
            }
            $select = rtrim($select, ', ');
        }

        $tableLang = $this->getTableLang($table);
        // Si ta table lang existe, c'est qu'il y a des traductions
        if ($this->tableExists($tableLang)) {
            if ($select == '*') {
                $select = $this->makeSelect($this->fusionLang($table));
                $sql = 'SELECT ' . $select;
            } else {
                $sql = 'SELECT ' . $select;
            }
            $sql .= ' FROM ' . $table . ' 
                LEFT JOIN ' . $tableLang . ' ON ' . $table . '.id = ' . $tableLang . '.reference 
                LEFT JOIN language ON ' . $tableLang . '.language = language.id
                    WHERE ' . $table . '.id = ? AND ' . $tableLang . '.language = ?';
            $var = array($id, $lang);
        } else {
            // On crée la requette sql
            $sql = 'SELECT ' . $select . ' FROM ' . $table . ' WHERE id = ?';
            $var = array($id);
        }
        return $this->fetchPreparedQuery($sql, $var, FALSE, $fetchMethode);
    }

    /**
     * permet d'effectuer le select from where
     * 
     * @param String $table
     * @param array $fields
     * @param array $WHERE
     * 
     * @return array Le résultat de la requête mysql
     * 
     * @author
     * @todo écrire la fonction
     */
    public function findWhere($object = NULL, $table = '', $fields = FALSE, $searchItems = array(), $fetchMode = PDO::FETCH_ASSOC) {
        if ($object == NULL and empty($table)) {
            log_message('error', 'une table ou un objet doit être passé en paramètre');
            return false;
        }

        if (empty($table)) {
            $table = $this->getTable($object);
        }

        if (!empty($object->id) and empty($where)) {
            $where = array(
                array('id' => array('value' => $object->id))
            );
        }
        // Par défaut on séléctionne tous les champs
        $select = '*';

        // On vérifie les fields
        if ($fields) {
            $select = '';
            foreach ($fields as $field) {
                $select .= $field . ', ';
            }
            $select = rtrim($select, ', ');
        }

        $where = '';
        if (!empty($searchItems)) {
            if (isset($searchItems[count($searchItems) - 1]['op'])) {
                unset($searchItems[count($searchItems) - 1]['op']);
            }
            $where = " WHERE ";
            $vars = array();
            foreach ($searchItems as $key => $searchItem) {
                $vars[$key] = $searchItem['value'];
                $where = $where . "$key=:$key";
                if (isset($searchItem['op'])) {
                    $where.=" " . $searchItem['op'] . " ";
                }
            }
        }

        $tableLang = $this->getTableLang($table);
        // Si ta table lang existe, c'est qu'il y a des traductions
        if ($this->tableExists($tableLang)) {
            if ($select == '*') {
                $select = $this->makeSelect($this->fusionLang($table));
                $sql = 'SELECT ' . $select;
            } else {
                $sql = 'SELECT ' . $select;
            }
            $sql .= ' FROM ' . $table . ' 
                LEFT JOIN ' . $tableLang . ' ON ' . $table . '.id = ' . $tableLang . '.reference 
                LEFT JOIN language ON ' . $tableLang . '.language = language.id
                    ' . $where . ' AND ' . $tableLang . '.language = ?';
        } else {
            // On crée la requette sql
            $sql = 'SELECT ' . $select . ' FROM ' . $table . $where;
        }

        if (!empty($searchItems)) {
            $req = $this->prepare($sql);
            $req->execute($vars);
        } else {
            $req = $this->query($sql);
        }
        if ($req->rowCount() == 1) {
            return $req->fetch($fetchMode);
        } elseif ($req->rowCount() == 0) {
            return FALSE;
        } else {
            return $req->fetchAll($fetchMode);
        }
    }

    /**
     * Fetch tous les champs d'une table
     * @Testé Non testé
     * 
     * @param $table la table dans laquel il se trouve
     * @param $fields (facultatif) array avec les champs que l'on veut récupérer (défault : tous)
     * @param $conditions, les conditions (where)
     * @param $limitations, array avec Les limits : array(X, Y) : X = début, Y = nbr ligne
     * On peut préciser juste le x, le Y sera alors par défault la constante DATA_PER_PAGE
     * @param $fetchMethode (facultatif) Le mode du fetch, par defaut PDO::FETCH_ASSOC (array assiotiatif)
     * 
     * @todo Donner la possibilité de passer des variables dans les conditions
     * 
     * @ATTENTION Cette méthode peut être réécrite dans le model de l'entité (pour enlever le param $table) 
     * 
     * @return default : array
     */
    public function fetchAll($table, $fields = FALSE, $conditions = FALSE, $limitations = FALSE, $lang = 0, $fetchMethode = PDO::FETCH_ASSOC) {
        // Par défaut on séléctionne tous les champs
        $select = '*';
        // Par défaut, il n'y a pas de condition
        $where = '';
        // Par défaut, il n'y a pas de limit
        $limit = '';

        // On vérifie les fields
        if ($fields) {
            $select = '';
            foreach ($fields as $field) {
                $select .= $field . ', ';
            }
            $select = rtrim($select, ', ');
        }

        // On vérifie les conditions
        if ($conditions) {
            $where .= ' WHERE ' . $conditions;
        }

        // On vérifie les limitations
        if ($limitations) {
            if (!isset($limitations[1])) {
                $limitations[1] = DATA_PER_PAGE;
            }
            $limit .= ' LIMIT ' . $limitations[0] . ', ' . $limitations[1];
        }

        $tableLang = $this->getTableLang($table);
        // Si ta table lang existe, c'est qu'il y a des traductions
        if ($this->tableExists($tableLang)) {
            if ($select == '*') {
                $select = $this->makeSelect($this->fusionLang($table));
                $sql = 'SELECT ' . $select;
            } else {
                $sql = 'SELECT ' . $select;
            }
            // On vérifie les conditions
            if ($conditions) {
                $where .= ' AND ' . $conditions;
            }

            $sql .= ' FROM ' . $table . ' 
                LEFT JOIN ' . $tableLang . ' ON ' . $table . '.id = ' . $tableLang . '.reference 
                LEFT JOIN language ON ' . $tableLang . '.language = language.id
                    WHERE ' . $tableLang . '.language = ?';
            $sql .= $where;
            $sql .= $limit;
            $var = array($lang);
            return $this->fetchPreparedQuery($sql, $var, TRUE, $fetchMethode);
        } else {
            // On crée la requette sql
            $sql = 'SELECT ' . $select . ' FROM ' . $table . $where . $limit;
            return $this->fetchQuery($sql, TRUE, $fetchMethode);
        }
    }

//--------------------------------jusqu'à là________

    /**
     * 
     * Permet d'aller chercher l'onglet requis dans la bdd et de le charger dans 
     * le bon objet défini par $class
     * @Testé FONCTIONNE
     * 
     * @param Object $objet l'objet à manipuler
     * @param int $id l'id du champ et de l'objet
     * @param $lang , l'id de la lang
     * @param (Facultatif) String $table le nom de la table dans la bdd
     *
     * @todo les conditions
     * @todo $id optionel : YVES : Fait, à tester
     * YVES : J'ai mis FETCH_INTO plutôt que FETCH_CLASS
     * @return Object or FALSE
     */
    public function loadObject($object, $id = 0, $lang = 0, $table = '') {
        if (empty($table)) {
            $table = $this->getTable($object);
        }
        if ($id == 0) {
            $id = $object->id;
        }
        if ($lang != 0) {
            $lang = $object->language;
        }
        $tableLang = $this->getTableLang($table);
        // Si ta table lang existe, c'est qu'il y a des traductions
        if ($this->tableExists($tableLang)) {
            $select = $this->makeSelect($this->fusionLang($table));

            // Selectionner les valeurs à traduire et récupérer en plus de l'objet de base.
            $sql = 'SELECT ' . $select . '  
                FROM ' . $table . ' 
                LEFT JOIN ' . $tableLang . ' ON ' . $table . '.id = ' . $tableLang . '.reference 
                LEFT JOIN language ON ' . $tableLang . '.language = language.id 
                WHERE ' . $table . '.id = ? AND language.id = ?';
            $var = array($id, $lang);
        } else {
            $sql = 'SELECT * FROM ' . $table . ' WHERE id = ?';
            $var = array($id);
        }
        $req = $this->prepare($sql);
        $req->execute($var);
        $req->setFetchMode(PDO::FETCH_INTO, $object);
        $res = $req->fetch();
        $req->closeCursor();
        return $res;
    }

    /**
     * Permet de sauvegarder un objet dans une bdd.
     * La méthode gère elle-même le fait créer un nouveau champ ou de mettre à 
     * jour un champ existant
     * @Testé FONCTIONNE
     * 
     * @param Object $class l'objet à manipuler
     * @param (Facultatif) String $table le nom de la table dans la bdd
     * 
     * @todo les conditions
     * @todo supprimer le param $table->fait -> tester si çA marche
     * @Return Boolean

     */
    public function saveObject($object, $table = '') {
        if (empty($table)) {
            $table = $this->getTable($object);
        }
        if ($this->find($object->id, $table)) { // Il existe, on update dans la bdd
            return $this->updateObject($object, $table);
        } else { // Il n'existe pas, on l'insert
            return $this->insertObject($object, $table);
        }
    }

    /**
     * Permet de mettre à jour un champ de la table donnée à partir d'un objet
     * @Testé FONCTIONNE
     * 
     * @param Object $class l'objet à manipuler
     * @param (Facultatif) String $table le nom de la table dans la bdd
     * 
     * @Return Boolean

     */
    public function updateObject($object, $table = '') {
        if (empty($table)) {
            $table = $this->getTable($object);
        }
        $tableLang = $this->getTableLang($table);
        // Chercher si la table de traduction existe
        if ($this->tableExists($tableLang)) {
            // On récupère les champs
            $fields = $this->fusionLang($table);
            $sql = 'UPDATE ' . $table . ' 
                    LEFT JOIN ' . $tableLang . ' 
                        ON ' . $table . '.id = ' . $tableLang . '.reference 
                    LEFT JOIN language 
                        ON ' . $tableLang . '.language = language.id ';
            $sql .= 'SET ';
            // On récupère les champs
            foreach ($fields as $key => $field) {
                // Ca évite de changer l'id par erreur.
                if ($key != 'id') {
                    $sql .= ' ' . $field . ' = :' . $key . ', ';
                }
            }
            $sql = rtrim($sql, ', ');
            $sql .= ' WHERE ' . $table . '.id = :id AND language.id = :language';

            $data = array();
            // On met les variables dans $var
            foreach ($object AS $key => $value) {
                $data[$key] = $value;
            }
            $data['language'] = $object->language;
        } else {
            $data = array();
            $sql = 'UPDATE `' . $table . '` SET';
            foreach ($object as $key => $value) {
                // Ca évite de changer l'id par erreur.
                if ($key != 'id') {
                    $sql .= ' `' . $key . '` = :' . $key . ',';
                }
                $data[$key] = $value;
            }
            $sql = rtrim($sql, ',');
            $sql .= ' WHERE `' . $table . '`.`id` = :id;';
        }
        $req = $this->prepare($sql);
        $result = $req->execute($data);
        $req->closeCursor();

        if ($result)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Permet d'insérer un champ dans une table donnée à partir d'un objet
     * @Testé FONCTIONNE pas tout à fait. Parenthèse non fermé dans la requête sql. ->numa
     * @test ok
     * 
     * @param Object $object l'objet à manipuler
     * @param (Facultatif) String $table le nom de la table dans la bdd
     * 
     * @todo les conditions
     * @param String $table la table dans la bdd
     * @param Object $object l'objet à insérer dans la bdd
     * @todo supprimer le param $table->fait -> tester si çA marche
     * @return Boolean
     * 
     * @author numa + yves

     */
    public function insertObject($object, $table = '') {
        if (empty($table)) {
            $table = $this->getTable($object);
        }
        $tableLang = $this->getTableLang($table);
        // Chercher si la table de traduction existe
        if ($this->tableExists($tableLang)) {
            // On récupère les champs
            $fields = $this->fusionLang($table);

            $tableLangInsert = 'INSERT INTO ' . $tableLang . ' ( ';
            $tableLangValues = ' ) VALUES ( ';
            $tableLangData = array();
            $tableInsert = 'INSERT INTO ' . $table . ' ( ';
            $tableValues = ' ) VALUES ( ';
            $tableData = array();

            foreach ($fields AS $key => $field) {
                if ($key != 'id') {
                    // C'est les champs de la table_lang
                    if (strpos($field, $tableLang) === 0) {
                        $tableLangInsert .= $key . ', ';
                        $tableLangValues .= ':' . $key . ', ';
                        $tableLangData[$key] = $object->$key;
                    } else {
                        $tableInsert .= '' . $key . ', ';
                        $tableValues .= ':' . $key . ', ';
                        $tableData[$key] = $object->$key;
                    }
                }
            }

            // INSERTION DE L OBJET
            $tableInsert = rtrim($tableInsert, ', ');
            $tableValues = rtrim($tableValues, ', ');
            $tableValues = $tableValues . ' )';
            $tableSql = $tableInsert . $tableValues;

            $req = $this->prepare($tableSql);
            $result1 = $req->execute($tableData);
            $req->closeCursor();

            // Insertion de la traduction
            $tableLangInsert .= 'language, ';
            $tableLangValues .= ':language, ';
            $tableLangInsert .= 'reference, ';
            $tableLangValues .= ':reference, ';
            $tableLangData['language'] = $object->language;
            $tableLangData['reference'] = $this->getBdd()->lastInsertId(); // LA référence est la dernière id insérée dans la bdd
            $tableLangInsert = rtrim($tableLangInsert, ', ');
            $tableLangValues = rtrim($tableLangValues, ', ');
            $tableLangValues = $tableLangValues . ' )';
            $tableLangSql = $tableLangInsert . $tableLangValues;

            $req = $this->prepare($tableLangSql);
            $result2 = $req->execute($tableLangData);
            $req->closeCursor();

            if ($result1 AND $result2)
                return TRUE;
            else
                return FALSE;
        } else {
            $data = array();
            $insert = 'INSERT INTO `' . $table . '` ( ';
            $values = 'VALUES (';
            foreach ($object as $key => $value) {
                if ($key != 'id') {
                    $insert .= $key . ', ';
                    $values .= ':' . $key . ', ';
                    $data[$key] = $value;
                }
            }
            $insert = rtrim($insert, ', ');
            $values = rtrim($values, ', ');
            $sql = $insert . ')' . $values . ');';
            $req = $this->prepare($sql);
            $result = $req->execute($data);
            $req->closeCursor();
            return $result;
        }
    }

    /**
     * permet d'effacer un champ dans une table donnée
     * 
     * @param $object l'objet à effacer
     * @param (Facultatif) String $table le nom de la table dans la bdd
     * 
     * @todo les conditions
     * @return Boolean
     */
    public function deleteObject($object, $table = '') {
        if (empty($table)) {
            $table = $this->getTable($object);
        }
        $tableLang = $this->getTableLang($table);
        // Chercher si la table de traduction existe
        if ($this->tableExists($tableLang)) {
            // Si elle est, on doit aussi virer les champs de trad lié
            $sql = 'DELETE FROM ' . $tableLang . ' WHERE reference = ?';
            $req = $this->prepare($sql);
            $result = $req->execute(array($object->id));
            $req->closeCursor();
        }
        $sql = 'DELETE FROM ' . $table . ' WHERE `id` = ?';
        $req = $this->prepare($sql);
        $result = $req->execute(array($object->id));
        $req->closeCursor();

        if ($result)
            return TRUE;
        else
            return FALSE;
    }

    /**
     * Ca va chercher le nom de table grâce à la class (car elle porte le même nom)
     * @return string
     */
    public function getTable($object) {
        // ça met en minuscul, ça enlève le _entity à la fin
        return str_replace('_entity', '', strtolower(get_class($object)));
    }

    /**
     * Ca va chercher le nom de table de traduction
     * @return string
     */
    public function getTableLang($table) {
        return $table . '_lang';
    }

    /**
     * Table Exists permet de savoir sur la table en question existe
     * 
     * @param $table La table à chercher
     * @return Boolean
     */
    public function tableExists($table) {
        $sql = 'SHOW TABLES 
                LIKE \'' . $table . '\'';
        $exec = $this->query($sql);
        return $exec->rowCount();
    }

    /**
     * Fusionne les champs des deux tables (table et table_lang)
     * 
     * @param $object, l'objet en question
     * @return array() of the fields sous form de array('nomDuChamps' => 'table.nomDuChamp')
     */
    public function fusionLang($table) {
        $tableLang = $this->getTableLang($table);

        // Trouver les champs de la table $tableLang qui sont différent de id, language et reference
        $sql = 'DESCRIBE ' . $tableLang;
        $req = $this->query($sql);

        $fields = array();
        // Faire passer tout les champs
        while ($field = $req->fetchColumn()) {
            // Si il est différent, c'est que c'est un champ traduit : à récupérer.
            if ($field != 'id' AND $field != 'language' AND $field != 'reference') {
                // On l'ajoute dans $Fields
                $fields[$field] = $tableLang . '.' . $field;
            }
        }
        // On continue mais maintenant avec les variables de table
        // Trouver les champs de la table $table qui sont différent de id, language et reference
        $sql = 'DESCRIBE ' . $table;
        $req = $this->query($sql);

        while ($field = $req->fetchColumn()) {
            // On prend toutes les autres variables de la table
            $fields[$field] = $table . '.' . $field;
        }
        return $fields;
    }

    /**
     * Permet de créer un sélect SQL grâce a un array de field
     * @param type $fields
     * @return string
     */
    public function makeSelect($fields) {
        //On construit notre select
        $select = '';
        foreach ($fields as $field) {
            // Faire la requete
            $select .= $field . ', ';
        }
        return rtrim($select, ', ');
    }

    /**
     * Permet de récupérer l'id de la langue grâce à l'iso de celle-ci
     * 
     * @param $iso iso de la langue
     *
     * @return int
     */
    public function getLangId($iso) {
        $sql = 'SELECT id FROM language WHERE lang = ?';
        $var = array($iso);
        $req = $this->fetchPreparedQuery($sql, $var);
        return $req['id'];
    }

    /**
     * Permet de sauvegarder tous les tableaux d'une classe dans une bdd relationelle.
     * détermine si il faut insérer ou ne rien faire
     * Pour l'instant, seulement en many to many
     * @test pas fait
     * @param Object $object l'objet entité qui contient les tableaux à sauvegarder.
     * @return boolean true si tout c'est bien passé
     * @author numa
     * @todo optimiser la requête sql pour insérer
     * @todo la requête sql pour modifier
     */
    public function saveArray($class) {

        if (empty($class) or !is_object($class)) {
            return false;
        }

        $refClass = new ReflectionClass($class);
        $properties = $refClass->getProperties(ReflectionProperty::IS_PRIVATE);
        foreach ($properties as $property) {
            $tableClass = strtolower(substr(get_class($class), 0, strpos(get_class($class), '_entity')));
            if ($this->findRelational($property, $class) == self::MANY_TO_MANY) {
                foreach ($property->getValue($class) as $arrayObject) {
                    if (is_object($arrayObject)) {
                        $id = $arrayObject->id;
                        if (empty($id)) {
                            log_message('error', 'l\'entité ne contient pas d\'id');
                            continue;
                        }
                    } elseif (is_array($arrayObject)) {
                        $id = $arrayObject['id'];
                        if (empty($id)) {
                            log_message('error', 'l\'entité ne contient pas d\'id');
                            continue;
                        }
                    } else {
                        log_message('error', 'ce n\'est ni un objet ni un tableau');
                        continue;
                    }

                    $test = $this->count($property->getName() . '_' . $tableClass, FALSE, array('id_role' => array('value' => $id, 'op' => 'AND'), 'id_user' => array('value' => $class->id)));
                    if ($test == 0) {
                        $sql = 'INSERT INTO `' . $property->getName() . '_' . $tableClass . '` (`id_' . $property->getName() . '`, `id_' . $tableClass . '`) VALUES(:id_property, :id_class);';
                        $this->executePreparedQuery($sql, array('id_property' => $id, 'id_class' => $class->id));
                    }
                }
            }
            $property->setAccessible(FALSE);
        }
        return true;
    }

    /**
     * Permet de connaitre la relation dans la bdd d'une variable d'un objet de type entity
     * pour l'instant uniquement possible de savoir le many to many
     * 
     * @param ReflectionProperty $property
     * @param Object $class
     * @return int 0 si erreure, sinon constante de la classe
     */
    public function findRelational($property, $class) {
        if (empty($property)) {
            return 0;
        }
        $property->setAccessible(TRUE);
        $var = $property->getValue($class);
        if (is_array($var)) {
            //pour savoir si une table du nom du tableau existe
            if ($this->tableExists($property->getName())) {
                //pour savoir si une table du nom de la classe existe
                $tableClass = strtolower(substr(get_class($class), 0, strpos(get_class($class), '_entity')));
                if ($this->tableExists($tableClass)) {
                    //pur savoir si une table nom-de-la-variable_nom-de-la-classe existe
                    if ($this->tableExists($property->getName() . '_' . $tableClass)) {
                        //mnt on sait. c'est possible de sauvegarder en many to many dans la bdd
                        return self::MANY_TO_MANY;
                    }
                }
            }
        }
    }

    /**
     * 
     * Charge dans les l'entité toutes les valeures des ses tableaux correspondant 
     * aux tables de la bdd.
     * Pour l'instant, seulement en many to many et charge toutes les valeures disponibles.
     * Travail seulement avec les tableau
     * 
     * @author numa
     * 
     * @param Object $object entité dans la quelle seront chargé les valeures du many to many
     */
    public function loadArray($object) {
        $refClass = new ReflectionClass($object);
        $properties = $refClass->getProperties(ReflectionProperty::IS_PRIVATE);
        $id = $object->id;

        foreach ($properties as $property) {
            $tableClass = strtolower(substr(get_class($object), 0, strpos(get_class($object), '_entity')));
            $relTable = $property->getName() . '_' . $tableClass;
            if ($this->findRelational($property, $object) == self::MANY_TO_MANY) {

                $fields = '';
                $tableFields = $this->getFields($property->getName());
                foreach ($tableFields as $field) {
                    $fields.=$property->getName() . '.' . $field . ', ';
                }
                $fields = substr($fields, 0, strlen($fields) - 2);
                $sql = "SELECT " . $fields . " FROM " . $tableClass . ", " . $property->getName() . ", $relTable WHERE $tableClass.id=$relTable.id_$tableClass and " . $property->getName() . ".id=$relTable.id_" . $property->getName() . " and $tableClass.id=:id";
                $req = $this->prepare($sql);
                $req->execute(array('id' => $id));
                $res = $req->fetchAll(PDO::FETCH_ASSOC);
                $req->closeCursor();
                $refMethod = new ReflectionMethod(get_class($object), 'set' . ucfirst($property->getName()));
                $refMethod->invoke($object, $res);
            }
            $property->setAccessible(FALSE);
        }
    }

    /**
     * renvoie tous les champs d'une table
     * 
     * @param String $table le nom de la table dans la quelle chercher
     * @return array avec tous les nom des champs de la table
     */
    public function getFields($table) {
        $req = $this->getBdd()->query("DESCRIBE $table;");
        $fields = array();
        $res = $req->fetchAll();
        foreach ($res as $field) {
            $fields[] = $field['Field'];
        }
        return $fields;
    }

    public function getBdd() {
        return $this->bdd;
    }

    public function setBdd($bdd) {
        $this->bdd = $bdd;
    }

    public function getHost() {
        return $this->host;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function getBddName() {
        return $this->bddName;
    }

    public function setBddName($bddName) {
        $this->bddName = $bddName;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getMdp() {
        return $this->mdp;
    }

    public function setMdp($mdp) {
        $this->mdp = $mdp;
    }

    public function getFetchMod() {
        return $this->fetchMod;
    }

    public function setFetchMod($fetchMod) {
        $this->fetchMod = $fetchMod;
    }

}

?>
