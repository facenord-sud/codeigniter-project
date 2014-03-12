<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * La classe personalisée de CI_Controller. Faite pour faciliter l'intégration de twiggy.
 *
 * @author leo
 */
require_once APPPATH . 'models/entity.php';

class MY_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->spark('twiggy/0.8.5');
        // Mettre un titre de base sur les pages
        $this->twiggy->title('Collaide');
        $this->twiggy->register_function('find_uri');
        // Ajouté par Yves, pour traduire les pages
        $this->twiggy->register_function('translate_page');
        $this->twiggy->set('msg_info', $this->session->flashdata('msg_info'));
        $this->twiggy->set('false', 0);
        $this->twiggy->set('true', 1);

        $this->lang->loads('layout/layout');
        $this->lang->loads('layout/form/form_layout');
        $factory = Factory::getMysql();
        $this->load->model($factory['dao'], 'daoFact');
        $this->load->model($factory['entity'], 'entityFact');
        $this->load->model($factory['form'], 'formFact');

        // Activer le profiler (MOD DEV)
        $this->output->enable_profiler(TRUE);

        $this->load->library('form', NULL, 'formBuilder');

        $this->load->entity('language/language', 'lang_entity');
        require_once APPPATH . 'models/AbstractFormular.php';
        $this->load->form('user/user_connect', 'connect');

        $this->_remeberMe();
        $this->twiggy->set('breadcrumb', $this->breadcrumb);
        $this->twiggy->set('user', $this->usersession->getAllUserInfos());
        $this->_setMessageToTwig();
        $this->twiggy->set('usersession', $this->usersession);
        $this->twiggy->set('languages', $this->lang_entity->what(array('id', 'lang', 'language'))->get());
//        $test = $this->lang_entity;
//        print_r($test);
        $this->twiggy->set('form_l', $this->formBuilder);
    }

    private function _setMessageToTwig() {
        $msg = array('info' => $this->session->flashdata('msg_info'),
            'error' => $this->session->flashdata('msg_error'),
            'success' => $this->session->flashdata('msg_success'));
        $this->twiggy->set('msg', $msg);
    }

    /**
     * 
     */
    private function _remeberMe() {
        $this->load->helper('cookie');
        $this->load->entity('user/user', 'user');
        if ($this->usersession->isConnected() == TRUE) {

            $this->dmo->setLoadRelation(TRUE);
            $this->dmo->loadObject($this->user, $this->usersession->getInfos(UserSession::ID));
//            $this->user->find($this->usersession->getInfos(UserSession::ID));
//            $this->user->role->get();
            $this->dmo->flush();
            if ($this->user->id == 0) {
                $this->usersession->logOut();
                return;
            }
            $this->checkUserActivation($this->user);
            $this->usersession->update($this->user);
            return;
        }

        $cookie = get_cookie('remeber_me');
        if (!$cookie) {
            return;
        }
        $this->dmo->setLoadRelation(TRUE);
        $this->dmo->loadObject($this->user, $cookie);
        $this->dmo->flush();
        if ($this->user->id != 0) {
            $this->checkUserActivation($this->user);
            $this->usersession->register($this->user);
        }
    }

    /**
     * contrôle si l'utilisateur à le droit de se connecter
     * ie: son compte n'est ni bloqué, ni supprimé, ni temporarire
     * 
     * @param User_entity $user l'entité user
     */
    protected function checkUserActivation($user) {
        $this->lang->loads('user/connect');
        if ($user->banned) {
            show_error($this->lang->line('destroyed_msg'), 500, $this->lang->line('destroyed_title'));
        }
        if ($user->locked) {
            show_error($this->lang->line('locked_msg'), 500, $this->lang->line('locked_title'));
        }
        if (!$user->enabled) {
//            echo $user->date_creation;
//            
//            die();
            $twoWeeks = 60 * 60 * 24 * 7 * 2;
            $diffTime = 23;
            if ($diffTime <= $twoWeeks) {
                $this->session->set_flashdata('msg_info', $this->lang->line('unactivated_title'));
            } else {
                show_error($this->lang->line('unactivated_msg'), 500, $this->lang->line('unactivated_title'));
            }
        }
    }

}

?>
