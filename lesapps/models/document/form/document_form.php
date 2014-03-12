<?php

/**
 * Description of document_form
 *
 * @author Yves
 */
class document_form extends CI_Model {

    // Détermine si on a besoin d'envoyer le file ou pas
    private $required_file = TRUE;

    public function __construct() {
        parent::__construct();
        $this->lang->loads('document/form/form_doc');
    }

    public function makeForm($validation = '', $required = TRUE, $optional = TRUE) {

        $this->formBuilder
                ->open();

        if ($required)
            $this->requiredForm();
        if ($optional)
            $this->optionalForm();

        $this->formBuilder
                ->fieldset_close()
                ->submit();

        $data['form'] = $this->formBuilder->get($validation); // this returns the validated form as a string
        $data['errors'] = $this->formBuilder->errors;  // this returns validation errors as a string
        return $data;
    }

    public function requiredForm() {
        // On rend les array utilisable pour les dropDown

        $this->doc->domain->get();

        $default_domain = '';
        foreach ($this->doc->getDomain() AS $object) {
            $default_domain .= $object->id.',';
        }
        $default_domain = rtrim($default_domain, ',');

        $this->query->setLanguage($this->query->getLangId($this->lang->getTagLang()));
        $domains = $this->tree->getTree('domain');
        
        $document_type = $this->formBuilder->makeDropDown($this->document_type->get());
        $domain = $this->formBuilder->makeDropDown($domains, 'prefix_name');
        $language = $this->formBuilder->makeDropDown($this->language->get(), 'language');

        $this->formBuilder
                ->fieldset($this->lang->line('form_fieldset_required'))
                ->text('title', TRUE, 'required', $this->doc->title)
                ->textarea('description', TRUE, 'required', $this->doc->description)
                ->number('page', TRUE, 'required', $this->doc->page, 'min=1')
                ->select('document_type', $document_type, TRUE, $this->doc->document_type->get()->id, 'required')
                ->select('domain', $domain, TRUE, $default_domain, 'required', "multiple=multiple")
                ->select('language', $language, TRUE, $this->doc->language->get()->id, 'required');
        if ($this->required_file)
            $this->formBuilder->upload('file', TRUE, TRUE);
    }

    public function optionalForm() {
        // $year est un tableau d'année
        $year = array();
        for ($y = date('Y'); $y >= 1950; $y--) {
            $year[$y] = $y;
        }
        $month = array();
        for ($i = 1; $i <= 12; $i++) {
            $month[$i] = $this->calendar->get_month_name(sprintf('%02d', $i));
        }
        $study_level = $this->formBuilder->makeDropDown($this->study_level->get());

        $this->formBuilder->fieldset($this->lang->line('form_fieldset_optional'));

        $default_month = date('m');
        $default_year = date('Y');
        // Si il y a une date de réalisation entrée, on va changer les valeurs par défault
        if ($this->doc->date_realisation) {
            $default_month = date('m', $this->doc->date_realisation);
            $default_year = date('Y', $this->doc->date_realisation);
        }

        if (!$this->required_file)
            $this->formBuilder->upload('file');
        $this->formBuilder
                ->select('date_realisation_month', $month, $this->lang->line('form_date_realisation'), $default_month)
                ->select('date_realisation_year', $year, FALSE, $default_year)
                ->select('study_level', $study_level)
//                ->recaptcha()
        // TODO : Ajouter document_author
        ;
    }

    public function getRequired_file() {
        return $this->required_file;
    }

    public function setRequired_file($required_file) {
        $this->required_file = $required_file;
    }
}