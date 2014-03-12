<?php

/**
 * La classe étendue de CI_Lang. une nouvelle fonction implémetée pour mieux
 * gérer le mutlilinguisme dans Twig.
 * 
 * @version 0.2
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class MY_Lang extends CI_Lang {

    /**
     * le tabeleau composé des variables qui sont actuellement ajouté au tableau (lors d'un load)
     * Contient la valeur de $lang dans la fonction load().
     * Utile pour les insérer dans loads
     * 
     * @var string $load_language le tabeleau qui contient tous le texte pour une vue
     */
    private $load_language;

    /**
     * le tabeleau composé de différents tableau contenant toutes les variables pour
     * afficher le text dans une vue et les layouts de la vue.
     * 
     * @var string $languages le tabeleau qui contient tous le texte pour une vue
     */
    private $languages;

    /**
     * Le string qui contient le language de la page. Par exemple french
     * 
     * @var String
     */
    private $idiom;

    /**
     * Le string qui contient le sigle de deux lettrs qui fait référence au language
     * de la page. par exemple, fr. Ces deux lettres sont présentes dans l'url
     * 
     * @var String 
     */
    private $tagLang;

    /**
     * le constructeur. Redéfini pour initialiser la variables $languages
     * 
     * @access public
     * @param null 
     */
    public function __construct() {
        parent::__construct();
        $this->languages = array();
        $this->load_language = array();
        $this->idiom = '';
        $this->tagLang = '';
    }

    /**
     * Cette méthode charge le fichier de langue passé en paramètre dans la variable
     * $language de CI_Lang et crée un tableau avec toutes les valeures de ce fichier.
     * enregistre le tableau crée dans la variable $languages. Si la clé passé en paramètre
     * est nulle alors la clé vaut le nom du fichier de langue sans son chemin.
     * 
     * ATTENTION !!! ne pas confondre avec CI_Lang->load()
     * 
     * @see MY_Lang::$languages
     * @see CI_Lang::$language
     * @access public
     * @param String $file_name le nom pour le fichier de langue à charger.
     * @param mixed $key la clé pour identifier le tableau (optionelle). Si nulle, 
     * elle vaut le nom du fichier de langue ($file_name) sans son chemin
     */
    public function loads($file_name, $key = '') {
        // On vide l'array
        $this->load_language = array();

        if (empty($key)) {
            $str = explode('/', $file_name);
            //prend le nom du fichier sans son chemin
            $key = $str[count($str) - 1];
        }
        //charge toutes les valeures du fichier
        $this->load($file_name);
        $lang = $this->load_language;
        //on enleve de $lang toutes les clé-valeures qui existent déjà
//        foreach ($this->languages as $statement) {
//            foreach ($statement as $key_statement => $value) {
//                unset($lang[$key_statement]);
//            }
//        }
        //on ajoute $lang au tableau $languages dans un tableau qui à 
        //comme clé le paramètre $key
        $this->languages = array_merge($this->languages, array($key => $this->load_language));
    }

    /**
     * @access public 
     * @param null
     * @return array $languages la variable $languages contenant toutes les valeures dans différents
     * tableau pour afficher une vue.
     */
    public function getLanguages() {
        return $this->languages;
    }

    /**
     * Load a language file
     * YVES : Je redéfinie cette fonction, pour y ajouter quelque chose
     * NUMA :  j'ai aussi ajouté deux choses le language de la page et le sigle de
     * deux lettres de la page. Sauvegardé dans deux variables de classe.
     * lignes 132 et 177
     * ATTENTION : Ne pas appeler son fichier route_lang.php !! 
     *
     * @author  Yves et numa
     * @todo faire pour inclure le fichier de base de langue si $baseLayout=true
     * @access	public
     * @param	mixed	the name of the language file to be loaded. Can be an array
     * @param	string	the language (english, etc.)
     * @param	bool	return loaded array of translations
     * @param 	bool	add suffix to $langfile
     * @param 	string	alternative path to look for language file
     * @param       bool    $baseLanguage décide de charger le fichier avec la langue du Layout de base
     * @return	mixed
     */
    function load($langfile = '', $idiom = '', $return = FALSE, $add_suffix = TRUE, $alt_path = '', $baseLayout = FALSE) {
        $langfile = str_replace('.php', '', $langfile);

        if ($add_suffix == TRUE) {
            $langfile = str_replace('_lang.', '', $langfile) . '_lang';
        }

        $langfile .= '.php';

        // Verifie que le fichier n'est pas déjà chargé
        if (in_array($langfile, $this->is_loaded, TRUE)) {
            return;
        }

        $config = & get_config();

        if ($idiom == '') {
            $deft_lang = (!isset($config['language'])) ? 'english' : $config['language'];
            $idiom = ($deft_lang == '') ? 'english' : $deft_lang;
        }
        //-----> ajouté numa
        $this->setIdiom($idiom);
        //----->fin ajouté
        // Determine where the language file is and load it
        if ($alt_path != '' && file_exists($alt_path . 'language/' . $idiom . '/' . $langfile)) {
            include($alt_path . 'language/' . $idiom . '/' . $langfile);
        } else {
            $found = FALSE;

            foreach (get_instance()->load->get_package_paths(TRUE) as $package_path) {
                // >>>>>>>>>Ajouter
                // Si $langfile == route, on load la route, qui se situe dans language/
                if ($langfile == 'route_lang.php' && file_exists($package_path . 'language/' . $langfile)) {
                    include($package_path . 'language/' . $langfile);
                    $found = TRUE;
                    break;
                }
                // >>>>>>>>>>Fin ajouter

                if (file_exists($package_path . 'language/' . $idiom . '/' . $langfile)) {
                    include($package_path . 'language/' . $idiom . '/' . $langfile);
                    $found = TRUE;

                    break;
                }
            }

            if ($found !== TRUE) {
                show_error('Unable to load the requested language file: language/' . $idiom . '/' . $langfile);
            }
        }


        if (!isset($lang)) {
            log_message('error', 'Language file contains no data: language/' . $idiom . '/' . $langfile);
            return;
        }

        if ($return == TRUE) {
            return $lang;
        }

        $this->is_loaded[] = $langfile;
//        echo '-------------------------------------------------<br />';
//        print_r($this->language);
        $this->language = array_merge($this->language, $lang);
//        ------>ajouté numa
        $this->setTagLang($this->language['lang']);
//        ------>fin ajouté
        $this->load_language = $lang;
        unset($lang);

        log_message('debug', 'Language file loaded: language/' . $idiom . '/' . $langfile);
        return TRUE;
    }

    /**
     * Fetch a single line of text from the language array
     * YVES : J'ajoute juste la key, mais si on la met pas, la function reste par défault
     * et donc va chercher le ligne du lieu ou on se trouve lorsqu'on l'appelle
     *
     * @access	public
     * @param	string	$line	the language line
     * @return	string
     */
    function line($line = '', $key = '') {
        if (empty($key)) {
            $value = ($line == '' OR !isset($this->language[$line])) ? FALSE : $this->language[$line];

            // Because killer robots like unicorns!
            if ($value === FALSE) {
                log_message('error', 'Could not find the language line "' . $line . '"');
            }

            return $value;
        } else {
            $value = ($line == '' OR !isset($this->languages[$key][$line])) ? FALSE : $this->languages[$key][$line];
            // Because killer robots like unicorns!
            if ($value === FALSE) {
                log_message('error', 'Could not find the language line "' . $line . '" in key "' . $key . '"');
            }
            return $value;
        }
    }

    /**
     * Retourne le language de la page
     * 
     * @return String $this->idiom Le language de la page
     */
    public function getIdiom() {
        return $this->idiom;
    }

    /**
     * Enregistre le language de la page
     * 
     * @param String $idiom  le language de la page
     */
    public function setIdiom($idiom) {
        $this->idiom = $idiom;
    }

    /**
     * Retourne le sigle de deux lettre de la page
     * 
     * @return String $this->tagLang le sigle de deux lettres du language de la page
     */
    public function getTagLang() {
        return $this->tagLang;
    }

    /**
     * Enregistre le sigle de deux lettre de la page
     * 
     * @param String $tagLang le sigle de deux lettres du language de la page
     */
    public function setTagLang($tagLang) {
        $this->tagLang = $tagLang;
    }

}

//end of class
?>
