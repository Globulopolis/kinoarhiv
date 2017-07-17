<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Class KinoarhivModelSearch
 *
 * @since  3.0
 */
class KinoarhivModelSearch extends JModelForm
{
	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.search', 'search', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  object    The default data.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		// This is always validated and filtered(see getActiveFilters()), even if data injected from request directly.
		return JFactory::getApplication()->input->get('form', array(), 'array');
	}

	/**
	 * Get the homepage Itemid for movies and names lists
	 *
	 * @return  array
	 *
	 * @since  3.0
	 */
	public function getHomeItemid()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$itemid = array('movies' => 0, 'names' => 0);

		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__menu'))
			->where("link = 'index.php?option=com_kinoarhiv&view=movies' AND type = 'component'")
			->where("published = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->setLimit(1, 0);

		$db->setQuery($query);
		$itemid['movies'] = $db->loadResult();

		$query = $db->getQuery(true)
			->select('id')
			->from($db->quoteName('#__menu'))
			->where("link = 'index.php?option=com_kinoarhiv&view=names' AND type = 'component'")
			->where("published = 1 AND access IN (" . $groups . ") AND language IN (" . $db->quote($lang->getTag()) . ",'*')")
			->setLimit(1, 0);

		$db->setQuery($query);
		$itemid['names'] = $db->loadResult();

		return $itemid;
	}

	/**
	 * Get an active filters from search page
	 *
	 * @return  mixed  Object of filtered data if valid, false otherwise.
	 *
	 * @since  3.0
	 */
	public function getActiveFilters()
	{
		$app = JFactory::getApplication();
		$form = $this->getForm();
		$content = $app->input->get('content', '', 'word');

		if (empty($content))
		{
			KAComponentHelper::eventLog('Wrong search query: content parameter');

			return false;
		}

		$data[$content] = $app->input->get($content, array(), 'array');
		$validData = $this->validate($form, $data, $content);

		if ($validData === false)
		{
			return false;
		}

		$data = new Registry;
		$data->loadArray($validData);

		return $data;
	}
}
