<?php

/**
 * Description of user_modify_form
 *
 * @property User_entity $user
 * @author leo
 */
class user_modify_form extends CI_Model {

    private $user;

    public function printForm() {
        echo validation_errors();

        echo form_open();
        echo form_fieldset($this->lang->line('fieldset_title'));
        $this->email->form();
        echo my_form('enabled', 'enabled', 'cb', $this->user->enabled);
        echo my_form('locked', 'locked', 'cb', $this->user->locked);
        echo my_form('banned', 'banned', 'cb', $this->user->banned);
        echo my_form('points', 'points', '', $this->user->points);

        foreach ($this->user->getRole() as $role) {
            echo $role['nick_name'];
            echo form_checkbox('roles[]', $role['id'], TRUE);
            $this->query->where('id', $role['id'], 'AND', '!=');
            echo '</br>';
        }
        $roles = $this->query->setTableName('role')->setFetchAll(TRUE)->select();
        if ($roles) {
            foreach ($roles as $role) {
                echo $role['nick_name'];
                echo form_checkbox('roles[]', $role['id'], FALSE);
                echo '</br>';
            }
        }

        echo form_submit('submit', $this->lang->line('submit'));
        echo form_fieldset_close();
        echo form_close();
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getRoles() {
        return $this->roles;
    }

    public function setRoles($roles) {
        $this->roles = $roles;
    }

}

?>
