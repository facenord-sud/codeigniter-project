<?php

/**
 * Cette classe contient les langues
 *
 * @author yves
 */
class Language_entity extends Entity{

    /**
     * Id
     *
     * @var int
     */
    public $id;

    /**
     * l'iso de la langue
     *
     * @var varchar(2)
     */
    public $lang = 'dt';

    /**
     * La langue écrit en toute lettre
     *
     * @var varchar(50)
     */
    public $language = 'Developpement';
}