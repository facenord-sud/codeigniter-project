<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * url Helper qui contient des nouvelles fonctions pour les urls.
 * 
 *
 * @author Yves+numa
 */
/**
 * No Duplicate permet d'éviter les pages dupliquée.
 * En cas de possibilité de duplicate content, lancer cette fonction au début de la methode du controller
 *
 * @param $url est l'url que nous souhaitant pour la page en question.
 * @param $curentUrl est l'url actuel
 * 
 */
if (!function_exists('noDuplicate')) {

    function noDuplicate($url) {
// Verifier que l'url est correct
        if ($url != current_url()) {
// Si elle n'est pas correct, faire une redirection 301 permanente
            redirect($url, 'location', 301);
        }
    }

}

// ------------------------------------------------------------------------

/**
 * Anchor Link redéfinie pour ajouter le language de la page actuel
 *
 * Creates an anchor based on the local URL. Avec le language de la page dans la
 * quelle est affiché le lien.
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 * @access	public
 * @param	string	the URL
 * @param	string	the link title
 * @param	mixed	any attributes
 * @param boolean $printLang permet de savoir si il faut ajouter le language dans l'url
 * @return	string
 */
if (!function_exists('anchor')) {

    function anchor($bundle = '', $name = '', $title = '', $param = '', $printLang = TRUE, $uri = '', $attributes = '') {

//----->début modif
        if ($printLang) {
            $CI = &get_instance();
            $uri = $CI->lang->getTaglang() . '/' . $uri;
        }

        if (!empty($bundle)) {
            $uri = find_uri($bundle, $name, $param, TRUE);
        }
        // Ajouté par yves, plus besoin normalement, a voir...
//        $uri = rtrim($uri, '/');
//        if (!empty($param)) {
//            if (is_array($param)) {
//                foreach ($param as $p) {
//                    $uri.='/' . $p;
//                }
//            } else {
//                $uri .= '/' . (string) $param;
//            }
//        }

//----->fin modif

        $title = (string) $title;

        if (!is_array($uri)) {
            $site_url = (!preg_match('!^\w+://! i', $uri)) ? site_url($uri) : $uri;
        } else {
            $site_url = site_url($uri);
        }

        if ($title == '') {
            $title = $site_url;
        }

        if ($attributes != '') {
            $attributes = _parse_attributes($attributes);
        }

        return '<a href="' . $site_url . '"' . $attributes . '>' . $title . '</a>';
    }

}

/*
 * Mettre une url complete en paramatre
 */
if (!function_exists('anchor_url')) {

    function anchor_url($url, $title = '', $attributes = '') {

        $title = (string) $title;

        if (!is_array($url)) {
//            prep_url(
            $site_url = (!preg_match('!^\w+://! i', $url)) ? site_url($url) : $url;
        } else {
            $site_url = site_url($url);
        }

        if ($title == '') {
            $title = $site_url;
        }

        if ($attributes != '') {
            $attributes = _parse_attributes($attributes);
        }

        return '<a href="' . $site_url . '"' . $attributes . '>' . $title . '</a>';
    }

}

// ------------------------------------------------------------------------

/**
 * redirige vers la dernière page visitée
 * redirige vers la page choisie dans le fichier de config redirect, si on veut 
 * éviter la redirection sur une page
 * @autor leo
 */
if (!function_exists('redirectLastPage')) {

    function redirectLastPage() {
        $CI = & get_instance();
        $CI->config->load('redirection');
        $redirection = $CI->config->item('no_redirection');
        $url = $_SERVER['HTTP_REFERER'];
        $uri = substr($url, strlen(base_url()), strlen($url));

        if (array_search($uri, $redirection) === TRUE) {
            redirect($CI->config->item('redirect_to'));
        }
        redirect($url);
    }

}

/**
 * retourne l'uri en fonction des paramètre $bundle et $name.
 * utilise le ficher de configration routing.php pour trouver la correspondance
 * entre un nom et une uri. Ainsi, l'url d'un lien pourra changer, sans tout modifier.
 * tout le lien interne du site devrait se faire en utilisant cette méthode
 * 
 * @author leo
 */
if (!function_exists('find_uri')) {

    function find_uri($bundle, $name = '', $param = '', $printLang = TRUE) {
        $CI = & get_instance();
        $CI->config->load('routing');
        $CI->lang->load('errors/routing');
        $routing = $CI->config->item('routing');

        if ((!isset($routing[$bundle]) or !isset($routing[$bundle]['methods'][$name])) and !empty($name)) {
            $msg = $CI->lang->line('not_find_t1') . $bundle . '+' . $name . $CI->lang->line('not_find_t2');
            show_error($msg, '404', $CI->lang->line('not_find'));
        }
        $lang = '';
        if ($printLang) {
            $lang = '/' . $CI->lang->getTaglang() . '/';
        }
        // $uri='';
        // $routes=array();
        // $keyRoute='';
        // $valueRoute = '';
        // if(empty($routing[$bundle]['pattern'])) {
        //     $uri = $routing[$bundle]['controller_name'];
        // } else {
        //     $uri = $routing[$bundle]['pattern'];
        //     $keyRoute = $uri
        //     $valueRoute = $routing[$bundle]['controller_name'];
        // }
        // if(!empty($name)) {

        // }
        $uri=$routing[$bundle]['prefix'];
        $uriMethod='';
        if(empty($uri)) {
            $uri=$routing[$bundle]['controller_name'];
        }
        if(!empty($name)) {
            // TU prends le pattern si il y en a un
            $uriMethod = $routing[$bundle]['methods'][$name]['pattern'];
            // SI il n'y en a pas, tu mets la method comme pattern
            if(empty($routing[$bundle]['methods'][$name]['pattern'])) {
                $uriMethod = $routing[$bundle]['methods'][$name]['method'];
            }
            // TU enlève le "/" à la fin si il y en a un (et si le pattern c'est "/") et tu ajoute "/" avant
            $uriMethod = rtrim('/'.$uriMethod, '/');
        }
        // J'ajoute ça car c'était impossible de trouver un lien avec des param avant (sans faire anchor)
        $uriEnd = '';
        if (!empty($param)) {
            if (is_array($param)) {
                foreach ($param as $p) {
                    if (!empty($p)) {
                        $uriEnd.='/' . $p;
                    }
                }
            } else {
                $uriEnd .= '/' . (string) $param;
            }
        }
        
        return rtrim($lang.$uri.$uriMethod.$uriEnd, '/');
    }

}

/**
 * Base URL
 * 
 * Create a local URL based on your basepath.
 * Segments can be passed in as a string or an array, same as site_url
 * or a URL to a file can be passed in, e.g. to an image file.
 * 
 * j'ai modifié pour que cela retourne avec la langue.
 *
 * @author leo
 * @access	public
 * @param string
 * @return	string
 */
if (!function_exists('find_url')) {

    function find_url($bundle, $name, $param = '', $printLang = TRUE) {
        $baseUrl = base_url();
        $baseUrl = substr($baseUrl, 1, -1);
        return 'h' . $baseUrl . find_uri($bundle, $name, $param, $printLang);
    }

}

/**
 * 
 */
if (!function_exists('find_params')) {

    function find_params() {
        echo site_url();
    }

}

/**
 * Create URL Title
 *
 * Takes a "title" string as input and creates a
 * human-friendly URL string with a "separator" string 
 * as the word separator.
 * 
 * Modifié par : Numa de Montmollin
 *
 * @access	public
 * @param	string	the string
 * @param	string	the separator
 * @return	string
 * @author codeigniter + Numa de Montmollin
 */
if (!function_exists('url_title')) {

    function url_title($str, $separator = '-', $lowercase = FALSE) {
        if ($separator == 'dash') {
            $separator = '-';
        } else if ($separator == 'underscore') {
            $separator = '_';
        }

        // On remplace les Y
        $y = array("ý", "ÿ", "Ý");
        $str = str_replace($y, "y", $str);

        // On remplace les C
        $c = array("Ç", "ç");
        $str = str_replace($c, "c", $str);

        //On remplace les A avec accent par un A normal
        $a = array("ä", "â", "à", "ã", "ä", "å", "À", "Á", "Â", "Ã", "Ä", "Å", "@");
        $str = str_replace($a, "a", $str);

        //On remplace les E avec accent par un E normal
        $e = array("é", "è", "ê", "ë", "È", "É", "Ê", "Ë");
        $str = str_replace($e, "e", $str);

        //On remplace les I avec accent par un I normal
        $i = array("ï", "î", "ì", "í", "Ì", "Í", "Î", "Ï");
        $str = str_replace($i, "i", $str);

        //On remplace les O avec accent par un O normal
        $o = array("ö", "ô", "õ", "ó", "ò", "ð", "Ò", "Ó", "Ô", "Õ", "Õ", "Ö");
        $str = str_replace($o, "o", $str);

        //On remplace les U avec accent par un U normal
        $u = array("ù", "û", "ü", "ú", "Ù", "Ú", "Û", "Ü");
        $str = str_replace($u, "u", $str);

        $q_separator = preg_quote($separator);

        $trans = array(
            '&.+?;' => '',
            '[^a-z0-9 _-]' => '',
            '\s+' => $separator,
            '(' . $q_separator . ')+' => $separator
        );

        $str = strip_tags($str);

        foreach ($trans as $key => $val) {
            $str = preg_replace("#" . $key . "#i", $val, $str);
        }

        if ($lowercase === TRUE) {
            $str = strtolower($str);
        }

        return trim($str, $separator);
    }
    
    if (!function_exists('translate_page')) {
        function translate_page($tagLang) {
            $segments = explode('/', uri_string());
            $segments[0]=$tagLang;
            $uri = '';
            foreach ($segments AS $segment) {
                $uri.=$segment.'/';
            }
            rtrim($uri, '/');
            echo $uri.'<br/>';
            return base_url().substr($uri, 0, -1);
        }
        
    }
}