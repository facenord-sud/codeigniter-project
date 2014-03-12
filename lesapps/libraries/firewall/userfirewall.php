<?php

/**
 * Description of UserFirewall
 *
 * @author leo
 */
class UserFirewall {

    /**
     * 
     * @param User $CI
     */
    public function profil($CI) {
        $id = $CI->uri->rsegment(3);
        if(empty($id)) {
            redirect(find_uri('user', 'profil').'/'.$CI->usersession->getInfos(UserSession::ID));
        }
        if ($id == $CI->usersession->getInfos(UserSession::ID)) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param User $CI
     */
    public function index($CI) {
        if ($CI->usersession->isConnected() and !$CI->usersession->hasRole(UserSession::ADMIN)) {
            redirect(find_uri('user', 'profil'));
        }
        if (!$CI->usersession->isConnected()) {
            redirect(find_uri('user', 'connect'));
        }
    }
    
    /**
     * 
     * @param User $CI
     */
    public function register($CI) {
        if ($CI->usersession->isConnected() and !$CI->usersession->hasRole(UserSession::ADMIN)) {
            redirect(find_uri('main', 'index'));
        }
    }

}



?>
