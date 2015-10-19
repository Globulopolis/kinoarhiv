<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\String\String;

/**
 * Content component helper.
 *
 * @since  3.0
 */
class KAContentHelper extends JHelperContent
{
	/**
	 * Method to encode item alias for using in filesystem paths and url.
	 *
	 * @param   string  $item_alias  Item alias
	 * @param   string  $item_title  Item title
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public static function getFilesystemAlias($item_alias, $item_title)
	{
		if (empty($item_alias) && empty($item_title))
		{
			echo json_encode(
				array(
					'success' => false,
					'message' => JText::_('COM_KA_FIELD_MOVIE_FS_ALIAS_GET_ERROR')
				)
			);

			jexit();
		}

		if (empty($item_alias))
		{
			$item_alias = $item_title;
		}

		$item_alias = String::substr(String::strtolower($item_alias), 0, 1);

		echo json_encode(
			array(
				'success' => true,
				'data'    => rawurlencode($item_alias)
			)
		);
	}
}
