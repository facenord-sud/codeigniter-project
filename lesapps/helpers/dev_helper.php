<?php
/**
 * affiche joliment une variable
 */
if(!function_exists('debug')) {
    function debug($var, $msg='') {
        echo '<pre style="margin: 20px; margin-bottom:0px;">';
        echo '<h3>'.$msg.'</h3> ';
        print_r($var);
        echo '</pre>';
    }
}

if(!function_exists('type')) {
    function type($var, $msg='') {
        echo '<pre style="margin: 20px; margin-bottom:0px;">';
        echo '<h3>'.$msg.'</h3> ';
        echo gettype($var);
        echo '</pre>';
    }
}

if(!function_exists('vv')) {
    function vv($var, $msg='') {
        echo '<pre style="margin: 20px; margin-bottom:0px;">';
        echo '<h3>'.$msg.'</h3> ';
        echo var_dump($var);
        echo '</pre>';
    }
}

if(!function_exists('code')) {
    function code($code, $msg='') {
        $CI =& get_instance();
        $CI->load->helper('text');
        echo '<pre style="margin: 20px; margin-bottom:0px;"><h3>'.$msg.'</h3> ';
        echo highlight_code($code);
        echo '</pre>';
    }
}

if(!function_exists('entity')) {
    function entity($entity, $msg='') {
        echo '<pre style="margin: 20px; margin-bottom:0px;"><h3>'.$msg.'</h3> ';
        if(is_array($entity)) {
            echo "Array (<br/>";
            foreach ($entity as $k => $value) {
               echo "\t[$k] => Entity ".get_class($value).' (<br/>';
                foreach ($value as $key => $v) {
                    if(empty($v)) {
                        $v = 'EMPTY_VALUE or 0';
                    }
                   echo "\t\t$key => $v<br/>";
                }
                echo "\t)\n";
            }
            echo ")<br/><br/>";
        } else {
            echo 'Entity '.get_class($entity).' (<br/>';
            foreach ($entity as $key => $value) {
                if(empty($value)) {
                    $value = 'EMPTY_VALUE';
                }
               echo "\t$key => $value<br/>";
            }
            echo ')';
        }
        echo "</pre>";
    }
}
?>
