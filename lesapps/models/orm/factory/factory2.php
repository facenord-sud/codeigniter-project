<?php

/**
 * Gère le chargement des modules du framework orm
 *
 * @author leo
 */
class Factory2 {

    private function _loadAbstractClass() {
        require_once APPPATH . '/models/orm/factory/abstract_factory.php';
    }

    /**
     * 
     * @return la <code>fabrique</code> qui s'occupe du chargement des classes
     * de tout un module pour le framwork orm.
     * Par exemple, le module pdo
     */
    public function getPlugin() {
        $this->_loadAbstractClass();
        $CI = & get_instance();
        $module = $CI->config->item('orm_db');
        $CI->load->model('orm/factory/' . $module . '_factory', 'plugin_factory');
        return get_instance()->plugin_factory;
    }

}

?>
