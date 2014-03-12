<?php

/**
 * @access public
 * @author leo
 */
class Address_entity {
	/**
	 * @var int
	 */
	public $id=0;
        
	/**
	 * @var string
	 */
	public $name='';
        
	/**
	 * @var int
	 */
	public $number=0;
        
        /**
         *
         * @var string
         */
	public $street='';
        
	/**
         * le code postal
         * 
	 * @var int
	 */
	public $code='';
        
	/**
         * le code iso du pays
         * 
	 * @var string
	 */
	public $country='';
        
        /**
         * l'addresse actuelle de l'utilisateur
         * @var boolean 
         */
        public $current=FALSE;
	
}
?>