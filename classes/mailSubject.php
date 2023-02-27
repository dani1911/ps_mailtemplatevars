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
	public $id_order_state;
	public $subject;

	/**
	 * Definition
	 * @var unknown
	 */
	public static $definition = [
		'table' => 'mailtplvars_subjects',
		'primary' => 'id_order_state',
		'multilang' => true,
		'fields' => [
			'id_order_state' => ['type' => self::TYPE_INT],
			'subject' => ['type' => self::TYPE_STRING, 'lang' => true],
		]
	];

	public function getSubjectByOrderState($id_order_state)
	{
        $sql = 'SELECT msl.`subject` FROM `' . _DB_PREFIX_ . 'mailtplvars_subjects_lang` msl
		WHERE msl.`id_order_state` = ' . (int)$id_order_state;

		if ($result = Db::getInstance()->executeS($sql))
		{
            return $result;
        }

        return false;
    }
}
