<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sqlite_driver
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class Sqlite_driver extends Pdo_driver {

    public function connectDb() {
        $bdd = new PDO('sqlite:'.dirname(__FILE__).'/database.sqlite');
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setBdd($bdd);
    }
}

?>
