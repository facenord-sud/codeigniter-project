<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of group_share_form
 *
 * @author Numa de Montmollin <facenord.sud@gmail.com>
 */
class group_share_form extends AbstractFormular {

    protected function form() {
        my_form('share', 'share');
        my_form('text', 'text', 'text');
    }

}

?>
