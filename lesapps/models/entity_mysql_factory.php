<?php

/**
 * La classe qui donne le nom et le chemin pour chaque entité du projet
 *
 * @author leo
 */
class Entity_mysql_factory {
    
    public function getDomainEntities() {
        return 'domain/entity/';
    }
    
    public function getDomainEntity() {
        return $this->getDomainEntities().'domain_entity';
    }
    
    public function getUserEntity() {
        return 'user/entity/';
    }
    
    public function getUser() {
        return $this->getUserEntity().'user_entity';
    }
    
    public function getRole() {
        return $this->getUserEntity().'role';
    }
}

?>
