<?php

/*
 * Ce fichier permet de mettre les règle de validation des formulaires
 * 
 */

$config = array(
    /*
     * Création d'un nouveau domaine
     */
    'domain/create' => array(
        array(
            'field' => 'name',
            'label' => 'lang:form_name',
            'rules' => 'required|min_length[3]|max_length[255]'
        ),
        array(
            'field' => 'description',
            'label' => 'lang:form_description',
            'rules' => ''
        ),
        array(
            'field' => 'domain',
            'label' => 'lang:form_domain',
            'rules' => 'is_field[domain]'
        ),
        array(
            'field' => 'mode',
            'label' => 'lang:form_mode',
            'rules' => 'required|enum[ES,YS,BB,LB,F]'
        )
    ),
    /*
     * Edition d'un domaine
     */
    'domain/edit' => array(
        array(
            'field' => 'name',
            'label' => 'lang:form_name',
            'rules' => 'required|min_length[3]|max_length[255]'
        ),
        array(
            'field' => 'description',
            'label' => 'lang:form_description',
            'rules' => ''
        ),
    ),
    /*
     * supression d'un domaine
     */
    'domain/delete' => array(
        array(
            'field' => 'domain',
            'label' => 'lang:form_domain',
            'rules' => 'required|is_field[domain]'
        ),
        array(
            'field' => 'mode',
            'label' => 'lang:form_mode',
            'rules' => 'required|enum[0,1]'
        )
    ),
    /*
     * Création d'un nouveau membre
     */
    'user/register' => array(
        array(
            'field' => 'username',
            'label' => 'lang:pseudo',
            'rules' => 'required|min_length[3]|max_length[255]|callback__checkUserName'
        ),
        array(
            'field' => 'email',
            'label' => 'lang:email',
            'rules' => 'required|valid_email|max_length[255]|callback__checkEmail'
        ),
        array(
            'field' => 'password',
            'label' => 'lang:password',
            'rules' => 'required|min_length[3]|max_length[255]'
        ),
        array(
            'field' => 'password2',
            'label' => 'lang:password2',
            'rules' => 'required|min_length[3]|max_length[255]|callback__checkSamePassword'
        )
    ),
    'user/connect' => array(
        array(
            'field' => 'username',
            'label' => 'lang:pseudo',
            'rules' => 'required|min_length[3]'
        ),
        array(
            'field' => 'password',
            'label' => 'lang:password',
            'rules' => 'required|min_length[3]'
        ),
    ),
    'user/modify' => array(
        array(
            'field' => 'points',
            'label' => 'lang:points',
            'rules' => 'required|is_natural_no_zero'
        ),
    ),
    'user/delete' => array(
        array(
            'field' => 'username',
            'label' => 'lang:username',
            'rules' => 'required|callback__checkDelete'
        ),
    ),
    'user/contact' => array(
        array(
            'field' => 'f_name',
            'label' => 'lang:f_name',
            'rules' => 'required|max_length[255]'
        ),
        array(
            'field' => 'l_name',
            'label' => 'lang:l_name',
            'rules' => 'required|max_length[255]'
        ),
    ),
    'user/editMail' => array(
        array(
            'field' => 'email',
            'label' => 'lang:email',
            'rules' => 'required|valid_email|max_length[255]|callback__checkEmail'
        ),
    ),
    'user/editPassword' => array(
        array(
            'field' => 'old_password',
            'label' => 'lang:old_password',
            'rules' => 'required|callback__checkOldPassword'
        ),
        array(
            'field' => 'password',
            'label' => 'lang:password',
            'rules' => 'required|min_length[5]|max_length[255]'
        ),
        array(
            'field' => 'password2',
            'label' => 'lang:password2',
            'rules' => 'required|min_length[5]|max_length[255]|callback__checkSamePassword'
        )
    ),
    'user/address' => array(
        array(
            'field' => 'name',
            'label' => 'lang:name',
            'rules' => 'required'
        ),
        array(
            'field' => 'country',
            'label' => 'lang:country',
            'rules' => 'required'
        ),
    ),
    'role/register' => array(
        array(
            'field' => 'nick_name',
            'label' => 'lang:nick_name',
            'rules' => 'required|min_length[3]|max_length[255]|callback__checkSameRole'
        ),
    ),
    'role/traduce' => array(
        array(
            'field' => 'name',
            'label' => 'lang:name',
            'rules' => 'required|min_length[3]|max_length[255]'
        ),
        array(
            'field' => 'description',
            'label' => 'lang:description',
            'rules' => 'required|min_length[10]'
        ),
    ),
    /*
     * Création d'un nouveau document / Edition d'un document
     */
    'document' => array(
        array(
            'field' => 'title',
            'label' => 'lang:form_title',
            'rules' => 'required|min_length[3]|max_length[255]'
        ),
        array(
            'field' => 'description',
            'label' => 'lang:form_description',
            'rules' => 'required|min_length[30]'
        ),
        array(
            'field' => 'page',
            'label' => 'lang:form_page',
            'rules' => 'required|is_natural_no_zero'
        ),
        array(
            'field' => 'document_type[]',
            'label' => 'lang:form_document_type',
            'rules' => 'required|is_field[document_type]'
        ),
        array(
            'field' => 'domain[]',
            'label' => 'lang:form_domain',
            'rules' => 'required|is_field[domain]'
        ),
        array(
            'field' => 'language[]',
            'label' => 'lang:form_language',
            'rules' => 'is_field[language]'
        ),
        array(
            'field' => 'file',
            'label' => 'lang:form_file',
            'rules' => 'callback__checkFile'
        ),
//        array(
//            'field' => 'recaptcha_response_field',
//            'label' => 'lang:form_recaptcha',
//           // 'rules' => 'required|max_length[128]|xss_clean|recaptcha_matches'
//        ),
        array(
            'field' => 'date_realisation_year[]',
            'label' => 'lang:form_date_realisation',
            'rules' => 'is_natural_no_zero'
        ),
        array(
            'field' => 'date_realisation_month[]',
            'label' => 'lang:form_date_realisation',
            'rules' => 'is_natural_no_zero'
        ),
        array(
            'field' => 'study_level[]',
            'label' => 'lang:form_study_level',
            'rules' => 'is_field[study_level]'
        )
    ),
    /*
     * Formulaire de contact
     */
    'main/contact' => array(
        array(
            'field' => 'emailaddress',
            'label' => 'EmailAddress',
            'rules' => 'required|valid_email'
        ),
        array(
            'field' => 'name',
            'label' => 'Name',
            'rules' => 'required|alpha'
        ),
        array(
            'field' => 'title',
            'label' => 'Title',
            'rules' => 'required'
        ),
        array(
            'field' => 'message',
            'label' => 'MessageBody',
            'rules' => 'required'
        )
    ),
    'group/login' => array(
        array(
            'field' => 'password',
            'label' => 'lang:password',
            'rules' => 'required|callback__checkPassword'
        )
    ),
    'group/create' => array(
        array(
            'field' => 'name',
            'label' => 'lang:form_name',
            'rules' => 'required|min_length[3]|max_length[255]|callback__checkSameName'
        ),
        array(
            'field' => 'description',
            'label' => 'lang:form_description',
            'rules' => 'max_length[255]'
        ),
        array(
            'field' => 'password',
            'label' => 'lang:form_password',
            'rules' => 'callback__checkPrivateGroup'
        ),
        array(
            'field' => 'password2',
            'label' => 'lang:form_password2',
            'rules' => 'matches[password]'
        ),
        array(
            'field' => 'website',
            'label' => 'lang:form_website',
            'rules' => 'prep_url'
        )
    ),
    'group/share' => array(
        array(
            'field' => 'share',
            'label' => 'lang:share',
            'rules' => 'required'
        ),
        array(
            'field' => 'text',
            'label' => 'lang:text',
            'rules' => ''
        )
    ),
    'minimess/speak' => array(
        array(
            'field' => 'speak',
            'label' => 'lang:speak',
            'rules' => 'required'
        ),
    )
);

