<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Print Helper permet d'automatiser l'affichage de données
 *
 * @author Yves
 */
/**
 * Table Fetch permet d'afficher un tableau avec les données reçue par une requette
 *
 * @param $fetchAll est la tableau renvoyé par fetchAll qui contient les données
 * @param $fields array ou sont les champs à afficher dans le tableau et leur titre 
 * array(field => Label, field2 => Label2)
 * Exemple : $fields = array (name => Nom, desc => Description)
 * @param $caption Le caption du tableau (titre)
 * @param $summary Résumé de ce que propose le tableau
 * @param array $anchor permet de créer un lien au moyen d'unt tableau.
 * Doit être de la forme array('label'=>'le nom du champs pour le lien', 'uri'=>'l'addresse uri', 'param'=>quel paramètre)
 */
if (!function_exists('printFetchAll')) {

    function tableFetchAll($fetchAll, $fields, $caption = '', $summary = '') {

        $result = '<table summary="' . $summary . '">
        <caption>
        ' . $caption . '
        </caption>
        <thead>
          <tr>';
        foreach ($fields as $label) {
            $result .= '<th>' . $label . '</th>';
        }
        $result .= '
          </tr>
        </thead>
        <tfoot>
        </tfoot>
        <tbody>';

        foreach ($fetchAll as $fetch) {
            $result .= '<tr>';
            foreach ($fields as $field => $label) {
                $result .= '<td>' . $fetch[$field] . '</td>';
            }
            $result .= '</tr>';
        }
        $result .= '
         </tbody>
         </table>';
        return $result;
    }
}
