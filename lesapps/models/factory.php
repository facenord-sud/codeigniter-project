<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of factory
 *
 * @author leo
 */
class Factory {
    public static function getMysql() {
        return array('dao'=>'dao_mysql_factory', 'entity'=>'entity_mysql_factory', 'form' => 'form_factory', 'handler' => 'handler_factory');
    }
}

?>
