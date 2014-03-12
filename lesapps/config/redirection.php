<?php

/*
 * Permet de définir quelle route on ne veut pas de redirection automatique
 * les clés et valeurs sont le nom du bundle et le nom correspondant à la méthodes.
 * Ils sont définis dans le fichier de config routing.php
 * @see redirectLastPage()
 */

$config['no_redirection']=array(
    'user/logout',
);
$config['redirect_to'] =  base_url();
?>
