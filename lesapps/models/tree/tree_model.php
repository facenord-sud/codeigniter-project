<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Permet de gérer l'arbre à données intervallaires
 *
 * @author yves
 */
class Tree_model extends CI_Model {
    
    /**
     * Montre l'arbre
     * 
     * 
     * @param $id l'id de la feuille à récupérer
     * @param $prefix permet de préciser le préfix
     *
     * @return Array() of the tree
     */
    public function showTree($table, $limitations = FALSE, $lang = 0,  $prefix = '-----') {
       $table = $this->driver->getPrefix().$table;
        $limit = '';
        // On vérifie les limitations
        if ($limitations) {
            if (!isset($limitations[1])) {
                $limitations[1] = DATA_PER_PAGE;
            }
            $limit .= ' LIMIT ' . $limitations[0] . ', ' . $limitations[1];
        }
        
        $tableLang = $this->forge->getTableLang($table);
        // Si ta table lang existe, c'est qu'il y a des traductions
        if ($this->forge->isTable($tableLang)) {

            $sql = 'SELECT CONCAT(REPEAT(\''.$prefix.'\', T1.lvl), \' \', ' . $tableLang . '.name) AS name,
                (SELECT COUNT(*)
                FROM   '.$table.' T2
                WHERE  T2.lft > T1.lft
                AND  T2.rgt < T1.rgt) AS descendant,
                T1.id, T1.lvl, T1.lft, T1.rgt, T1.prt   
                FROM ' . $table . ' T1
                LEFT JOIN ' . $tableLang . ' ON T1.id = ' . $tableLang . '.reference 
                LEFT JOIN '.$this->forge->getNameTableLanguage().' ON ' . $tableLang . '.language = '.$this->forge->getNameTableLanguage().'.id
                WHERE ' . $tableLang . '.language = ?
                ORDER BY lft'.$limit;
            $var = array($lang);
            return $this->driver->execute($sql, $var);
        }
        else {
            $sql ='SELECT CONCAT(REPEAT(\''.$prefix.'\', lvl), \' \', name) AS name,
                (SELECT COUNT(*)
                FROM   '.$table.' T2
                WHERE  T2.lft > T1.lft
                AND  T2.rgt < T1.rgt) AS descendant,
                id, lvl, lft, rgt, prt 
                FROM '.$table.' T1 
                ORDER BY lft'.$limit;
            return $this->driver->execute($sql);
        }
    }
    
    /**
     * Permet de récupérer la feuille
     * 
     * 
     * @param $id l'id de la feuille à récupérer
     *
     * @return Array() of the leaf
     */
    public function getLeaf($table, $id) {
        if(!preg_match('`^'.$this->driver->getPrefix().'`', $table)) {
            $table = $this->driver->getPrefix().$table;
        }

        // On récupère les donnée du parent
        $sql = 'SELECT prt, lft, rgt, lvl 
                FROM '.$table.'
                WHERE id = ?';
        $var = array($id);
        return $this->driver->execute($sql, $var);
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
    public function addLeaf($object, $id = 0, $mode = 'ES') {
        $table = $this->forge->getTable($object);
        // C'est le premier de la table
        if ($id == 0) {
            // Si c'est vrai que c'est le premier
            //if ($this->query->count($table) == 0) {
                return $this->dmo->insertObject($object);
            //}
            // Sinon, il y a une erreur
            //return FALSE;
            
        }
        if(!preg_match('`^'.$this->driver->getPrefix().'`', $table)) {
            $table = $this->driver->getPrefix().$table;
        }
        $req = $this->getLeaf($table, $id);

        // On défini les variable dont on a besoin
        $prt = is_numeric($req['prt']) ? (int)$req['prt'] : FALSE;
        $lft = is_numeric($req['lft']) ? (int)$req['lft'] : FALSE;
        $rgt = is_numeric($req['rgt']) ? (int)$req['rgt'] : FALSE;
        $lvl = is_numeric($req['lvl']) ? (int)$req['lvl'] : FALSE;

        // Si une des variables n'est pas, on quitte
        if ($prt === FALSE || $lft === FALSE || $rgt === FALSE || $lvl === FALSE || !is_object($object))
            return FALSE;
        

        switch ($mode) {


            // addEldestSon
            case 'ES': // Fils ainé
                // Limite sup.
                $sql = 'UPDATE '.$table.'
                SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($lft);
                $this->driver->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($lft);
                $this->driver->execute($sql, $var);
                
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
                $sql = 'UPDATE '.$table.'
           	SET rgt = rgt + 2
           	WHERE rgt >= ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

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
                $sql = 'UPDATE '.$table.'
           	SET rgt = rgt + 2
         	WHERE rgt > ?';
                $var = array($lft);
                $this->driver->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 2
           	WHERE lft >= ?';
                $var = array($lft);
                $this->driver->execute($sql, $var);

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
                $sql = 'UPDATE '.$table.'
           	SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

                // Limite inf.
                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 2
           	WHERE lft >= ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

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
                $sql = 'UPDATE '.$table.'
           	SET rgt = rgt + 2
           	WHERE rgt > ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 2
           	WHERE lft > ?';
                $var = array($rgt);
                $this->driver->execute($sql, $var);

                // Décalalage ensemble visé vers le bas
                $sql = 'UPDATE '.$table.'
           	SET lft = lft + 1,
               	rgt = rgt + 1,
               	lvl = lvl + 1
           	WHERE lft >= ? AND rgt <= ?';
                $var = array($lft, $rgt);
                $this->driver->execute($sql, $var);

                $object->lft = $lft;
                $object->rgt = $rgt + 2;
                $object->lvl = $lvl;
                $object->prt = $prt;
                
                break;
        }
        
        // On insert l'objet
        return $this->dmo->insertObject($object);
    }

    /**
     * Suppression d'une feuille
     * 
     * 
     * @param $id l'id de la feuille
     * @param $recurs Suppression recursive ou pas.
     * @return ...
     */
    public function removeLeaf($object, $recurs = false) {

        $table = $this->forge->getTable($object);
        
        // On récupère les donnée de la feuille
        $req = $this->getLeaf($table, $object->id);

        // On défini les variable dont on a besoin
        $prt = is_numeric($req['prt']) ? (int)$req['prt'] : false;
        $lft = is_numeric($req['lft']) ? (int)$req['lft'] : false;
        $rgt = is_numeric($req['rgt']) ? (int)$req['rgt'] : false;

        // Si une des variables n'est pas, on quitte
        if ($prt === FALSE || $lft === FALSE || $rgt === FALSE)
            return FALSE;

        // La suppression est récursive, tout le sous arbre doit être supprimé
        if ($recurs) {

            // Calcul du Delta
            $delta = $rgt - $lft + 1;

            // suppression de tous les éléments
            $sql = 'DELETE FROM '.$table.'
                    WHERE lft >= ?
                    AND rgt <= ?';
            $var = array($lft, $rgt);
            $this->driver->execute($sql, $var);

            // décalage des bornes gauche
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - ?
                    WHERE lft > ?';
            $var = array($delta, $rgt);
            $this->driver->execute($sql, $var);

            // décalage des bornes droites
            $sql = 'UPDATE '.$table.'
                    SET rgt = rgt - ?
                    WHERE rgt > ?';
            $var = array($delta, $rgt);
            $this->driver->execute($sql, $var);
            
            return TRUE;
        } else {
            // NON ! on ne supprime que l'élément
            // suppression de l'élément
            $this->dmo->deleteObject($object);
            
            // décalage des bornes et niveau de l'arbre sous l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - 1,
                    rgt = rgt - 1,
                    lvl = lvl - 1
                    WHERE lft > ?
                    AND rgt < ?';
            $var = array($lft, $rgt);
            $this->driver->execute($sql, $var);

            // décalage des bornes gauches des éléments situés à droite de l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET lft = lft - 2
                    WHERE lft > ?';
            $var = array($rgt);
           $this->driver->execute($sql, $var);

            // décalage des bornes droites des éléments situés à droite de l'élément supprimé
            $sql = 'UPDATE '.$table.'
                    SET rgt = rgt - 2
                    WHERE rgt > ?';
            $var = array($rgt);
            $this->driver->execute($sql, $var);
            
            return TRUE;
        }

        // Si on est la, c'est qu'il ne s'est rien passé
        return FALSE;
    }
}

?>
