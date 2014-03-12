<?php
/**
 * le modèle qui gère les utilisateur.
 * implémente les méthodes pour lire, écrire, modifier la bdd qui ne peuvent pas
 * être faite de manière générique dans la classe DAO
 *
 * @author leo
 */

require_once APPPATH.'models/user/abstractUser.php';

class User_model extends MY_Model implements AbstractUser{
    
    /**
     * Cette méthode retourne un tableau de tous les rôles d'un membre à partir
     * de l'id du membre.
     * 
     * Elle permet de savoir tous les rôles qu'a un membre. par exemple, il pourrait
     * être simple utilisateur, ainsi que rédacteur et que modérateur. A voir si
     * ça fait vraiment sens.
     * 
     * @TESTED ça fonctionne
     * @param int $id l'id du membre
     * @return array un tableau associatif avec tous les roles d'un membre
     */
    public function loadRole($id) {
        $sql = "SELECT role.name, role.description FROM user, role, role_user WHERE user.id=role_user.id_user and role.id=role_user.id_role and user.id=? LIMIT 0, 30 ";
        return  $this->fetchPreparedQuery($sql, array($id), true);
    }
    
}

?>
