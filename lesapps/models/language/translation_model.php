<?php

/**
 * Permet de traduire les champs dans les base de données
 *
 * @author yves
 */
class Translation_model extends MY_Model {
    
    public function insertTranslation($table, $traductions, $fields, $lang = 1) {
        
    }
    
    /**
     * Permet de récupérer la traduction
     * 
     * @param $table nom de la table à traduire
     * @param $id du champ à traduire
     * @param $lang id de la lang
     *
     * @return lang
     */
    public function getTranslation($table, $id, $lang = 1) {
        $tableLang = $this->getTransTable($table);
        $sql = 'SELECT * FROM '.$tableLang.' trans
                LEFT JOIN '.$table.' table ON trans.reference = table.id
                LEFT JOIN language ON trans.language = language.id
                WHERE table.id = ? AND language.id = ?';
        $var = array($id, $lang);
        return $this->fetchPreparedQuery($sql, $var);
    }
    
    /**
     * Permet de créer la table lang en suivant la table de base
     * 
     * 
     * @param $table nom de la table de base
     * @param $fields Les champs à traduire
     *
     * @return lang
     */
    public function creatTable($table, $fields) {

    }
    
    /**
     * Permet de récupérer la table de base pour laquel cette table traduit
     * 
     * 
     * @param $table nom de la table
     * @param $id l'id de la donnée
     *
     * @return lang
     */
    public function getTable($table, $id) {

        $sql = '';
        $var = array($id);
        //$req = $this->fetchPreparedQuery($sql, $var);
        //return $req['lang'];
    }
    
    /**
     * Permet de récupérer la langue de la donnée
     * 
     * 
     * @param $table nom de la table
     * @param $id l'id de la donnée
     *
     * @return lang
     */
    public function getLang($table, $id) {

        $sql = 'SELECT lang 
                FROM ' . $table . '
                WHERE id = ?';
        $var = array($id);
        $req = $this->fetchPreparedQuery($sql, $var);
        return $req['lang'];
    }
    
    /**
     * Permet de récupérer le nom de la table de traduction
     * 
     * @param $table nom de la table
     *
     * @return lang
     */
    public function getTransTable($table) {
        return $table.'_lang';        
    }
}

?>
