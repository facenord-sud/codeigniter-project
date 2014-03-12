<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Le modèle qui gère les domaines
 * implémente les méthodes pour lire, écrire, modifier la bdd intervallaire qui ne peut pas
 * être fait de manière générique dans la classe DAO
 *
 * @author yves
 */

require_once APPPATH.'models/tree/tree_model.php';

class Domain_model extends Tree_model  {
    
    public function toString($name, $level, $prefix = '-----') {
       
    }
        
}