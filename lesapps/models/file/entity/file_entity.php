<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Entité des fichiers sur le serveur (documents, images par ex).
 *
 * @relations : 
 * Many to one : Document
 * One to one : User
 * 
 * @author yves
 */
require_once APPPATH . 'models/tree/abstractTree.php';

class File_entity extends AbstractTree {

    /**
     * id du file
     *
     * @var int
     */
//    public $id;// l'id est déjà initialisé dans le tree

    /**
     * Nom du fichier avant qu'il ne soit envoyé sur le serveur (nom que l'auteur lui a donné)
     *
     * @var string : varchar(255)
     */
    public $name;

    /**
     * Nom du fichier une fois enregistré sur le server
     * . pour un dossier
     *
     * @var string : varchar(255)
     */
    public $slug;
    
    /**
     * Chemin absolut du dossier dans lequel le fichier est enregistré
     *
     * @var string : varchar(255)
     */
    public $path = '';

    /**
     * Chemin absolut du fichier enregistré
     *
     * @var string : varchar(255)
     */
    public $full_path = '';
    
    /**
     * Chemin dans lequel se situe le fichier ('/images' par exemple)
     *
     * @var string : varchar(255)
     */
    public $folder = '';

    /**
     * Date d'envoi du fichier
     *
     * @var int
     */
    public $date;

    /**
     * Poids du fichier
     *
     * @var int
     */
    public $size;
    
    /**
     * Type du fichier
     *
     * @var varchar
     */
    public $type;

    /**
     * Extension du fichier (pdf, doc, etc..)
     *
     * @var varchar(10)
     */
    public $extension;

    /**
     * Nombre de fois qu'on a téléchargé ce fichier
     *
     * @var int
     */
    public $hit = 0;
    
    /**
     * Bool pour voir si c'est une image
     * 
     * @var Bool 
     */
    public $is_image = FALSE;
    
    /**
     * Largeur de l'image
     * 
     * @var int
     */
    public $image_width = 0;
    
    /**
     * Hauteur de l'image
     * 
     * @var int
     */
    public $image_height = 0;
    
    /**
     * Type de l'image
     * 
     * @var varchar
     */
    public $image_type = '';
    
    /**
     * La taille de l'image en strin
     * 
     * @var varchar
     */
    public $image_size_str = '';

}

?>
