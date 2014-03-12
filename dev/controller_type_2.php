extends MY_Controller {

    public function __construct() {
        parent::__construct();

        $this->lang->loads('');
    }

    /**
     * Page d'index
     *
     * 
     */
    public function index() {
        $this->twiggy->set($this->lang->getLanguages());
        $this->twiggy->display('');
    }
}
?>
