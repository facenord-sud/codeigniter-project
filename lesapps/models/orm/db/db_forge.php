<?php

/**
 * classe abstraite définissant les méthodes pour intérargir avec les tables
 * utiliser u driver pour excuter les requêtes
 *
 * @property DbDriver $driver
 * @author leo
 */
abstract class DbForge {

    /**
     *
     * @var DbDriver le driver qui s'occupe de la connection à la bdd et de l'excution des requêtes 
     */
    protected $driver;

    public abstract function isTable($tabelName);

    public abstract function getFields($tableName);

    public abstract function getAllLanguage();

    public function setDriver($driver) {
        $this->driver = $driver;
    }

    public function getDriver() {
        return $this->driver;
    }

    /**
     * Ca va chercher le nom de table grâce à la class (car elle porte le même nom)
     * @return string
     */
    public function getTable($object) {
        // ça met en minuscul, ça enlève le _entity à la fin
        if (is_object($object)) {
            $object = get_class($object);
        }
        return str_replace('_entity', '', strtolower($object));
    }

    /**
     * Ca va chercher le nom de table de traduction
     * @return string
     */
    public function getTableLang($table) {
        return $this->getTable($table) . '_lang';
    }

    public function getNameTableLanguage() {
        return $this->getDriver()->getPrefix() . 'language';
    }

    public function getFieldsTableLang($tableName) {
        $tableLang = $this->getTableLang($tableName);
        $fieldsTableLang = $this->getFields($tableLang);
        foreach ($fieldsTableLang as $key => $field) {
            if ($field == 'id' OR $field == 'reference' OR $field == 'language') {
                unset($fieldsTableLang[$key]);
            } else {
                $fieldsTableLang[$key] = $tableLang . '.' . $field;
            }
        }
        return $fieldsTableLang;
    }
    
    /**
     * Si besoin est, ajoute le préfixe au nom de la table
     * 
     * @param string $tableName le nom de la table
     * @return string la bale avec le préfixe
     */
    public function addPrefix($tableName) {
        if (!preg_match('`^' . $this->getDriver()->getPrefix() . '`', $tableName)) {
            return $tableName = $this->getDriver()->getPrefix() . $tableName;
        }
    }

}

?>
