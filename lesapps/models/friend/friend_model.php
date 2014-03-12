<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of friend_model
 *
 * @author leo
 */
class friend_model extends CI_Model {

    public function getFriendsDemand($userID) {
        $this->query->join('friend_user', 'friend.id', 'friend_user.id_friend')->join('user', 'friend_user.id_user', 'user.id');
        $this->query->where('demand', TRUE)->where('user.id', $userID, 'AND', '=', TRUE)->fields('id_user');
        return $this->query->select('friend');
    }

}

?>
