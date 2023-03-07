<?php
/**
 * custom design module to sync data
 * between prestashop and Katana Stock system
 * using Katana API v1
 *
 * @author    dani9 <dani.strba@gmail.com>
 * @copyright 2022 dani9
 */

 class mailSubject extends ObjectModel
 {

 	/**
 	 * Fields
 	 */
	public $id;
	public $subject;

	/** @var bool Enables to define an ID before adding object. */
	public $force_id = true;

	/**
	 * Definition
	 * @var unknown
	 */
	public static $definition = [
		'table' => 'mailtplvars_subjects',
		'primary' => 'id',
		'multilang' => true,
		'fields' => [
			'id' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
			'subject' => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 155],
		]
	];
}
