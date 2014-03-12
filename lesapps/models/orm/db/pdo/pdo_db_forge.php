<?php

/**
 * La classe qui gère toutes les opérations sur les tables en utilisant
 * le driver pdo
 *
 * @author leo
 */
class pdo_db_forge extends DbForge {

    /**
     * 
     * @test passed
     * @param String $tabelName le nom de la table
     * @return boolean true si la table existe
     */
    public function isTable($tabelName) {
        $sql = 'SHOW TABLES 
                LIKE \'' . $tabelName . '\'';
        $count = $this->driver->count($sql);
        if ($count >= 1) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * renvoie tous les champs d'une table
     * 
     * @param String $table le nom de la table dans la quelle chercher
     * @return array avec tous les nom des champs de la table
     */
    public function getFields($tableName) {
        $tableName = $this->getTable($tableName);
        $res = $this->driver->execute("DESCRIBE $tableName;");
        $fields = array();
        if (isset($res['Field'])) {
            $res = array($res);
        }
        foreach ($res as $field) {
            $fields[] = $field["Field"];
        }
        return $fields;
    }

    public function getAllLanguage() {
        return $this->driver->execute('SELECT * FROM ' . $this->getNameTableLanguage());
    }

}

?>
