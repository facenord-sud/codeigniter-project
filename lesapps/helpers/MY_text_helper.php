<?php

function startsWith($haystack, $needle) {
    return preg_match('`^'.$needle.'`', $haystack);
}

function endsWith($haystack, $needle) {
    return preg_match('`'.$needle.'$`', $haystack);
}

?>
