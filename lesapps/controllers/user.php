<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * le controlleur pour la gestion des utilisateurs
 * 
 * @property User_entity $user
 * @property Contact_entity $contact
 * @property Address_entity $address
 * @author leo
 */
class User extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->entity('user/user', 'user');
        $this->load->orm();
    }

    /**
     * Genère le nombre d'utilisateur passés en paramètre.
     * Le nombre peut être sans limites et les utilisateurs ont générés un par un
     * ...
     * 
     * @param int $nb le nombre d'utilisateur à générer
     */
    public function genUser($nb) {
        $i = 0;
        for ($i = 0; $i <= $nb; $i++) {
            $this->user = new User_entity();
            $this->user->username = 'test' . $i;
            $this->user->email = 'test' . $i . '@collaide.com';
            $this->user->password = hash('sha512', 'test.' . $i);
            $this->user->date_creation = date('Y-m-d H:i:s', time());
            $this->user->last_login = date('Y-m-d H:i:s', time());
            $this->user->enabled = TRUE;
            $this->user->banned = FALSE;
            $this->user->locked = FALSE;
            $this->user->points = 5;
            $this->query->setTableName('role')->where('nick_name', UserSession::SIMPLE_MEMBER)->fields('id');
            $this->user->addRole($this->query->select());
            $this->dmo->saveObject($this->user);
        }
        redirect(find_uri('user'));
    }

    /**
     * page principal de ce bundle
     * 1. Si l'utlisateur est un admin, permet la gestion de tous les utilisateurs du
     * site<br/>
     * 2. Si l'utilisateur est connecté, le redirige vers son profil</br>
     * 3. si l'utilisateur est non connect, redirige vers la page d'inscription
     *
     * 
     */
    public function index($limit = 0) {
        if ($limit == 0) {
            noDuplicate(site_url(find_uri('user', 'index')));
        } else {
            noDuplicate(site_url(find_uri('user', 'index')).'/'.$limit);
        }
        $this->load->helper('print');
        $this->load->helper('form');
        $this->load->library('pagination');
        $this->twiggy->register_function('tableFetchAll');

        $users = $this->query->setTableName('user')->setFetchAll(TRUE)->limit($limit)->select();
        foreach ($users as $key => $user) {
            $user['cb'] = '<input type="checkbox" name="aUser[]" value="' . $user['id'] . '"/>';
            $users[$key] = $user;
        }

        $config['base_url'] = site_url(find_uri('user', 'index'));
        $config['total_rows'] = $this->query->count('user');
        $config['num_links'] = 10;
        $this->pagination->initialize($config);

        $this->twiggy->set('users', $users);
        $this->twiggy->set('user_dropdown', pdoArrayToDropDown($users, 'username'));
        $this->twiggy->set('pagination', $this->pagination);
    }

    /**
     * Page pour voir le profil du membre selon son id
     * Seulement 
     *
     * @param int $id l'id d'un membre
     */
    public function profil($id = 0) {

        $this->dmo->setLoadRelation(TRUE);
        $this->dmo->loadObject($this->user, $id);
        $this->twiggy->set('user_profil', $this->user);
        $this->twiggy->set('contact', $this->user->getContact());
        $this->twiggy->set('roles', $this->user->getRole());
        $this->twiggy->set('friends', $this->user->friend->where('demand', FALSE)->get());

        $this->user->friend->where('demand', TRUE)->get();
        foreach ($this->user->getFriend() as $key => $friend) {
            $this->user->getFriend()[$key] = $friend->user->get();
        }
    }

    /**
     * page pour une nouvelle inscription
     * @todo envoi email de confirmation
     */
    public function register() {


        $this->load->helper('cookie');
        $this->lang->loads('user/profil');
        $this->load->form('user/user_new', 'form');
        /*
         * Twiggy
         */

        $this->twiggy->set('form', $this->form);
        $this->twiggy->title()->prepend($this->lang->line('title'));
        $this->twiggy->meta('description', $this->lang->line('description'));


        if ($this->form_validation->run() == TRUE) {
            $this->user->username = $this->input->post('username');
            $this->user->email = $this->input->post('email');
            $this->user->password = hash('sha512', $this->input->post('password'));
            $this->user->date_creation = date('Y-m-d H:i:s', time());
            $this->user->last_login = date('Y-m-d H:i:s', time());
            $this->user->enabled = TRUE;
            $this->user->banned = FALSE;
            $this->user->locked = FALSE;
            $this->user->points = 5;
            $this->query->setTableName('role')->where('nick_name', UserSession::SIMPLE_MEMBER)->fields('id');
            $this->user->addRole($this->query->select());
            $this->dmo->saveObject($this->user);
            $this->checkUserActivation($this->user);
            $this->usersession->register($this->user);
            set_cookie('remeber_me', $this->user->id, 365 * 24 * 60 * 60);
            $this->session->set_flashdata('new_inscription', TRUE);
            redirect(find_uri('user', 'valid_inscription'));
        }
    }

    public function _checkSamePassword($str) {
        if ($this->input->post('password') != $str) {
            $this->form_validation->set_message('_checkSamePassword', $this->lang->line('not_same_password'));
            return FALSE;
        }

        return TRUE;
    }

    public function _checkUserName($str) {
        $this->query->setTableName('user')->fields('id');
        $this->query->where('username', $str);
        if ($this->query->select() != FALSE) {
            $this->form_validation->set_message('_checkUserName', sprintf($this->lang->line('same_username'), $str));
            return FALSE;
        }
        return TRUE;
    }

    public function _checkEmail($str) {
        $this->query->setTableName('user')->fields('id')->where('email', $str);
        if ($this->query->select() != FALSE) {
            $this->form_validation->set_message('_checkEmail', sprintf($this->lang->line('same_email'), $str));
            return FALSE;
        }
        return TRUE;
    }

    public function _checkOldPassword($str) {
        if ($this->user->password != hash('sha512', $str)) {
            $this->form_validation->set_message('_checkOldPassword', "Votre mot de passe actuel n'est pas le bon");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * page de confirmation pour une inscriptione réussie
     */
    public function valid_inscription() {
        if (!$this->session->flashdata('new_inscription')) {
            redirect(find_uri('main', 'index'));
        }
    }

    /**
     * page pour modifier les données d'un membre
     * @param int $id du membre
     */
    public function edit($id) {

        $this->dmo->loadObject($this->user, $id);
        $this->user->addRole(array('id' => 3));
        $this->dmo->saveObject($this->user);
        $this->usersession->register($this->user);
    }

    public function connect() {

        $this->lang->loads('user/profil');

        $this->load->form('user/user_connect', 'form');
        if ($this->form_validation->run() == TRUE) {
            $this->_checkConnection();
        }
        $this->twiggy->set('msg', $this->session->flashdata('msg'));
    }

    public function logout() {
        $this->load->helper('cookie');
        delete_cookie('remeber_me');
        $this->usersession->logOut();
        $this->twiggy->set('user', FALSE);
    }

    /**
     * gère la connection
     */
    public function _checkConnection() {
        $this->lang->loads('user/connect');
        $this->load->helper('cookie');

        $this->dmo->setLoadOptions('role', Dmo::MANY_TO_MANY);
        $this->query->where('username', $this->input->post('username'));
        $this->query->where('password', hash('sha512', $this->input->post('password')));
        $isUser = $this->dmo->loadObject($this->user);

        if ($isUser) {
            $this->checkUserActivation($this->user);
            $this->usersession->register($this->user);
            $this->user->last_login = $this->usersession->getInfos(UserSession::START_TIME);
            $this->dmo->saveObject($this->user);
            if ($this->input->post('remeber_me')) {
                set_cookie('remeber_me', $this->user->id, 365 * 24 * 60 * 60);
            } else {
                delete_cookie('remeber_me');
            }
            redirectLastPage();
        } else {
            $msg = 'Vous n\'êtes pas dans la bdd';
            $this->usersession->logOut();
            $this->session->set_flashdata('msg', $msg);
            redirect(find_uri('user', 'connect'));
        }
    }

    /**
     * 
     * @param type $id
     */
    public function modify($id = 0) {
        if (empty($id)) {
            redirect(find_uri('user', 'modify') . '/' . $this->input->post('id_user'));
        }
        $this->load->form('user/user_modify', 'form');
        $this->load->form('user/user_email', 'email');
        $this->load->form('group/group_creat', 'group');
        $this->load->entity('user/role', 'role');
        $this->dmo->setLoadOptions('*');
        $this->dmo->loadObject($this->user, $id);

        $this->twiggy->set('user_info', $this->user);
        $this->form->setUser($this->user);
        $this->_saveEmail();
        if ($this->form_validation->run('user/modify')) {
            $this->user->banned = $this->input->post('banned');
            $this->user->enabled = $this->input->post('enabled');
            $this->user->locked = $this->input->post('locked');
            $this->user->points = $this->input->post('points');
            $this->user->setRole(array());
            foreach ($this->input->post('roles') as $role) {
                $this->user->addRole(array('id' => $role));
            }
            $this->dmo->saveObject($this->user, $id);
            redirect(find_uri('user'));
        }
    }

    public function delete($id = 0) {
        if (empty($id)) {
            redirect(find_uri('user', 'delete') . '/' . $this->input->post('id_user'));
        }
        $this->load->form('user/user_delete', 'form');
        $this->dmo->setLoadOptions('*');
        $this->dmo->loadObject($this->user, $id);
        $this->twiggy->set('user_info', $this->user);
        if ($this->form_validation->run('user/delete')) {
            $this->dmo->deleteObject($this->user, $id);
            redirect(find_uri('user'));
        }
    }

    public function _checkDelete($str) {
        if ($this->user->username != $str) {
            $this->form_validation->set_message('_checkDelete', 'Raté, le pseudo que vous avez donné ne correspond pas. Réessayez.');
            return FALSE;
        }
        return TRUE;
    }

    public function resetPassword($id) {
        $this->dmo->loadObject($this->user, $id);
        $this->load->helper('string');
        $password = random_string('alnum', 16);
        $this->user->password = hash('sha512', $password);
        $this->twiggy->set('password', $password);
        $this->twiggy->set('user_infos', $this->user);
        $this->dmo->saveObject($this->user, $id);
    }

    public function selectAll() {
        $action = $this->input->post('action');
        $users = $this->input->post('aUser');
        if (empty($users)) {
            redirect(find_uri('user'));
        }
        foreach ($users as $idUser) {
            if ($action == 'delete') {
                $this->dmo->deleteObject($this->user, $idUser);
            } else {
                $this->user = new User_entity();
                $this->dmo->loadObject($this->user, $idUser);

                if ($action == 'point') {
                    if ($this->input->post('addPoint')) {
                        $this->user->points+=$this->input->post('point');
                    } else {
                        $this->user->points = $this->input->post('point');
                    }
                } elseif ($action == 'enable') {
                    $this->user->enabled = $this->input->post('check');
                } elseif ($action == 'lock') {
                    $this->user->locked = $this->input->post('check');
                } else {
                    $this->user->banned = $this->input->post('check');
                }
                $this->dmo->saveObject($this->user, $idUser);
            }
        }

        redirect(find_uri('user'));
    }

    /**
     *
     *
     */
    public function editMail() {
        $this->dmo->loadObject($this->user, $this->usersession->getInfos(UserSession::ID));
        $this->load->form('user/user_email');

        if ($this->_saveEmail()) {
            redirect(find_uri('user', 'profil'));
        }
    }

    private function _saveEmail() {
        if ($this->form_validation->run('user/editMail')) {
            $this->user->email = $this->input->post('email');
            $this->dmo->saveObject($this->user);
            return TRUE;
        }
    }

    /**
     *
     *
     */
    public function editPassword() {
        $this->dmo->loadObject($this->user, $this->usersession->getInfos(UserSession::ID));
        $this->load->form('user/user_password');
        if ($this->form_validation->run()) {
            $this->user->setPassword($this->input->post('password'));
            $this->dmo->saveObject($this->user);
            redirect(find_uri('user', 'profil'));
        }
    }

    /**
     *
     *
     */
    public function contact() {
        $this->load->form('user/user_contact');
        $this->load->entity('user/contact', 'contact');
        $this->dmo->setLoadOptions('*');
        $this->dmo->loadObject($this->user, $this->usersession->getInfos(UserSession::ID));
        $this->twiggy->set('addresses', $this->user->getAddress());
        $contact = $this->user->getContact();
        if (!property_exists($contact, 'id')) {
            $contact = $this->contact;
        }
        $this->form->setContact($contact);

        if ($this->form_validation->run()) {
            $this->contact->f_name = $this->input->post('f_name');
            $this->contact->last_name = $this->input->post('l_name');
            $this->user->setContact($this->contact);
            $this->dmo->saveObject($this->user);
            redirect(find_uri('user', 'profil'));
        }
    }

    /**
     *
     *
     */
    public function close() {
        if ($this->input->post('yes')) {
            $this->user->locked = TRUE;
            $this->dmo->saveObject($this->user);
            $this->load->helper('cookie');
            delete_cookie('remeber_me');
            $this->usersession->logOut();
            $this->twiggy->set('user', FALSE);
            redirect(find_uri('main'));
        }
        else {
            redirect(find_uri('main'));
        }
    }

    /**
     *
     *
     */
    public function address($id = 0) {
        $this->load->form('user/user_address');
        $this->load->entity('user/address', 'address');
        $this->dmo->setLoadOptions('*');
        $this->dmo->loadObject($this->user);
        if (!empty($id)) {
            $this->dmo->loadObject($this->address, $id);
        }
        $this->form->setAddress($this->address);
        if ($this->form_validation->run()) {
            $this->address->country = $this->input->post('country');
            $this->address->name = $this->input->post('name');
            $this->user->addAddress($this->address);
            $this->dmo->saveObject($this->user);
            redirect(find_uri('user', 'profil'));
        }
    }

    /**
     *
     *
     */
    public function test() {
//cette méthode n'affiche rien. C'est uniquement pour montrer comment ça marche

        $this->load->entity('user/role', 'role');
        $this->role->get(); // va chercher tous les rôles traduis de la bdd
        $this->user->get();
        $this->user->find(101); // charge l'utilisateur dont l'id = 101
        $this->user->find(); //va chercher l'utilisateur l'id = l'id de l'entité
        $this->user->find('admine', 'username'); // va chercher l'utilisateur dont son ername = admin
        $this->user->where('password', '123')->find('admin', 'username'); // l'utilisateur dont le mot de passe et 1234 et le username est admin
//charge toutes les entités friend dont le champ demand = 1
//en claire, toutes les demandes d'amis
//retourne un tableau d'objet et assigne ce tableau à la variable friend de User_entity
        $this->user->friend->where('demand', TRUE)->get();
//pour chaque demande d'amis
        foreach ($this->user->getFriend() as $friend) {
//charge l'utilisateur qui à fait la demande
            $friend->user->get();
        }

//la première demande d'amis n'est pas accepté
//dsl, ce n'est pas tout à fait au point, il faut 2 lignes. ça sera bientôt corrigé
        unset($this->user->getFriend()[0]);
        $this->user->save();

//supprime le contact de l'utilisateur
//comprtpment sur ce qui est effacé pas testé à 100%
        $this->user->contact->destroy();

//efface l'utilisateur et  toutes ses relations, mais par exemple 
//n'efface pas les roles de la bdd, seulement les relations avec l'utilisateur
        $this->user->destroy();
    }

}

