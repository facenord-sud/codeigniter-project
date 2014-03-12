<?php

/**
 * classe abstraite permettant le lien entre le php et la bdd
 * càd connection et exécution de requête
 * @property CI_Benchmark $benchmark
 *
 * @author leo
 */
abstract class DbDriver {

    /**
     *
     * @var string la connection à la bdd. utilisé pour interroger la bdd
     */
    private $bdd;

    /**
     *
     * @var string le nom de l'utilisateur 
     */
    private $host;

    /**
     *
     * @var string le nom de la base de donnée 
     */
    private $bddName;

    /**
     *
     * @var string le nom de l'utilisateur 
     */
    private $user;

    /**
     *
     * @var string l emot de passe 
     */
    private $mdp;

    /**
     *
     * @var String le préfixes des tables 
     */
    private $prefix;

    /**
     *
     * @var array toutes les requêtes excutées 
     */
    private $queries = array();
    private $debug = FALSE;

    /**
     * récupère les infos de connexion dans le fichier et se connect à la bonne bdd
     */
    public function __construct() {
        $configDb = & get_instance()->db;
        $this->host = $configDb->hostname;
        $this->bddName = $configDb->database;
        $this->user = $configDb->username;
        $this->mdp = $configDb->password;
        $this->prefix = $configDb->dbprefix;
        $this->debug = $configDb->db_debug;
        try {
            $this->connectDB();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage() . '<br />
                N° : ' . $e->getCode());
        }
        $this->benchmark = & get_instance()->benchmark;
    }

    public abstract function execute($query, $data = array());

    public abstract function count($query, array $data = array());

    public abstract function connectDb();

    /**
     * 
     * @return PDO
     */
    public function getBdd() {
        return $this->bdd;
    }

    public function setBdd($bdd) {
        $this->bdd = $bdd;
    }

    public function getHost() {
        return $this->host;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function getBddName() {
        return $this->bddName;
    }

    public function setBddName($bddName) {
        $this->bddName = $bddName;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getMdp() {
        return $this->mdp;
    }

    public function setMdp($mdp) {
        $this->mdp = $mdp;
    }

    public function getPrefix() {
        return $this->prefix;
    }

    public function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    public function getQueries() {
        return $this->queries;
    }

    public function setQueries($queries) {
        $this->queries = $queries;
    }

    protected function startObserve() {
        if ($this->debug) {
            get_instance()->benchmark->mark('start_query');
        }
    }

    protected function addQuery($query, $data = array()) {
        if ($this->debug) {
            get_instance()->benchmark->mark('stop_query');
            $time = get_instance()->benchmark->elapsed_time('start_query', 'stop_query');
            array_push($this->queries, array('query' => $query, 'data' => $data, 'time' => $time));
        }
    }

}

?>
