<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sqlite_factory
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 * @property Pdo_factory $pdoFactory
 */
class Sqlite_factory extends AbstractFactory {

    private $pdoFactory = NULL;


    public function __construct() {
        
        $this->pdoFactory = $this->useFactory('pdo');
    }

    public function getDbDriver() {
        $this->extendsFrom($this->pdoFactory->getDbDriver());
        return 'orm/driver/pdo/sqlite_driver';
    }

    public function getDbForge() {
        return $this->pdoFactory->getDbForge();
    }

    public function getQueryBuilder() {
        return $this->pdoFactory->getQueryBuilder();
    }

    public function getTreeBuilder() {
        return $this->pdoFactory->getTreeBuilder();
    }

}

?>
