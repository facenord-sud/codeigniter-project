<?php

/**
 * Gère la session d'un utilisateur
 *
 * @author leo
 */
class UserSession extends Phpsession {

    /**
     *
     * @var String $sessionID l'identifiant de session 
     */
    private $sessionID = '3k2h14289gwdf';

    const ID = 'id';
    const USER_NAME = 'username';
    const EMAIL = 'email';
    const ROLES = 'roles';
    const START_TIME = 'start_time';
    const IP_USER = 'ip_user';
    const ADMIN = "admin";
    const SIMPLE_MEMBER = "simple_member";

    public function __construct() {
        parent::Phpsession();
    }

    /**
     * Permet de créer une nouvelle session pour un untilisateur
     * @param User_entity $user l'objet user. on utilise quelques infos de cet objet
     */
    public function register($user) {
        $this->saveInfos(self::ID, $user->id);
        $this->update($user);
        $this->saveInfos(self::START_TIME, date('Y-m-d H:i:s', time()));
        $this->saveInfos(self::IP_USER, $_SERVER['REMOTE_ADDR']);
    }

    public function update($user) {
        $this->saveInfos(self::USER_NAME, $user->username);
        $this->saveInfos(self::EMAIL, $user->email);
        $this->saveInfos(self::ROLES, $user->getRole());
    }

    /**
     * Sauvegarde des données supplémentaires dans la session
     * 
     * @param mixed $key la clé pour retrouver la valeure
     * @param mixed $value la valeure à enregistré dans la session
     */
    public function saveInfos($key, $value) {
        $this->save($key, $value, $this->sessionID);
    }

    /**
     * permet de retourver des infos sur l'utilisateur
     * 
     * @param type $key la clé pour trouver la valeure recherchée
     * @return boolean si pas connecté sinon mixed
     */
    public function getInfos($key) {
        if (!$this->isConnected()) {
            return FALSE;
        }
        return $this->get($key, $this->sessionID);
    }

    /**
     * Permet de savoir si l'utilisateur est connecté
     * @return boolean FALSE si l'utilisateur n'est pas connecté
     */
    public function isConnected() {
        return isset($_SESSION[$this->sessionID]);
    }

    /**
     * efface la session d'un utilisateur.
     * ->se déconnecte
     */
    public function logOut() {
        unset($_SESSION[$this->sessionID]);
    }

    /**
     * 
     * @return boolean false si l'utilisateur n'est pas connecté sinon un tableau
     */
    public function getAllUserInfos() {
        if ($this->isConnected()) {
            return $_SESSION[$this->sessionID];
        } else {
            return FALSE;
        }
    }

    public function hasRole($role) {
        $roles = $this->getInfos(UserSession::ROLES);
        if (!empty($roles)) {
            foreach ($this->getInfos(UserSession::ROLES) as $roleSession) {
                if ($roleSession['nick_name'] == $role) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

}

?>
