<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

jimport('components.com_kinoarhiv.models.api', JPATH_ROOT);

/**
 * Global model class to provide an API for backend.
 *
 * @since  3.1
 */
class KinoarhivModelAPIBackend extends KinoarhivModelAPI
{
	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct();
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 * @param   integer  $id       The item ID (movie or name).
	 *
	 * @return  object
	 *
	 * @throws  RuntimeException
	 * @since   3.1
	 */
	public function getGalleryFiles($section = '', $type = '', $tab = 0, $id = 0)
	{
		$db      = $this->getDbo();
		$input   = JFactory::getApplication()->input;
		$section = !empty($section) ? $section : $input->get('section', '', 'word');
		$type    = !empty($type) ? $type : $input->get('type', '', 'word');
		$tab     = !empty($tab) ? $tab : $input->get('tab', 0, 'int');
		$id      = !empty($id) ? $id : $input->get('id', 0, 'int');

		if ($section == 'movie' && $type == 'gallery')
		{
			$query = $this->listQueryMovieImages($tab, $id);
		}
		elseif ($section == 'name' && $type == 'gallery')
		{
			$query = $this->listQueryNameImages($tab, $id);
		}

		if (empty($query))
		{
			throw new RuntimeException(JText::_('ERROR'), 500);
		}

		$db->setQuery($query);

		try
		{
			$result = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(JText::_('ERROR'), 500);
		}

		return $result;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for movie images.
	 *
	 * @param   integer  $tab  Tab number from gallery.
	 * @param   integer  $id   The movie ID.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.1
	 */
	private function listQueryMovieImages($tab, $id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('g.id', 'g.filename')))
			->from($db->quoteName('#__ka_movies_gallery', 'g'))
			->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.movie_id') . ' = ' . (int) $id);

		return $query;
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set for name images.
	 *
	 * @param   integer  $tab  Tab number from gallery.
	 * @param   integer  $id   The movie ID.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	private function listQueryNameImages($tab, $id)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('g.id', 'g.filename')))
			->from($db->quoteName('#__ka_names_gallery', 'g'))
			->where($db->quoteName('g.type') . ' = ' . (int) $tab)
			->where($db->quoteName('g.name_id') . ' = ' . (int) $id);

		return $query;
	}
}
