<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * @property Document_entity $doc
 */
class Document extends MY_Controller {

    // si le file est obligatoir ou pas dans le formulaire
    protected $form_checkfile = TRUE;

    public function __construct() {
        parent::__construct();
        $this->load->entity('document/document', 'doc');
    }

    /**
     * Page d'index pour le documentController
     * 
     */
    public function index($limit = 0) {
//        if ($limit == 0) { // Rediriger domain/index, sur domain/
//            noDuplicate(site_url(find_uri('document', 'index', array($limit, $sort, $order))));
//        }
        $sort = $this->input->get('sort');
        $order = $this->input->get('order');
        if ($order == 'desc')
            $order = FALSE;
        elseif ($order == 'asc')
            $order = TRUE;
        else
            $order = FALSE;

        $sort = ($sort == 'title' || $sort == 'description') ? $sort : 'id';

        $this->load->library('table');
        $this->load->library('pagination');

        // ATTENTION $sort DOIT ETRE SECURISEE !!!
        // 
        // Choix de la requete
        $this->doc->limit($limit)->order($sort, $order);
        // CREATION DU TABLEAU
        $i = 0;
        $data[$i++] = array('icon_' => $this->lang->line('table_icon'), 'title' => $this->lang->line('table_title'), 'description' => $this->lang->line('table_description'), 'type_' => $this->lang->line('table_type'), 'domain_' => $this->lang->line('table_domain'));
        foreach ($this->doc->get() AS $object) {
            $echo_domains = '';
            $domains = $object->domain->get();
            foreach ($domains AS $domain) {
                $echo_domains .= anchor('document', 'domain', $domain->name, $domain->id) . ', ';
            }
            $echo_domains = rtrim($echo_domains, ', ');
            $data[$i++] = array('icon', $object->title, $object->description, anchor('document', 'type', $object->document_type->get()->name, $object->document_type->get()->id), $echo_domains);
        }
        $this->table->set_caption($this->lang->line('table_caption'));
        $this->twiggy->set('table', $this->table->generate($data));
    }

    /**
     * Page d'index pour le documentController
     * @todo Doit afficher tous les documents dans l'ordre avec leur level
     * 
     */
    public function all() {
        //$this->lang->loads('document/all');
    }

    /**
     * Page pour voir un domain
     * @todo doit afficher le doc sélectionné 
     * 
     */
    public function show($id) {
        echo 'show ' . $id;
    }

    /**
     * Affiche les document du domain en paramètre
     * 
     */
    public function domain($id) {
        echo 'show ' . $id;
    }

    /**
     * Page pour modifier un domain
     * @todo modifier un domain
     * 
     */
    public function edit($id = 0) {
        $this->doc->find($id);
        // j'ai reajouté ça pour que quand l'id est pas trouvée,
        // on ajoute un document. Enlève le si tu pensais faire autrement.
        if (empty($this->doc->id)) {
            redirect(find_uri('document', 'add'));
        }

        $this->docForm(FALSE);
        $data = $this->form->makeForm('document');
        $this->twiggy->set($data);
        $this->_saveDocument();
    }

    /**
     * Page pour supprimer un domain
     * @todo demander confirmation
     * 
     */
    public function delete() {
        
    }

    /**
     *
     *
     */
    public function add() {
        // TODO vérifier que c'est bien deux domaines différents d'enregistrés. la fonction entity, sort deux fois les 
        // mêmes domaines, il me semble. A vérifier. CF ligne 389 de orm/dmo.php
        // le bug pour l'enregistrement de deux domaines pour un documents est trouvé mais pas corrigé. CF ligne 380 de orm/dmo.php
        $this->docForm();
        $data = $this->form->makeForm('document');
        $this->twiggy->set($data);
        $this->_saveDocument();
    }

    /*
     * Permet d'avoir toutes les infos pour le formulair d'ajout et suppression de doc
     */

    public function docForm($required_file = TRUE) {
        $this->load->entity('domain/domain', 'domain');
        $this->load->entity('document/document_type', 'document_type');
        $this->load->entity('document/document_author', 'document_author');
        $this->load->entity('study/study_level', 'study_level');
        $this->load->entity('language/language', 'language');
        $this->load->library('form');
        $this->load->helper('recaptcha');
        $this->load->library('calendar');

        $this->load->form('document/document');
        $this->load->model($this->daoFact->getDomainModel(), 'tree_model', TRUE);
        $this->form->setRequired_file($required_file);
        $this->setForm_checkfile($required_file);
    }

    public function _saveDocument() {
//        if ($this->form_validation->run('document/add')) {
        if ($this->formBuilder->valid) {
            $this->doc->title = $this->input->post('title');
            $this->doc->description = $this->input->post('description');
            $this->doc->page = $this->input->post('page');
            $this->doc->date = time();

            if ($this->user->id != $this->usersession->getInfos(UserSession::ID)) {
                $this->user->find($this->usersession->getInfos(UserSession::ID));
            }
            $this->doc->setUser($this->user);

            // On ajoute le(s) domaine(s)
            foreach ($this->input->post('domain') as $value) {
                $this->doc->addDomain($this->domain->find($value));
            }
            //coriigé. domain type n'existe pas
            $this->doc->domain->save();

            // On ajoute les liaison
            $this->doc->setDocument_type($this->document_type->find($this->input->post('document_type')[0]));
            $this->doc->setLanguage($this->language->find($this->input->post('language')[0]));

            // Optional
            $this->doc->date_realisation = mktime(0, 0, 0, $this->input->post('date_realisation_month')[0], 1, $this->input->post('date_realisation_year')[0]);
            $this->doc->setStudy_level($this->study_level->find($this->input->post('study_level')[0]));

            if ($this->upload->getFile_uploaded()) {
                if ($this->upload->do_upload()) {
                    // On enregistre le file
                    $this->load->entity('file/file', 'file');
                    $infos = $this->upload->data();
                    $this->file->name = $infos['client_name'];
                    $this->file->slug = $infos['file_name'];
                    $this->file->folder = FILE_FOLDER;
                    $this->file->full_path = $infos['full_path'];
                    $this->file->path = $infos['file_path'];
                    $this->file->date = time();
                    $this->file->size = $infos['file_size'];
                    $this->file->type = $infos['file_type'];
                    $this->file->extension = ltrim($infos['file_ext'], '.');
                    $this->file->is_image = $infos['is_image'];
                    $this->file->image_width = $infos['image_width'];
                    $this->file->image_height = $infos['image_height'];
                    $this->file->image_size_str = $infos['image_size_str'];
                    $this->tree_model->addLeaf($this->file);
                    $this->doc->addFile($this->file);
                    //File_entity est hérité d'abstract tree. Il faut d'abord l'enregistrer
                    //avec les méthodes de tree_model. CF la méthode _saveGroup de group.php
                    //Si ce n'est pas fait, il sera bien enregistré dans la bdd, mais pas dans l'arbre
                } else {
                    // Ne devrait pas arriver
                    echo 'erreur lors de l\'enrgistrement du document';
                }
            }
            // On sauve le tout
            $this->doc->save();
        }
    }

    public function _isDomain() {
        return $this->driver->isField('domain', $this->input->post('domain'));
    }

    /**
     * Permet de check si il y a une erreur sur le file uploadé
     * 
     * @param type $field
     * @return type
     */
    public function _checkFile() {
        $config['upload_path'] = FCPATH . FILE_FOLDER;
        $config['allowed_types'] = 'doc|pdf|odt|docx';
        $config['max_size'] = '2048'; // 2MB
        $this->load->library('upload', $config);
        $return = $this->upload->check_file('file', $this->form_checkfile);
        $this->form_validation->set_message('_checkFile', $this->upload->display_errors('', ''));

        return $return;
    }

    public function getForm_checkfile() {
        return $this->form_checkfile;
    }

    public function setForm_checkfile($form_checkfile) {
        $this->form_checkfile = (is_bool($form_checkfile)) ? $form_checkfile : TRUE;
    }

}

