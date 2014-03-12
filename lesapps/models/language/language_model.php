<?php

/**
 * Modèle de la bdd language
 *
 * @author yves
 */
class Language_model extends MY_Model {
    
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
}

?>
