<?php

function loadEntity($dir, $entity='') {
    if(empty($entity)) {
        $entity = $dir;
    }
    require_once APPPATH.'models/'.$dir.'/entity/'.$entity.'_entity.php';
}
?>
