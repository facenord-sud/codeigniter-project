<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Description of firewall
 * @tested tout fait, sauf le cas ou l'utilisateur à le droit d'accéder au bundle
 * mais pas à toutes les méthodes
 * @property UserSession $user
 * @author leo
 */
class Firewall {

    /**
     * tous les bundles déclarés dans le firewall.xml
     * @var type 
     */
    private $bundles;

    /**
     * la méthode qui sera appelée
     * @var type 
     */
    private $method;

    /**
     * le controlleur qui sera appelé
     * @var type 
     */
    private $class;

    /**
     * l'utilisateur qui à demandé la page
     * @var type 
     */
    private $user;
    private $lang;

    const LOGIN = 'true';

    public function __construct() {

        $router = &load_class('Router');
        $this->class = $router->class;
        $this->method = $router->method;

        $dom = new DOMDocument();
        $dom->load(APPPATH . 'config/firewall.xml');
        $this->bundles = $dom->getElementsByTagName('bundle');

        $CI = & get_instance();
        $this->user = $CI->usersession;
        $this->lang = $CI->lang;
    }

    private function _hasAcces() {

        /*
         * Pour chaque bundle:
         * 1: si la regex définie dans la balise name du bundle corresspond à l'uri
         *      a) si ça l'est, on continue
         *      b) sinon on passe au prochain bundle
         * 2: si on doit être logguer ou non 
         *      a) si on doit être logguer on regarde si le user l'est
         *              i)si il l'est pas on return false
         * 3: Si role de l'utilisateur corresspond à un des types de user défini dans le fichier xml
         *      a) on continue
         *      b) sinon on retourne false
         * 4: idem, mais pour la méthode si elle est définie. 
         */

        foreach ($this->bundles as $bundle) {
            $nameClass = $bundle->attributes->getNamedItem('name')->value;
            //si le nom correspond pas, on arrête l'exécution de la boucle et on reprend avce le prochain index
            if ($nameClass != $this->class) {
                continue;
            }
            //mnt on sait que le nom du bundle corresspond au nom du controlleur
            //demandé par l'url

            $methods = $bundle->getElementsByTagName('method');

            //si l'accès  requiert un login
            if ($bundle->attributes->getNamedItem('login')->value == Firewall::LOGIN) {
                //mnt on sait que l'utilisateur doit être connecté

                if ($this->user->isConnected() == FALSE) {//si l'utilisateur n'est pas connecté
                    return FALSE;
                }
                $users = $bundle->getElementsByTagName('user');

                if (!$this->_hasUserAcces($users)) {
                    return FALSE;
                }
            }

            //maintenant on sait que l'utilisateur à le droit d'accéder au bundle
            //même principe pour la ou les méthode(si il y en a) du controlleur


            foreach ($methods as $method) {
//                echo $method->attributes->getNamedItem('name')->value.' '.$this->method.'<br>';s
                //détermine si c'est la bonne méthode
                if ($method->attributes->getNamedItem('name')->value != $this->method) {
                    continue;
                }
                //mnt on teste si l'utilisateur à les droits d'accès pour la méthode.
                //cette méthode correspond à celle demandé dans l'url
                $users = $method->getElementsByTagName('user');
                //si on décrit des accèes et que l'utilisateur n'est pas connecté, ça fout le bordel
                if(!empty($users) and !$this->user->isConnected()) {
                    return FALSE;
                }
                if (!$this->_hasUserAcces($users)) {
                    return FALSE;
                }
            }
        }//fin du for

        return TRUE; // tout c'est bien passé, l'utilisateur à le droit de voir 
        //la page affiché par la méthode et le controlleur demandé.
    }

    private function _hasUserAcces($users) {
        //@todo améliorer l'efficacité. En utilisant intersect_array() ?
        foreach ($users as $user) {
            $allowedRole = $user->attributes->getNamedItem('name')->value;
            if($this->user->hasRole($allowedRole)) {
                return TRUE;
            }
        }
        return FALSE;
    }

    private function _hasSpecifiedAcces() {
        $fileSpecifiedFirewall = APPPATH . 'libraries/firewall/' . ucfirst($this->class) . 'Firewall.php';
        if (!is_file($fileSpecifiedFirewall)) {
            return FALSE;
        }
        $CI = & get_instance();
        $CI->load->library('firewall/' . $this->class . 'firewall', NULL, 'classFirewall');
        $ref = new ReflectionClass($CI->classFirewall);

        if ($ref->hasMethod($this->method)) {
            $met = new ReflectionMethod($CI->classFirewall, $this->method);
            return $met->invoke($CI->classFirewall, $CI);
        }
        return FALSE;
    }

    private function _showErrorNoAcces() {
        show_error('pas les perm ou pas connecté. Eh oui, j\'ai configuré le firewall, il faut ce mettre en administrateur (id 18 dans ma table). A faire: une jolie erreure et multilangue svp!', 300, 'Pas les droits');
    }

    public function start() {
        if ($this->_hasSpecifiedAcces()) {
//            echo 'salut1';
        } else {
            if ($this->_hasAcces()) {
//                echo 'salut2';
            } else {
                $this->_showErrorNoAcces();
            }
        }
    }

}

?>
