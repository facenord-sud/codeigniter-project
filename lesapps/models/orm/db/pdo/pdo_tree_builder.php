<?php

/**
 * Permet de gérer les arbres intervallaires
 *
 * @author Yves
 */
class Pdo_tree_builder extends TreeBuilder {

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
    public function get($table = '', $ref = NULL, $nodesOrLeaves = FALSE, $joinRef = FALSE, $reverse = FALSE, $object = FALSE, $count = FALSE) {
        if (empty($object)) {
            $object = $this->getObject();
        }
        if (!empty($object)) {
            $this->getQueryBuilder()->setTableName($object);
        }
        if (!empty($table)) {
            $this->getQueryBuilder()->setTableName($table);
        }
        if (!preg_match('`^' . $this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . '`', $this->getQueryBuilder()->getTableName())) {
            $this->getQueryBuilder()->setTableName($this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . $this->getQueryBuilder()->getTableName());
        }

        $order = $this->getQueryBuilder()->getOrder();
        if (empty($order)) {
            $this->getQueryBuilder()->order('lft');
        }

        // Ajoute la ref (fait ce qu'il y a en haut)
        $this->addReference($ref, $joinRef, $reverse);

        $this->getQueryBuilder()->translateSelect();

        $this->_nodesOrLeaves($nodesOrLeaves);

        if ($this->getQueryBuilder()->getFields() == '*') {
            $this->getQueryBuilder()->fields($this->getQueryBuilder()->getDbForge()->getFields($this->getQueryBuilder()->getTableName()));
        }

        // SI on doit juste compter, on fait un $select plus simple (plus rapide)
        if ($count) {
            $select = 'SELECT ' . $this->getQueryBuilder()->getTableName() . '.id FROM ' . $this->getQueryBuilder()->getTableName() . ' ' . $this->getQueryBuilder()->getJoinON() . ' ' . $this->where . ' ' . $this->getQueryBuilder()->getWhere() . ' ' . $this->echoNodesOrLeaves . ' ' . $this->getQueryBuilder()->getOrder() . ' ' . $this->getQueryBuilder()->getLimit();
            $res = $this->getQueryBuilder()->getDbForge()->getDriver()->count($select, $this->getQueryBuilder()->getAllValues());
        } else {
            $select = 'SELECT CONCAT(REPEAT(\'' . $this->getQueryBuilder()->getPrefix() . '\', lvl), \'\', name) AS prefix_name,
                (SELECT COUNT(*)
                FROM   ' . $this->getQueryBuilder()->getTableName() . ' T2
                WHERE  T2.lft > ' . $this->getQueryBuilder()->getTableName() . '.lft
                AND  T2.rgt < ' . $this->getQueryBuilder()->getTableName() . '.rgt) AS descendant,
                ' . $this->getQueryBuilder()->getFields() . '
                FROM ' . $this->getQueryBuilder()->getTableName() . ' ' . $this->getQueryBuilder()->getJoinON() . ' ' . $this->where . ' ' . $this->getQueryBuilder()->getWhere() . ' ' . $this->echoNodesOrLeaves . ' ' . $this->getQueryBuilder()->getOrder() . ' ' . $this->getQueryBuilder()->getLimit();
            
            if (!$object) {
                $res = $this->getQueryBuilder()->getDbForge()->getDriver()->execute($select, $this->getQueryBuilder()->getAllValues());
            } elseif (is_object($object)) {
                $res = $this->getQueryBuilder()->getDbForge()->getDriver()->fetchClass($select, $this->getQueryBuilder()->getAllValues(), $object);
            } else {
                log_message('error', 'The $object parameter must be FALSE or an object');
                $res = FALSE;
            }
        }
        $this->flush(TRUE);
        return $res;
    }

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
    public function add($object = '', $ref = NULL, $mode = 'ES') {
        if (empty($object)) {
            $object = $this->getObject();
        }
        if (!empty($object)) {
            $this->getQueryBuilder()->setTableName($object);
        }
        // C'est le premier de la table
        if (!$ref) {
            // Si c'est vrai que c'est le premier
            //if ($this->query->count($table) == 0) {
            $this->getDmo()->insertObject($object);
            return $this;
            //}
            // Sinon, il y a une erreur
            //return FALSE;
        }
        if (!preg_match('`^' . $this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . '`', $this->getQueryBuilder()->getTableName())) {
            $this->getQueryBuilder()->setTableName($this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . $this->getQueryBuilder()->getTableName());
        }

        // Récupère les infos de la reférence
        $this->_getLeafInfos($ref);
        $lft = $this->leaf['lft'];
        $rgt = $this->leaf['rgt'];
        $lvl = $this->leaf['lvl'];
        $prt = $this->leaf['prt'];

        // Si une des variables n'est pas, on quitte
        if (!is_object($object)) {
            log_message('error', 'The parametre $object must be an object');
            return FALSE;
        }

        $table = $this->getQueryBuilder()->getTableName();

        switch ($mode) {


            // addEldestSon
            case 'ES': // Fils ainé
                // Limite sup.
                $sql = 'UPDATE ' . $table . '
                SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($lft);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($lft);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $lfti = $lft + 1;
                $rgti = $lft + 2;
                $lvli = $lvl + 1;

                $object->lft = $lfti;
                $object->rgt = $rgti;
                $object->lvl = $lvli;
                $object->prt = $prt;

                break;


            // addYoungestSon
            case 'YS': // Fils cadet
                // Limite sup.
                $sql = 'UPDATE ' . $table . '
           	SET rgt = rgt + 2
           	WHERE rgt >= ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $lfti = $rgt;
                $rgti = $rgt + 1;
                $lvli = $lvl + 1;

                $object->lft = $lfti;
                $object->rgt = $rgti;
                $object->lvl = $lvli;
                $object->prt = $prt;

                break;


            // addBigBrother
            case 'BB':

                // Limite sup.
                $sql = 'UPDATE ' . $table . '
           	SET rgt = rgt + 2
         	WHERE rgt > ?';
                $var = array($lft);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 2
           	WHERE lft >= ?';
                $var = array($lft);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $lfti = $lft;
                $rgti = $lft + 1;
                $lvli = $lvl;

                $object->lft = $lfti;
                $object->rgt = $rgti;
                $object->lvl = $lvli;
                $object->prt = $prt;

                break;


            // addLittleBrother
            case 'LB':

                // Limite sup.
                $sql = 'UPDATE ' . $table . '
           	SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 2
           	WHERE lft >= ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $lfti = $rgt + 1;
                $rgti = $rgt + 2;
                $lvli = $lvl;

                $object->lft = $lfti;
                $object->rgt = $rgti;
                $object->lvl = $lvli;
                $object->prt = $prt;

                break;


            // addFather
            case 'F':

                //Décalage de l'ensemble colatéral droit
                $sql = 'UPDATE ' . $table . '
           	SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                // Décalalage ensemble visé vers le bas
                $sql = 'UPDATE ' . $table . '
           	SET lft = lft + 1,
               	rgt = rgt + 1,
               	lvl = lvl + 1
           	WHERE lft >= ? AND rgt <= ?';
                $var = array($lft, $rgt);
                $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

                $object->lft = $lft;
                $object->rgt = $rgt + 2;
                $object->lvl = $lvl;
                $object->prt = $prt;

                break;
        }

        // On insert l'objet
        $this->getDmo()->insertObject($object);
        $this->flush(TRUE);
        return $this;
    }
    
    /**
     * Suppression d'une feuille
     * 
     * 
     * @param $id l'id de la feuille
     * @param $recurs Suppression recursive ou pas.
     * @return ...
     */
    public function remove($object, $recurs = FALSE) {
        $this->getQueryBuilder()->setTableName($this->getQueryBuilder()->getDbForge()->getTable($object));
        
        if (!preg_match('`^' . $this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . '`', $this->getQueryBuilder()->getTableName())) {
            $this->getQueryBuilder()->setTableName($this->getQueryBuilder()->getDbForge()->getDriver()->getPrefix() . $this->getQueryBuilder()->getTableName());
        }
        $table = $this->getQueryBuilder()->getTableName();
        
        // On récupère les donnée de la feuille
        // On défini les variable dont on a besoin
        $this->_getLeafInfos($object);
        $lft = $this->leaf['lft'];
        $rgt = $this->leaf['rgt'];
        $prt = $this->leaf['prt'];

        // Si une des variables n'est pas, on quitte
        if ($prt === FALSE || $lft === FALSE || $rgt === FALSE) {
            log_message('error', '$object not valid !');
            return FALSE;
        }

        // La suppression est récursive, tout le sous arbre doit être supprimé
        if ($recurs) {
            // Calcul du Delta
            $delta = $rgt - $lft + 1;

            // suppression de tous les éléments
            $sql = 'DELETE FROM '.$table.'
                    WHERE lft >= ?
                    AND rgt <= ?';
            $var = array($lft, $rgt);
            $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

            // décalage des bornes gauche
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - ?
                    WHERE lft > ?';
            $var = array($delta, $rgt);
            $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

            // décalage des bornes droites
            $sql = 'UPDATE '.$table.'
                    SET rgt = rgt - ?
                    WHERE rgt > ?';
            $var = array($delta, $rgt);
            $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);
            
            $this->getDmo()->deleteObject($object);
        } else {
            // NON ! on ne supprime que l'élément
            // suppression de l'élément
            $this->getDmo()->deleteObject($object);
            
            // décalage des bornes et niveau de l'arbre sous l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - 1,
                    rgt = rgt - 1,
                    lvl = lvl - 1
                    WHERE lft > ?
                    AND rgt < ?';
            $var = array($lft, $rgt);
            $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

            // décalage des bornes gauches des éléments situés à droite de l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - 2
                    WHERE lft > ?';
            $var = array($rgt);
           $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);

            // décalage des bornes droites des éléments situés à droite de l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET rgt = rgt - 2
                    WHERE rgt > ?';
            $var = array($rgt);
            $this->getQueryBuilder()->getDbForge()->getDriver()->execute($sql, $var);
        }
        $this->flush(TRUE);
        return $this;
    }

    /*
     * Ajoute si on veut voir que les noeuds, que les feuilles, ou tout
     */

    protected function _nodesOrLeaves($nodesOrLeaves = '') {
        if (!empty($nodesOrLeaves)) {
            $this->nodesOrLeaves = $nodesOrLeaves;
        }
        // On ne met pas de AND si il y'a a pas d'autre where avant.
        $whereRes = $this->getQueryBuilder()->getWhere();
        if (!empty($whereRes)) {
            $this->where = "WHERE";
            // On met un AND, si il y a where avant
            $this->and = "AND";
        }

        // Défini si on prend les nodes ou les leaves
        if ($this->nodesOrLeaves) {
            if ($this->nodesOrLeaves == 'leaves') {
                $this->echoNodesOrLeaves = $this->and . ' rgt - lft = 1';
            } elseif ($this->nodesOrLeaves == 'nodes') {
                $this->echoNodesOrLeaves = $this->and . ' rgt - lft > 1';
            }
            if (empty($whereRes)) {
                $this->echoNodesOrLeaves = 'WHERE ' . $this->echoNodesOrLeaves;
            }
        }
        return $this;
    }

    protected function _getLeafInfos($leaf) {
        //On cherche la reférence (un tableau est plus légé qu'un objet ici)
        if (is_numeric($leaf)) {
            $this->leaf = $this->getQueryBuilder()->getDbForge()->getDriver()->execute('SELECT lft, rgt, prt, lvl FROM ' . $this->getQueryBuilder()->getTableName() . ' WHERE id = ?', array($leaf));
        } elseif (is_array($leaf)) {
            $this->leaf = $leaf;
        } elseif (is_object($leaf)) {
            $this->leaf['lft'] = $leaf->lft;
            $this->leaf['rgt'] = $leaf->rgt;
            $this->leaf['prt'] = $leaf->prt;
            $this->leaf['lvl'] = $leaf->lvl;
        } else {
            // Pas possible
            log_message('error', 'The $leaf parameter must be an integer, array or object');
        }
        return $this;
    }

    /*
     * Ajoute les where nécessaires pour une référence.
     */

    public function addReference($ref = FALSE, $joinRef = FALSE, $reverse = FALSE) {
        // Ajoute la référence dans le where
        if ($ref) {
            $echoLft = '>';
            $echoRgt = '<';
            // Si c'est reverse, on inverse les signes
            if ($reverse) {
                $echoLft = '<';
                $echoRgt = '>';
            }
            // Si on join l'element, on ajoute le =
            $echoJoinRef = '';
            if ($joinRef) {
                $echoJoinRef = '=';
            }

            // Récupère les infos de la reférence
            $this->_getLeafInfos($ref);

            // Il faut récupérer lft et rgt de l'id $ref. et les ajouter dans where
            $this->getQueryBuilder()->where('lft', $this->leaf['lft'], 'AND', $echoLft . $echoJoinRef);
            $this->getQueryBuilder()->where('rgt', $this->leaf['rgt'], 'AND', $echoRgt . $echoJoinRef);
        }
        return $this;
    }

}