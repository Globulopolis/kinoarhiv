<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_PLATFORM') or die;

/**
 * Kinoarhiv conent class for batch process filters
 *
 * @since  3.0
 */
abstract class KAHtmlContent
{
	protected static $items = null;

	/**
	 * Get a list of the available content country items.
	 *
	 * @return  string
	 */
	public static function country()
	{
		if (empty(static::$items[__METHOD__]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id AS value, a.name AS text')
				->from('#__ka_countries AS a')
				->where('a.state >= 0')
				->order('a.name');

			$db->setQuery($query);
			static::$items[__METHOD__] = $db->loadObjectList();
		}

		return static::$items[__METHOD__];
	}

	/**
	 * Get a list of the available content vendor items.
	 *
	 * @return  string
	 */
	public static function vendor()
	{
		if (empty(static::$items[__METHOD__]))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);

			$query->select('a.id AS value, a.company_name, a.company_name_intl')
				->from('#__ka_vendors AS a')
				->where('a.state >= 0');

			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$data = array();

			foreach ($rows as $row)
			{
				$vendor = ($row->company_name_intl != '') ? $row->company_name . ' / ' . $row->company_name_intl : $row->company_name;

				$data[] = array(
					'value' => $row->value,
					'text'  => $vendor
				);
			}

			static::$items[__METHOD__] = $data;
		}

		return static::$items[__METHOD__];
	}
}
