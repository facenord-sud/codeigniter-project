<?php

/**
 * Description of role
 *
 * @author leo
 */

/**
 * Gére les rôles
 * @property Role_entity $role 
 */
class Role extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->orm();
        $this->load->model('user/entity/role_entity', 'role');
    }

    public function index() {
        redirect(find_uri('role', 'show'));
    }

    public function show() {
        $this->load->form('user/role_new', 'form');
        $this->load->library('pagination');
        
        $this->twiggy->title("Tous les rôles");

        $this->query->setTableName('role')->fields(array('id', 'nick_name', 'description', 'name'));

        $idLang = $this->input->post('print_language');
        $printLang = $this->lang->getTagLang();
        if (empty($idLang)) {
            $idLang = $this->query->getLangId($printLang);
        }

        $this->query->setLanguage($idLang)->setNoOtherLanguage(TRUE)->setFetchAll(TRUE);
        $this->twiggy->set('roles', $this->query->select());

        $languages = array();
        foreach ($this->query->setTableName('language')->fields(array('id, language'))->select() as $language) {
            $languages[$language['id']] = $language['language'];
        }
        $this->twiggy->set('languages', $languages);
        $this->twiggy->set('select', $idLang);
        $this->twiggy->set('pagination', $this->pagination);

        if ($this->form_validation->run('role/register') == TRUE) {
            $this->_register();
        }
    }

    /**
     * le champ est rempis par la valeure de nick_name
     * 
     * @param int $id
     * @param int $lang
     * 
     */
    public function modify($id, $lang) {
        $this->role->setLanguage($lang);
        $this->load->form('user/role_modify', 'form');
        $this->dmo->loadObject($this->role, $id);
        $this->twiggy->set('role', $this->role);
        $this->form->setRole($this->role);
        if ($this->form_validation->run('role/register')) {
            $this->role->nick_name = $this->input->post('nick_name');
            $this->dmo->saveObject($this->role);
            redirect(find_uri('role', 'show'));
        }
    }

    /**
     * 
     * @param type $id
     * @todo prq ça marche pas comme la méthode modify??? les champs restes vides. putain!!
     */
    public function traduce($id, $idLang=0) {
        
        if(empty($idLang)) {
            redirect(find_uri('role', 'traduce').'/'.$id.'/'.$this->input->post('print_language'));
        }

        $this->load->form('user/role_traduce', 'form');
        $this->role->setLanguage($idLang);
        $this->dmo->loadObject($this->role, $id);
        $this->twiggy->set('role', $this->role);

        $this->twiggy->set('lang', $this->query->getIdiom($idLang));
        $this->form->setRole($this->role);
        if ($this->form_validation->run('role/traduce')) {
            $this->role->description = $this->input->post('description');
            $this->role->name = $this->input->post('name');
            $this->dmo->saveObject($this->role);
            redirect(find_uri('role', 'show'));
        }
    }

    public function delete($id) {
        $this->dmo->deleteObject($this->role, $id);
        redirect(find_uri('role', 'show'));
    }

    private function _register() {
        $this->role->nick_name = $this->input->post('nick_name');
        $this->dmo->saveObject($this->role);
        redirect(find_uri('role', 'show'));
    }

    public function _checkSameRole($str) {
        $this->query->flushQuery();
        $this->query->setTableName('role')->fields('id');
        $this->query->where('nick_name', $str);
        if ($this->query->select() != FALSE) {
            $this->form_validation->set_message('_checkSameRole', sprintf($this->lang->line('same_role'), $str));
            return FALSE;
        }
        return TRUE;
    }
    
    public function testRole() {
        $this->load->library('unit_test');
        $nbRole = $this->query->count('role');
        
        $this->unit->run(count($this->_testLoadRole(FALSE)), $nbRole, 'Test avec post à FALSE');
        $languages = $this->query->setTableName('language')->select();
        $this->unit->run(count($languages), $this->query->count('language'), 'Toutes les langues sont chargées');
        foreach ($languages as $lang) {
            $this->unit->run(count($this->_testLoadRole($lang['lang'])), $nbRole, 'Test avec la langue='.$lang['lang']);
            $this->unit->run(array('id'=>$lang['id'], 'lang' => $lang['lang'], 'language'=>$lang['language']), $lang, 'Test si les langues sont bien formatées');
        }
        
        $this->role->nick_name = 'test';
        $this->dmo->saveObject($this->role);
        $this->unit->run($this->query->count('role'), $nbRole+1, 'Test si un nouveau rôle est créée');
        
        $id = $this->role->id;
        $this->role = new Role_entity();
        $this->role->setLanguage($this->query->getLangId($this->lang->getTagLang()));
        $this->unit->run($this->dmo->loadObject($this->role, $id), TRUE, 'Test si on peut charger un role pour la traduction');
        
        $this->role->nick_name = 'test';
        $this->role->description='édkfchdsaF ';
        $this->unit->run($this->dmo->saveObject($this->role), TRUE, 'Test si on peut sauver un rôle traduit');
        
        $roleTest = new Role_entity();
        $this->dmo->loadObject($roleTest, $this->role->id);
        $this->unit->run($roleTest, $this->role, 'Test si le role traduit à été sauvegardé correctement');
        
        $this->dmo->deleteObject($this->role, $id);
        $this->unit->run($this->query->count('role'), $nbRole, 'Test si le nouveau rôle est supprimé');
        
        $this->twiggy->set('report', $this->unit->report());
    }
    
    private function _testLoadRole($idLang) {
        $printLang = $this->lang->getTagLang();
        if (empty($idLang)) {
            $idLang = $this->query->getLangId($printLang);
        }
        $this->query->setTableName('role')->fields(array('id', 'nick_name', 'description', 'name'));
        $this->query->setLanguage($idLang)->setNoOtherLanguage(TRUE)->setFetchAll(TRUE);
        return $this->query->select();
    }

}

?>
