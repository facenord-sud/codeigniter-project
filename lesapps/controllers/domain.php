<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Ce controller permet de gérer l'entité domain. 
 * C'est un controller d'administration.
 *
 * @author Yves
 */
class Domain extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->twiggy->title()->prepend($this->lang->line('title'));
        $this->load->orm();     
    }

    /**
     * Page d'index pour le domainController
     * @todo Doit afficher tous les domaines dans l'ordre avec leur level
     * 
     */
    public function index($limit = 0) {
        if ($limit == 0) { // Rediriger domain/index, sur domain/
            noDuplicate(site_url(find_uri('domain', 'index')));
        }
        /*
         * Charger les librairies, helpers, langue, modèles
         */
        $tagLang = $this->lang->getTagLang();
        $this->load->library('table');

        $this->load->model($this->daoFact->getDomainModel(), 'model', TRUE);
        $this->load->helper('print');
        $this->load->helper('form');
        $this->load->library('pagination');
        $idLang = $this->query->getLangId($tagLang);
        $this->tree->getQueryBuilder()->setLanguage(1);
        //print_r($this->tree->getTree('domain'));
        $domains = $this->model->showTree('domain', array($limit), $idLang);

        $config['base_url'] = site_url($tagLang . '/domain');
        $config['total_rows'] = $this->query->count('domain');
        $this->pagination->initialize($config);
        /*
         * Twiggy
         */
        $this->twiggy->register_function('tableFetchAll');
        $this->twiggy->set('domains', $domains);
        
        $fields = array('id' => $this->lang->line('table_id'), 'name' => $this->lang->line('table_name'));
        $this->twiggy->set('fields', $fields);
        $this->twiggy->set('pagination', $this->pagination);
        $this->twiggy->title()->prepend($this->lang->line('title'));
    }

    /**
     * Page pour voir un domain
     * @todo doit afficher le domain sélectionné 
     * 
     */
    public function show($id) {
        echo 'show ' . $id;
    }

    /**
     * Page pour modifier un domain
     * @todo modifier un domain
     * 
     */
    public function edit($id=0) {
        /*
         * Charger les librairies, helpers, langue, modèles
         */
        $tagLang = $this->lang->getTagLang();
        $this->load->model($this->daoFact->getDomainModel(), 'dao', TRUE);
//        $this->load->model($this->entityFact->getDomainEntity(), 'domain');
//        $this->load->model($this->formFact->getDomainEditForm(), 'form');
        $this->load->entity('domain/domain', 'domain');
        $this->load->form('domain/domain_edit', 'form');

        // On récup l'id de la lang
        $idLang = $this->query->getLangId($tagLang);
        $this->domain->setLanguage($idLang);

        // On test l'id
        $this->dmo->loadObject($this->domain, $id);

        if (!is_object($this->domain)) {
            echo 'Ce domaine n\'existe pas.';
        }

        /*
         * Twiggy
         */
        $this->twiggy->set('form', $this->form);
        $this->twiggy->set('domain', $this->domain);
        $this->twiggy->title()->prepend($this->lang->line('title'));
        $this->twiggy->meta('description', 'salut');

        if ($this->form_validation->run('domain/edit') == TRUE) {
            // ON ajoute la feuille
            $this->domain->name = $this->input->post('name');
            $this->domain->description = $this->input->post('description');
            $this->dmo->saveObject($this->domain);
            redirect(find_uri('domain', 'index'));
        } 
    }

    /**
     * Page pour créer un domain
     * 
     */
    public function create() {
        /*
         * Charger les librairies, helpers, langue, modèles
         */

        $tagLang = $this->lang->getTagLang();
        $this->load->entity('domain/domain', 'domain');
        $this->load->form('domain/domain_new', 'form');

        // On récup l'id de la lang
        $idLang = $this->query->getLangId($tagLang);

        /*
         * Twiggy
         */


        $this->twiggy->title()->prepend($this->lang->line('title'));
        $this->twiggy->meta('description', 'salut');

        if ($this->form_validation->run() == TRUE) {
            // ON ajoute la feuille
            $this->domain->name = $this->input->post('name');
            $this->domain->description = $this->input->post('description');
            $this->domain->setLanguage($idLang);
            $this->tree->add($this->domain, $this->input->post('domain'), $this->input->post('mode'));
            //redirect(find_uri('domain', 'index'));
        }
    }

    /**
     * Page pour supprimer un domain
     * @todo demander confirmation
     * 
     */
    public function delete() {

        $this->load->form('domain/domain_delete', 'form');
        $this->load->entity('domain/domain', 'domain');

        $this->twiggy->title()->prepend($this->lang->line('title'));
        
        if($this->form_validation->run('domain/delete')) {
            $this->domain->find($this->input->post('domain'));
            if ($this->input->post('mode') === '1') {
                echo 'rec';
                $this->tree->removeSubTree($this->domain);
            } else {
                echo 'nop';
                $this->tree->removeLeaf($this->domain);
            }
            
            //redirect(find_uri('domain', 'index'));
        }
    }

}