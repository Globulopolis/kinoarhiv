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

use Joomla\String\StringHelper;

/**
 * Class KinoarhivModelTools
 *
 * @since  3.0
 */
class KinoarhivModelTools extends JModelLegacy
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   string   $type  Item type.
	 * @param   integer  $id    Item ID.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function updateAlias($type, $id)
	{
		if ($type == 'movies')
		{
			return $this->updateMovieAlias($id);
		}
		elseif ($type == 'names')
		{
			return $this->updateNameAlias($id);
		}
		elseif ($type == 'albums')
		{
			return $this->updateAlbumAlias($id);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Method to update item alias and move media items.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  mixed  False on error, array with results otherwise.
	 *
	 * @since   3.0
	 */
	public function updateMovieAlias($id)
	{
		if (empty($id))
		{
			return false;
		}

		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$query = $db->getQuery(true)
			->select('title, alias, fs_alias')
			->from($db->quoteName('#__ka_movies'))
			->where('id = ' . (int) $id);

		$db->setQuery($query);
		$item = $db->loadObject();

		$fs_alias = rawurlencode(StringHelper::substr($item->alias, 0, 1));

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_movies'))
			->set("fs_alias = '" . $fs_alias . "'");

		//$db->setQuery($query);
		//$db->execute();

		return array(
			'item'      => array(
				'id'           => $id,
				'title'        => $item->title,
				'old_fs_alias' => $item->fs_alias,
				'new_fs_alias' => $fs_alias
			),
			'old_paths' => array(
				'media_posters_root'    => JPath::clean($params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $id),
				'media_wallpapers_root' => JPath::clean($params->get('media_wallpapers_root') . '/' . $item->fs_alias . '/' . $id),
				'media_scr_root'        => JPath::clean($params->get('media_scr_root') . '/' . $item->fs_alias . '/' . $id),
				'media_trailers_root'   => JPath::clean($params->get('media_trailers_root') . '/' . $item->fs_alias . '/' . $id)
			),
			'new_paths' => array(
				'media_posters_root'    => JPath::clean($params->get('media_posters_root') . '/' . $fs_alias . '/' . $id),
				'media_wallpapers_root' => JPath::clean($params->get('media_wallpapers_root') . '/' . $fs_alias . '/' . $id),
				'media_scr_root'        => JPath::clean($params->get('media_scr_root') . '/' . $fs_alias . '/' . $id),
				'media_trailers_root'   => JPath::clean($params->get('media_trailers_root') . '/' . $fs_alias . '/' . $id)
			)
		);
	}
}
