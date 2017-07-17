<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
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
		$db->setQuery("SELECT title, year FROM #__ka_movies WHERE id = " . (int) $id);
		$item = $db->loadObject();

		if (!empty($item->year) && $item->year != '0000')
		{
			$item->title = $item->title . ' (' . $item->year . ')';
		}

		return $item->title;
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

		/*$quickFaqRouterPath = JPATH_SITE.DS.'components'.DS.'com_quickfaq'.DS.'helpers'.DS.'route.php';

		if (is_file($quickFaqRouterPath)) {
			require_once ($quickFaqRouterPath);

			$db = JFactory::getDbo();

			$query = 'SELECT CASE WHEN CHAR_LENGTH(i.alias) THEN CONCAT_WS(\':\', i.id, i.alias) ELSE i.id END as slug,'
					. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as categoryslug'
					. ' FROM #__quickfaq_items AS i'
					. ' LEFT JOIN #__quickfaq_cats_item_relations AS rel ON rel.itemid = i.id'
					. ' LEFT JOIN #__quickfaq_categories AS c ON c.id = rel.catid'
					. ' WHERE i.id = '.$id
			;
			$db->setQuery($query);
			$row = $db->loadObject();

			$link = JRoute::_(QuickfaqHelperRoute::getItemRoute($row->slug, $row->categoryslug));
		}*/

		return $link;
	}

	/**
	 * Get item author ID.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  string
	 */
	public function getObjectOwner($id)
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT created_by FROM #__ka_movies WHERE id = " . (int) $id);
		$userid = $db->loadResult();

		return $userid;
	}
}
