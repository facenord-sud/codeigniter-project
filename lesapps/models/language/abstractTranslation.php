<?php

/**
 * Cette classe abstraite permet d'être implémentée pour créer une bdd multilangue
 *
 * @author yves
 */
abstract class AbstractTranslation {

    /**
     * Id
     *
     * @var int
     */
    public $id;

    /**
     * L'id de la langue
     *
     * @var int
     */
    public $language = 1;

    /**
     * C'est l'id de référence que le champs en question traduit.
     *
     * @var varchar(255)
     */
    public $reference;
}