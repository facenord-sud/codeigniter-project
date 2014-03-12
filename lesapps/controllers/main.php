<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Main extends MY_Controller {

    public function __construct() {
        parent::__construct();

        //$this->lang->loads('main/template/template');
    }

    /**
     * Page d'index pour le mainController
     *
     * 
     */
    public function index() {
        noDuplicate(site_url().$this->lang->getTagLang());
//         $this->load->driver('cache', array('adapter' => 'apc', 'backup' => 'file'));


//      echo 'Saving to the cache!<br />';
//      $foo = array('param'=>array('iso'=>'dt'), 'result'=>array('id'=>1));

//      // Save into the cache for 5 minutes
//      $this->cache->save('SELECT v2_role.id, v2_role.nick_name, v2_role_lang.name, v2_role_lang.description FROM v2_role LEFT JOIN v2_role_user ON v2_role_user.id_role = v2_role.id LEFT JOIN v2_user ON v2_user.id = v2_role_user.id_user LEFT JOIN v2_role_lang ON v2_role.id = v2_role_lang.reference LEFT JOIN v2_language ON v2_role_lang.language = v2_language.id  WHERE v2_user.id = :user_id AND v2_language.id = :v2_language_id', $foo, 300);

// debug($this->cache->get('SELECT v2_role.id, v2_role.nick_name, v2_role_lang.name, v2_role_lang.description FROM v2_role LEFT JOIN v2_role_user ON v2_role_user.id_role = v2_role.id LEFT JOIN v2_user ON v2_user.id = v2_role_user.id_user LEFT JOIN v2_role_lang ON v2_role.id = v2_role_lang.reference LEFT JOIN v2_language ON v2_role_lang.language = v2_language.id  WHERE v2_user.id = :user_id AND v2_language.id = :v2_language_id'));
        //echo htmlspecialchars($this->breadcrumb->getBreadcrumb());
    }

    public function contact() {
        echo $_SERVER['REQUEST_URI'];
        $this->lang->load('main/contact');

        // On défini le titre
        $this->twiggy->title()->prepend($this->lang->line('title'));

        $data = array();

        // On passe les textes dans une variable
        $data['h1_title'] = $this->lang->line('h1_title');
        $data['content'] = $this->lang->line('content');

        $this->twiggy->set($data, NULL)->display('contact');
    }

}
