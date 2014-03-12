<?php

/**
 * Description of PDO
 *
 * @property PDO $bdd
 * @author leo
 */
class Pdo_driver extends DbDriver {

    /**
     * connection à la bdd en utilisant pdo
     * gestion des erreures
     */
    public function connectDb() {
        $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $bdd = new PDO('mysql:host=' . $this->getHost() . ';dbname=' . $this->getBddName() . '', $this->getUser(), $this->getMdp(), $pdo_options);

        $bdd->exec('SET NAMES utf8');
        $this->setBdd($bdd);
    }

    /**
     * execute la requête sql. utilise les proprieté de pdo pour linsertion de variables dans la requête
     * si une entité est passée en paramètre, rempli cette entitée
     * retourn un tableau associatif par défaut
     * 
     * @param string $query la requête PDO à excutée
     * @param aray[optional] $data les valeures à insérer dans la requête
     * @param Object[optional] $object l'entité à remplir avec la requête
     * @param int[optianl] $fetchMode la façon dont la requête est retournée
     * @return mixed <code>FALSE</code> si une erreure, sinon le résultat de l'exécution de la requête
     */
    public function execute($query, $data = array(), $object = NULL, $fetch = FALSE, $fetchMode = PDO::FETCH_ASSOC) {
        try {
            $this->startObserve();
//            echo '<br>'.$query;
//            print_r($data);
//            echo '<br>';
            if (empty($data)) {
                $req = $this->getBdd()->query($query);
            } else {
                $req = $this->getBdd()->prepare($query);
                $req->execute($data);
            }
            if ($object == NULL) {
                $req->setFetchMode($fetchMode);
            } else {
                $req->setFetchMode(PDO::FETCH_INTO, $object);
            }

            if ($req->rowCount() == 0) {
                return FALSE;
            }

            $result = array();
            if (stripos($query, 'INSERT') !== FALSE or stripos($query, 'UPDATE') !== FALSE or stripos($query, 'DELETE') !== FALSE) {
                return TRUE;
            }
            if ($req->rowCount() > 1 or $fetch) {
                $result = $req->fetchAll();
            } else {
                $result = $req->fetch();
            }
            $req->closeCursor();
        } catch (Exception $e) {
            debug($e->getLine(), 'Erreur a la ligne: ');
            debug($e->getFile(), 'Dans le fichier: ');
            debug($e->getMessage(), 'Message:');
            code($query, 'SQL:');
            debug($e->getTraceAsString(), 'trace:</br>');
            die();
        }
        $this->addQuery($query, $data);
        return $result;
    }

    public function fetchClass($query, $data, $class) {
        try {
            $this->startObserve();
//            code($query);
//            debug($data);
            
            $req = $this->getBdd()->prepare($query);
            $req->execute($data);
            $res = array();
            $nbRow = $req->rowCount();
            $nbFetch = 0;
            while ($nbFetch < $nbRow) {
                $nbFetch++;
                $object = new $class();
                $req->setFetchMode(PDO::FETCH_INTO, $object);
                $req->fetch();
                if (method_exists($class, 'getLanguage')) {
                    $object->setLanguage($class->getLanguage());
                }
                $res[$object->id] = $object;
            }
            $req->closeCursor();
        } catch (Exception $e) {
            debug($e->getLine(), 'Erreur a la ligne: ');
            debug($e->getFile(), 'Dans le fichier: ');
            debug($e->getMessage(), 'Message:');
            code($query, 'SQL:');
            debug($e->getTraceAsString(), 'trace:</br>');
            die();
        }
        $this->addQuery($query, $data);
        return $res;
    }

    /**
     * compte le nombre de lignes retournées par la requête
     * @param string $query la requête
     * @param array $data les valeures à insérer dans la requête
     * @return int le nombre de ligne
     */
    public function count($query, array $data = array()) {
        try {
//            echo '<br>'.$query;
//            print_r($data);
//            echo '<br>';
            $this->startObserve();
            $req = $this->getBdd()->prepare($query);
            $req->execute($data);
            $count = $req->rowCount();
            $req->closeCursor();
        } catch (Exception $e) {
            debug($e->getLine(), 'Erreur a la ligne: ');
            debug($e->getFile(), 'Dans le fichier: ');
            debug($e->getMessage(), 'Message:');
            code($query, 'SQL:');
            debug($e->getTraceAsString(), 'trace:</br>');
            die();
        }
        $this->addQuery($query, $data);
        return $count;
    }

}

?>
