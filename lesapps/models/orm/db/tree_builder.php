<?php

/**
 * Permet de gérer les arbres à données intervallaires
 *
 * @property DbForge $dbForge
 * @property QueryBuilder $query
 * @author Yves
 */
abstract class TreeBuilder {
    
    protected $queryBuilder = NULL;
    protected $dmo = NULL;
    protected $object = NULL;
    protected $nodesOrLeaves = NULL;
    protected $where = ' ';
    protected $and = ' ';
    protected $echoNodesOrLeaves = '';
    
    // La feuille de référence
    protected $leaf = array();
    
    /**
     * initialise les variables avec les bonnes valeures
     */
    public function __construct() {
        $this->flush();
    }
    

    /**
     * Nettoie $queryBuilder et Tree
     */
    public function flush($flushQuery = FALSE) {
        if ($flushQuery) $this->getQueryBuilder()->flushQuery();
        $this->object = NULL;
        $this->nodesOrLeaves = NULL;
        $this->where = ' ';
        $this->and = ' ';
        $this->echoNodesOrLeaves = '';
        $this->leaf = array();
    }
    
    /**
     * Affiche l'arbre des données intervallaires
     * 
     * @param type $table
     * @param type $ref donne l'id, le tableau, l'objet de référence
     * @param type $nodesOrLeaves permet de définir si on veut les noeufs (nodes), les feuilles,(leaves) ou tout (FALSE)
     * @param $joinRef permet de définir si on inclu ou exclu la référence dans le tableau de retour
     * @param $reverse Permet de prendre les tous les éléments indépendants d'un élément de référence (complément au sous arbre)
     * @param type $object retourn un tableau d'objet dans l'$objet mit en parametre
     * @param type BOOL $count, si c'est TRUE, on compte juste le nombre de sortie.
     * @return 
     */
    public abstract function get($table = '', $ref = NULL, $nodesOrLeaves = FALSE, $joinRef = FALSE, $reverse = FALSE, $object = FALSE, $count = FALSE);
    
    /**
     * Insertion d'une feuille
     * 
     * 
     * @param $id l'id de la feuille de référence
     * @param $name nom du '.$table.'
     * @param $description du '.$table.'
     * @param $mode le mode d'ajout ('ES' = Fils ainé, 'YS' = Fils cadet, 'BB' = Grand frère, 
     * 'LB' = Petit frère, 'F' = Père)
     * @return Boolean
     */
    public abstract function add($object, $ref = NULL, $mode = 'ES');
    
    /**
     * Suppression d'élément(s) dans l'arbre
     * 
     * 
     * @param $id l'id de la feuille
     * @param $recurs Suppression recursive ou pas.
     * @return ...
     */
    public abstract function remove($object, $recurs = FALSE);

    /**
     * Compte les éléments de l'arbre
     * 
     * @param string $table le nom de la table
     * @return integer le nombre des éléments de l'arbre
     */
    public function count($table = '', $ref = NULL, $joinRef = FALSE) {
        return $this->get($table, $ref, FALSE, $joinRef, FALSE, FALSE, TRUE);
    }

    /**
     * Compte les feuilles de l'arbre
     * 
     * @param string $table le nom de la table
     * @param type $ref 
     * @return int
     */
    public function countLeaves($table = '', $ref = NULL, $joinRef = FALSE) {
        return $this->get($table, $ref, 'leaves', $joinRef, FALSE, FALSE, TRUE);
    }

    /**
     * Compte les noeuds de l'arbre
     * 
     * @param string $table le nom de la table
     * @param type $ref
     * @return int
     */
    public function countNodes($table = '', $ref = NULL, $joinRef = FALSE) {
        return $this->get($table, $ref, 'nodes', $joinRef, FALSE, FALSE, $true);
    }

    /**
     * Retourn l'arbre entier
     * 
     * @param string $table le nom de la table
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getTree($table = '', $ref = NULL, $object = FALSE) {
        return $this->get($table, $ref, FALSE, FALSE, FALSE, $object);
    }

    /**
     * Retourne les feuilles de l'arbre
     * 
     * @param string $table le nom de la table
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getLeaves($table = '', $ref = NULL, $object = FALSE) {
        return $this->get($table, $ref, 'leaves', FALSE, FALSE, $object);
    }

    /**
     * Retourne les noeuds de l'arbre
     * 
     * @param string $table le nom de la table
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getNodes($table = '', $ref = NULL, $object = FALSE) {
        return $this->get($table, $ref, 'nodes', FALSE, FALSE, $object);
    }

    /**
     * Retourne l'arbre complémentaire
     * 
     * @param string $table le nom de la table
     * @param type $ref Description
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getReverseTree($table = '', $ref = NULL, $object = FALSE) {
        return $this->get($table, $ref, FALSE, FALSE, TRUE, $object);
    }

    /**
     * Retourne le sous-arbre
     * 
     * @param string $table le nom de la table
     * @param type $ref Description
     * @param type $ref Description
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getSubTree($table = '', $ref = NULL, $joinRef = FALSE, $object = FALSE) {
        return $this->get($table, $ref, FALSE, $joinRef, FALSE, $object);
    }

    /**
     * retourn les feuilles du sous-arbre
     * 
     * @param string $table le nom de la table
     * @param type $ref Description
     * @param type $ref Description
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getSubLeaves($table = '', $ref = NULL, $joinRef = FALSE, $object = FALSE) {
        return $this->get($table, $ref, 'leaves', $joinRef, FALSE, $object);
    }

    /**
     * Retourne les noeuds du sous-arbre
     * 
     * @param string $table le nom de la table
     * @param type $ref Description
     * @param type $ref Description
     * @param Object $object l'objet en paramètre si on veut un tableau d'objet
     * @return tableau associatif ou tableau d'objet
     */
    public function getSubNodes($table = '', $ref = NULL, $joinRef = FALSE, $object = FALSE) {
        return $this->get($table, $ref, 'nodes', $joinRef, FALSE, $object);
    }
    
    /*
     * Ajouter un fils ainé
     */
    public function addEldestSon($object = '', $ref = NULL) {
        $this->add($object, $ref, 'ES');
        return $this;
    }
    
    /*
     * Ajouter un fils cadet
     */
    public function addYoungerSon($object = '', $ref = NULL) {
        $this->add($object, $ref, 'YS');
        return $this;
    }
    
    /*
     * Ajouter un grand frère
     */
    public function addBigBrother($object = '', $ref = NULL) {
        $this->add($object, $ref, 'BB');
        return $this;
    }
    
    /*
     * Ajouter un petit frère
     */
    public function addLittleBrother($object = '', $ref = NULL) {
        $this->add($object, $ref, 'LB');
        return $this;
    }
    
    /*
     * Ajouter un père
     */
    public function addFather($object = '', $ref = NULL) {
        $this->add($object, $ref, 'F');
        return $this;
    }
    
    /*
     * Supprimer un élément seul
     */
    public function removeLeaf($object) {
        $this->remove($object, FALSE);
    }
    
    /*
     * Supprimer un sous arbre depuis une référence (comprise)
     */
    public function removeSubTree($object) {
        $this->remove($object, TRUE);
    }
    
    /**
     * 
     * @return QueryBuilder
     */
    public function getQueryBuilder() {
        return $this->queryBuilder;
    }

    /**
     * 
     * @param QueryBuilder $queryBuilder
     */
    public function setQueryBuilder($queryBuilder) {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }
    
    public function getDmo() {
        return $this->dmo;
    }

    public function setDmo($dmo) {
        $this->dmo = $dmo;
        return $this;
    }

    
    /**
     * 
     * @return Entity
     */
    public function getObject() {
        return $this->object;
    }

    /**
     * 
     * @param Entity $object
     * @return \TreeBuilder
     */
    public function setObject($object) {
        $this->object = $object;
        return $this;
    }
}