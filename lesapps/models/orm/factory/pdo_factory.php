<?php

/**
 * Description of sql_factory
 *
 * @author leo
 */
class Pdo_factory extends AbstractFactory {

    public function getDbDriver() {
        $this->loadDBDriver();
        return 'orm/driver/pdo/pdo_driver';
    }

    public function getDbForge() {
        $this->loadBDForge();
        return 'orm/db/pdo/pdo_db_forge';
    }

    public function getQueryBuilder() {
        $this->loadQueryBuilder();
        return 'orm/db/pdo/pdo_query_builder';
    }

    public function getTreeBuilder() {
        $this->loadTreeBuilder();
        return 'orm/db/pdo/pdo_tree_builder';
    }

}

?>
