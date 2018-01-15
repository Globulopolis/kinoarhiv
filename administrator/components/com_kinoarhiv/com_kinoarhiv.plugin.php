<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * JComments plugin class for Kinoarhiv support
 *
 * @since  3.0
 */
class jc_com_kinoarhiv extends JCommentsPlugin
{
	/**
	 * Get object title.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  string
	 */
	public function getObjectTitle($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('title', 'year')))
			->from($db->quoteName('#__ka_movies'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);
		$item = $db->loadObject();

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		return KAContentHelper::formatItemTitle($item->title, '', $item->year);
	}

	/**
	 * Get item link.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  string
	 */
	public function getObjectLink($id)
	{
		$link = '';

		// Not yet implemented

		return $link;
	}

	/**
	 * Get item author ID.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  integer
	 */
	public function getObjectOwner($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('created_by'))
			->from($db->quoteName('#__ka_movies'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);
		$userid = $db->loadResult();

		return (int) $userid;
	}
}
