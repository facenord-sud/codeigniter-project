<?php

/**
 * fichier de configuration pour créer les routes correspondantes aux urls
 */
$config['routing'] = array(
    'main' => array(
        'controller_name' => 'main',
        'prefix'=>'',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>''),
            'contact' => array('method'=>'contact', 'pattern'=>''),
        )
    ),
    'user' => array(
        'controller_name' => 'user',
        'prefix'=>'Uskebajskgdaksdgka',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>'/'),
            'profil' => array('method'=>'profil', 'pattern'=>''),
            'register' => array('method'=>'register', 'pattern'=>''),
            'edit' => array('method'=>'edit', 'pattern'=>''),
            'connect' => array('method'=>'connect', 'pattern'=>''),
            'logout' => array('method'=>'logout', 'pattern'=>''),
            'valid_inscription' => array('method'=>'valid_inscription', 'pattern'=>''), 
            'modify' => array('method'=>'modify', 'pattern'=>''),
            'delete' => array('method'=>'delete', 'pattern'=>''),
            'reset_password'=> array('method'=>'resetPassword', 'pattern'=>''), 
            'select_all' => array('method'=>'selectAll', 'pattern'=>''),
            'edit_mail' => array('method'=>'editMail', 'pattern'=>''),
            'edit_psw' => array('method'=>'editPassword', 'pattern'=>''),
            'contact' => array('method'=>'contact', 'pattern'=>''),
            'close' => array('method'=>'close', 'pattern'=>''),
            'address' => array('method'=>'address', 'pattern'=>'')
        )
    ),
    'domain' => array(
        'controller_name' => 'domain',
        'prefix'=>'domains',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>'/'),
            'show' => array('method'=>'show', 'pattern'=>''),
            'edit' => array('method'=>'edit', 'pattern'=>''),
            'create' => array('method'=>'create', 'pattern'=>''),
            'delete' => array('method'=>'delete', 'pattern'=>''),
        )
    ),
    'document' => array(
        'controller_name' => 'document',
        'prefix'=>'document',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>''),
            'show' => array('method'=>'show', 'pattern'=>''),
            'add' => array('method'=>'add', 'pattern'=>''),
            'domain' => array('method'=>'domain', 'pattern'=>''),
            'type' => array('method'=>'type', 'pattern'=>''),
        )
    ),
    'role' => array(
        'controller_name' => 'role',
        'prefix'=>'',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>'/'),
            'show' => array('method'=>'show', 'pattern'=>''),
            'delete' => array('method'=>'delete', 'pattern'=>''),
            'modify' => array('method'=>'modify', 'pattern'=>''),
            'traduce' => array('method'=>'traduce', 'pattern'=>''),
        )
    ),
    'group' => array(
        'controller_name' => 'group',
        'prefix'=>'groups',
        'methods' => array(
            'index' => array('method'=>'index', 'pattern'=>'/'),
            'show' => array('method'=>'show', 'pattern'=>''),
            'log_in' => array('method'=>'logIn', 'pattern'=>''),
            'log_off' => array('method'=>'logOff', 'pattern'=>''),
            'create' => array('method'=>'create', 'pattern'=>''),
            'show_all' => array('method'=>'showAll', 'pattern'=>''),
            'share' => array('method'=>'share', 'pattern'=>''),
            'user_group' => array('method'=>'userGroup', 'pattern'=>'my-groups'),
            'not_found' => array('method'=>'notFound', 'pattern'=>'not-found'),
                'admin' => array('method'=>'admin', 'pattern'=>''),
        )
    ),
);
?>
