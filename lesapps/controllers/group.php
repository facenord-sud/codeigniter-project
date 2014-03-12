<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Permet la gestion des groupes
 * @property Group_entity $group
 * @property invitation_entity $invitation
 * @property File_entity $file
 * @property  Tree_model $tree
 * @property Group_member_entity $member
 * @property Role_entity $role
 * @property minimess_entity $minimess
 * @author leo
 */
class group extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->entity('group/group', 'group');
//par défaut l'entité groupe est passé à twiggy
        $this->twiggy->set('group_entity', $this->group);
    }

    /**
     * permet de créer un nouveau groupe
     */
    public function create() {
        $this->load->form('group/group_creat');
        $this->load->entity('file/file', 'file');
//        $this->load->model($this->daoFact->getDomainModel(), 'tree', TRUE);
        $this->load->entity('group/group_member', 'member');
        $this->load->entity('user/role', 'role');

        $this->_saveGroup();
    }

    /**
     * sauvegarde le groupe
     * 1. lui attribue un répertoire de groupe
     * 2. enregistre les infos de base sur le groupe
     * 3. si la case à cocher indiquait privé, on lui donne un mot de passe
     * 4 la personne qui à crée le groupe est l'administrateur
     * 5. on sauvegarde le tout dans la bdd
     */
    public function _saveGroup() {
        if ($this->form_validation->run('group/create')) {
//création du répertoir du groupe
            $this->file->date = time();
            $this->file->slug = '.';
//le nom du répertoir est le nom du groupe
            $this->file->name = $this->input->post('name');
            $this->file->folder = '';
            $this->tree->addEldestSon($this->file, Group_entity::ID_FIELD_GROUP);
            $this->group->setFile($this->file);

//les infos du groupe
            $this->group->name = $this->input->post('name');
            $this->group->description = $this->input->post('description');
            $this->group->date_creation = $this->input->post('date_creation');
            $this->group->website = $this->input->post('website');
            $this->group->slug = url_title($this->group->name);
            $this->group->date_creation = mktime();

//si on veut un groupe privé
            if ($this->input->post('is_private')) {
                $this->group->password = $this->input->post('password');
            }

            //on sauvegarde
            $this->group->save();

//on charge l'utilisateur qui est connecté
            if ($this->user->id != $this->usersession->getInfos(UserSession::ID)) {
                $this->user->find($this->usersession->getInfos(UserSession::ID));
            }

//l'admin est celui qui à crée le groupe
            $this->member->since = mktime();
            $this->member->setRole($this->role->find(Role_entity::GROUP_ADMIN, 'nick_name'));
            $this->member->setUser($this->user);
            $this->member->setGroup($this->group);
            $this->member->save();
            if(!empty($this->group->id) && !empty($this->member->id) && !empty($this->file->id)) {
            $this->session->set_flashdata('msg_success', 'le groupe a bien été créé');
            redirect(find_uri('group', 'show').'/'.$this->group->id.'/'.$this->group->slug);
        } else {
            $this->session->set_flashdata('msg_error', 'Désolé, une erreure est survenue, reésaayez');
        }
        }
    }

    /**
     * permet de rejoindre un groupe
     * 1. si le groupe est public, l'utilisateur devient membre du groupe
     * 2. si le groupe ets privé, l'utilisateur devient membre du groupe 
     * si le mot de passe est juste
     * @todo check si l'utilisateur est édjà membre du groupe
     * @todo rejoindre depuis une invitation quand on est pas membre
     */
    public function logIn($id, $slug = '') {
        $this->_findGroup($id, 'log_in');
        $this->load->entity('group/group_member', 'group_member');
        $this->load->entity('group/invitation', 'invitation');
        $idUser = $this->usersession->getInfos(UserSession::ID);
        $this->invitation->where('id_user', $idUser)->find($this->group->id, 'id_group');
        if (!empty($this->group->password) and empty($this->invitation->id)) {
            $this->load->form('group/group_login');
            if ($this->form_validation->run('group/login')) {
                $this->_logInGroup($id, $slug);
            }
        } else {
            $this->_logInGroup($id, $slug);
        }
        redirect(find_uri('group', 'show') . "/$id/$slug");
    }

    /**
     * permet de quitter un groupe
     * @todo que faire si l'utilisateur est le dernier admin?
     */
    public function logOff($id, $slug = '') {
        $this->_findGroup($id, 'log_off');
        $userId = $this->usersession->getInfos(UserSession::ID);
        if ($this->user->id != $userId) {
            $this->user->find($userId);
        }

        $member = $this->group->group_member->where('id_user', $this->user->id)->get();
//l'utilisateur n'est plus membre du groupe
        $this->group->setGroup_member(array(end($member)));
        $this->group->group_member->destroy();
        $this->twiggy->set('group_name', $this->group->name);
    }

    /**
     *
     *
     */
    public function index() {
        $this->load->entity('minimess/minimess', 'minimess');
        $this->minimess = new minimess_entity();
        $this->minimess->text = "salut";
        $reply = new minimess_entity();
        $reply->text = "le 4 test";
        $reply->save();
        $this->minimess->setReply_as_minimess(array($reply));
        $this->minimess->save();
    }

    /**
     * affiche un groupe avec les personnes invitées
     */
    public function show($id, $slug = '') {
        $this->_findGroup($id, 'show');
        $this->load->entity('group/group_member', 'group_member');
        if ($this->user->id != $this->usersession->getInfos(UserSession::ID)) {
            $this->user->find($this->usersession->getInfos(UserSession::ID));
        }

        $this->load->model($this->daoFact->getDomainModel(), 'tree', TRUE);
        $this->load->entity('file/file', 'file');

        $this->twiggy->set('group', $this->group);

        //l'utilisateur fait-il part du groupe ?
        $member = $this->group_member->find($this->user->id, 'id_user');
        $isLogIn = !empty($member);
        $this->twiggy->set('isLogIn', $isLogIn);
        //on charge sson role
        if ($isLogIn) {
            $this->twiggy->set('role', $member);
            $this->_minimes($id, $slug);
        }
        //on affiche les autres membres si l'utilisateur à le droit de les voirs
        if ((!$isLogIn and empty($this->group->password)) or $isLogIn) {
            $members = $this->group->group_member_mto->exclude('id_user', $this->user->id)->get();
            foreach ($members as $m) {
                $m->user->get();
            }
        }
        //les personnes invitées
        $this->load->entity('group/invitation', 'invitation');
        $invitations = $this->invitation->where('id_group', $this->group->id)->get();
        //si la personne invitée est membre du site, charge son profil
        foreach ($invitations as $key => $invit) {
            if (empty($invit->mail_invited)) {
                $invit->user->get();
                $invitations[$key] = $invit;
            }
        }
        $this->twiggy->set('invitations', $invitations);



        //protège le groupe par mot de passe si l'utilisateur ne fait pas partie
        //du group et que le groupe est privé
        $this->load->entity('group/invitation', 'invitation');
        $this->invitation->where('id_user', $this->user->id)->find($this->group->id, 'id_group');
        $this->twiggy->set('isInvited', !empty($this->invitation->id));
        if (!empty($this->group->password) and !$isLogIn and empty($this->invitation->id)) {
            $this->load->form('group/group_login');
            if ($this->form_validation->run('group/login')) {
                $this->_logInGroup($id, $slug);
            }
        }
    }

    /**
     * affiche tous le groupes
     * seulement l'admin peut le voir
     */
    public function showAll() {
        $this->group = new Group_entity();
        $this->group->get();
    }

    /**
     * contrôle si le mot de passe est le bon pour rejoindre un groupe
     */
    public function _checkPassword($str) {
        if ($str != $this->group->password) {
            $this->form_validation->set_message('_checkPassword', 'Vous ne pouvez pas rejoindre le groupe. Le mot de passe ne corresspond pas');
            return FALSE;
        }
        return TRUE;
    }

    /**
     * enregistre un utilisateur dans le groupe donné
     * redirige vers la page show
     * @todo que faire quand l'utilisateur est invité mais pas sur collaide ?
     */
    private function _logInGroup($id, $slug) {
        $idUser = $this->usersession->getInfos(UserSession::ID);
        if ($this->user->id != $idUser) {
            $this->user->find($idUser);
        }
        //l'utilisateur fair déjà parti de ce groupe
        $res = $this->group->group_member->where('id_user', $idUser)->get();
        if (!empty($res)) {
            $this->usersession->flashsave('info_msg', 'Vous faites déjà parti de ce groupe');
//            redirect(find_uri('group', 'show') . "/$id/$slug");
        }
        $this->load->entity('user/role', 'role');
        $this->member = new group_member_entity();
        $this->member->id = 0;
        $this->member->since = time();
        $this->role->find(Role_entity::GROUP_WRITER, 'nick_name');
        $this->member->setRole($this->role);
        $this->member->setUser($this->user);
        $this->member->save();
        $this->group->addMember($this->member);
        $this->group->save();
        if (!empty($this->invitation->id)) {
            $this->invitation->destroy();
        }
//        redirect(find_uri('group', 'show') . "/$id/$slug");
    }

    /**
     * permet d'inviter une personne
     * @todo tester inviter 2x la même personne
     * @todo tester inviter qqn déjà membre du groupe
     */
    public function share($id, $slug) {
        $this->_findGroup($id, 'share');
//charge le profil de l'utilsateur connecté
        if ($this->user->id != $this->usersession->getInfos(UserSession::ID)) {
            $this->user->find($this->usersession->getInfos(UserSession::ID));
        }

//chargement des formulaires et des helpers
        $this->load->helper('email');
        $userInvited = new User_entity();
        $this->load->form('group/group_share');
        $this->load->entity('group/invitation', 'invitation');

        if ($this->form_validation->run('group/share')) {

//on reçoit un string contenant des addresse mail ou des pseudos
//séparé par virgules, on en fait un tableau
            $share = $this->input->post('share');
            $emailsList = explode(', ', $share);
//si il y a une seule addresse mail ou un seul pseudo pas au point
            if (valid_email($share) or empty($emailsList)) {
                $emailsList = array($this->input->post('share'));
            }
            foreach ($emailsList as $name) {
                $userInvited->id = 0;
                $this->invitation->id = 0;
//on cherhche un utilisateur sur collaide dont son mail ou son pseudo corresspond à $name
                $userInvited->where('username', $name, 'OR')->find($name, 'email');

                // test si la personne à déjà été invitée
                if ($userInvited->id == 0) {
                    //n'est pas sur collaide
                    if (!empty($this->invitation->where('id_group', $this->group->id)->find($name, 'mail_invited')->id)) {
                        continue;
                    } elseif (!valid_email($name)) {
                        vv($name, 'le pseudo existe pas');
                        //le pseudo n'existe pas
                        continue;
                }
                } else {//est sur collaide
                    if (!empty($this->invitation->where('id_user', $userInvited->id)->find($this->group->id, 'id_group')->id)) {
                        continue;
                    }
                }
//on crée une nouvelle invitation
                
                $this->invitation->date_of_invit = mktime();
                $this->invitation->id_invinting = $this->user->id;
                $this->invitation->setGroup($this->group);
                $this->invitation->text = $this->input->post('text');

                if ($userInvited->id == 0 and valid_email($name)) {
//send mail, la personne invité n'est pas sur collaide
                    $this->load->library('email');

                    $this->email->from('team@collaide.com', 'Le team de collaide');
                    $this->email->to($name);

                    $this->email->subject('Invitation à rejoindre un groupe');
                    $this->email->message('Vous avez été invité à rejoindre un groupe' . $this->input->post('text'));

                    $this->email->send();
                    log_message('info', $this->email->print_debugger());
                    $this->invitation->mail_invited = $name;

//la personne est sur collaide
                } else {
                    $this->invitation->setUser($userInvited);
                }
                $this->invitation->save();
//                redirect(find_uri('group', 'show') . "/$id/$slug");
            }
        }
    }

    public function _checkPrivateGroup($str) {
        if ($this->input->post('is_private')) {
            if (!($this->form_validation->required($str) and $this->form_validation->min_length($str, 3) and $this->form_validation->max_length($str, 255))) {
                $this->form_validation->set_message('_checkPrivateGroup', "Le mot de passe est requis");
                return FALSE;
            }
            return TRUE;
        }
        return TRUE;
    }

    public function _checkSameName($str) {
        $group = new Group_entity();
        $group->find($str, 'name');
        if (!empty($group->id)) {
            $this->form_validation->set_message('_checkSameName', "Le nom '$str' existe déjà");
            return FALSE;
        }
        return TRUE;
    }

    /**
     * affiche les groupes de l'utilisateur connecté
     *
     */
    public function userGroup() {
        $this->load->entity('group/group_member', 'group_member');
        $groups = array();
        $idUser = $this->usersession->getInfos(UserSession::ID);
        if ($this->user->id != $idUser) {
            $this->user->find($idUser);
        }
        foreach ($this->user->group_member_mto->get() as $groupMember) {
            array_push($groups, $groupMember->group->get());
        }
        $this->twiggy->set('groups', $groups);
    }

    private function _findGroup($id, $method) {
        if ((int) $id <= 0) {
            redirect(find_uri('group', 'not_found'));
        }
        if ($this->group->id != $id) {
            $this->group->find($id);
            if ((int) $this->group->id == 0 or empty($this->group->slug)) {
                redirect(find_uri('group', 'not_found'));
            }
        }
        noDuplicate(find_url('group', $method) . "/$id/" . $this->group->slug);
    }

    /**
     * si un groupe n'est pas trouvé
     */
    public function notFound() {
        
    }

    private function _minimes($id, $slug) {
        $this->load->form('minimess/minimess_speak');
        $this->load->entity('minimess/minimess', 'minimess');

        $this->form->setName('minimess');
        $minimess = $this->group->minimess_mto->order('publish_at', TRUE)->filter('id_reply', 0)->get();
        foreach ($minimess as $key => $minimes) {
//            $reply_minimess = $minimes->filter('id_reply', $minimes->id)->get();
            $minimes->reply_as_minimess_mto->get();
            $minimess[$key] = $minimes;
        }
        $this->twiggy->set('minimess', $minimess);
        if ($this->input->post('minimess')) {
            if ($this->form_validation->run('minimess/speak')) {
                $this->minimess->text = $this->input->post('speak');
//                $this->minimes->create_at = mktime();
                $this->minimess->setUser($this->user);
                $this->minimess->setGroup($this->group);
                $this->minimess->save();
                redirect(find_uri('group', 'show') . "/$id/$slug");
            }
        } if ($this->input->post('submit')) {
            if ($this->formBuilder->valid) {
                $idPost = $this->input->post('id');
                $reply = new minimess_entity();
                $reply->text = $this->input->post('text');
                $reply->setUser($this->user);
                $reply->setGroup($this->group);
                $reply->setReply_as_minimess($minimess[$idPost]);
                $reply->save();
                redirect(find_uri('group', 'show') . "/$id/$slug");
            }
        }
    }

}

