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

use Joomla\String\StringHelper;

/**
 * Class KinoarhivModelSettings
 *
 * @since  3.0
 */
class KinoarhivModelSettings extends JModelForm
{
	/**
	 * Method to get a form object.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since  3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFieldPath(JPATH_ROOT . '/components/com_kinoarhiv/models/fields/');

		// Load config.xml from root component folder.
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/');
		$form = $this->loadForm('com_kinoarhiv.config', 'config', array('control' => 'jform', 'load_data' => $loadData), false, '/config');

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Get the component information.
	 *
	 * @return  object
	 *
	 * @since  3.0
	 */
	public function getSettings()
	{
		return JComponentHelper::getComponent('com_kinoarhiv');
	}

	/**
	 * Method to save the configuration data.
	 *
	 * @param   array  $data  containing config data.
	 *
	 * @return  boolean   True on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function save($data)
	{
		$db = $this->getDbo();
		$rules = $data['rules'];

		// Unset rules array because we do not need it in the component parameters
		unset($data['rules']);

		if ($data['introtext_actors_list_limit'] > 10)
		{
			$data['introtext_actors_list_limit'] = 10;
		}
		elseif ($data['introtext_actors_list_limit'] < 0)
		{
			$data['introtext_actors_list_limit'] = 0;
		}

		if ($data['person_list_limit'] > 10)
		{
			$data['person_list_limit'] = 10;
		}
		elseif ($data['person_list_limit'] < 1)
		{
			$data['person_list_limit'] = 1;
		}

		if ($data['premieres_list_limit'] > 5)
		{
			$data['premieres_list_limit'] = 5;
		}
		elseif ($data['premieres_list_limit'] < 0)
		{
			$data['premieres_list_limit'] = 0;
		}

		if ($data['releases_list_limit'] > 5)
		{
			$data['releases_list_limit'] = 5;
		}
		elseif ($data['releases_list_limit'] < 0)
		{
			$data['releases_list_limit'] = 0;
		}

		if ($data['slider_min_item'] > 10)
		{
			$data['slider_min_item'] = 10;
		}
		elseif ($data['slider_min_item'] < 1)
		{
			$data['slider_min_item'] = 1;
		}

		$app       = JFactory::getApplication();
		$alphabet  = $app->input->post->get('letters', array(), 'array');
		$filter    = JFilterInput::getInstance();
		$_alphabet = array();

		if (count($alphabet['movie']) > 0)
		{
			foreach ($alphabet['movie'] as $key => $el)
			{
				foreach ($el as $i => $val)
				{
					if ($key == 'lang')
					{
						$_alphabet['movie_alphabet'][$i][$key] = $filter->clean($val, 'string');
					}
					elseif ($key == 'letters')
					{
						$_alphabet['movie_alphabet'][$i][$key] = explode(
							',',
							StringHelper::strtoupper(
								str_replace(' ', '', $filter->clean($val, 'string'))
							)
						);
					}
				}
			}
		}

		if (count($alphabet['name']) > 0)
		{
			foreach ($alphabet['name'] as $key => $el)
			{
				foreach ($el as $i => $val)
				{
					if ($key == 'lang')
					{
						$_alphabet['name_alphabet'][$i][$key] = $filter->clean($val, 'string');
					}
					elseif ($key == 'letters')
					{
						$_alphabet['name_alphabet'][$i][$key] = explode(
							',',
							StringHelper::strtoupper(
								str_replace(' ', '', $filter->clean($val, 'string'))
							)
						);
					}
				}
			}
		}

		if (count($alphabet['album']) > 0)
		{
			foreach ($alphabet['album'] as $key => $el)
			{
				foreach ($el as $i => $val)
				{
					if ($key == 'lang')
					{
						$_alphabet['album_alphabet'][$i][$key] = $filter->clean($val, 'string');
					}
					elseif ($key == 'letters')
					{
						$_alphabet['album_alphabet'][$i][$key] = explode(
							',',
							StringHelper::strtoupper(
								str_replace(' ', '', $filter->clean($val, 'string'))
							)
						);
					}
				}
			}
		}

		$data   = array_merge($data, $_alphabet);
		$params = json_encode($data);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . " = '" . $db->escape($params) . "'")
			->where(array($db->quoteName('type') . " = 'component'", $db->quoteName('element') . " = 'com_kinoarhiv'"));

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		if (JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && $rules)
		{
			$rules = new JAccessRules($rules);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . " = '" . $rules . "'")
				->where(array($db->quoteName('level') . " = 1", $db->quoteName('parent_id') . " = 1", $db->quoteName('name') . " = 'com_kinoarhiv'"));

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_KA_NO_ACCESS_RULES_SAVE'), 'error');

			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Restore settings from file into DB
	 *
	 * @param   string  $data  String with configuration
	 *
	 * @return boolean
	 *
	 * @since  3.0
	 */
	public function restoreConfig($data)
	{
		$db     = $this->getDbo();
		$params = json_encode($data);
		$query  = $db->getQuery(true);

		$query->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . " = '" . $db->escape($params) . "'")
			->where(array($db->quoteName('type') . " = 'component'", $db->quoteName('element') . " = 'com_kinoarhiv'"));

		$db->setQuery($query);
		$result = $db->execute();

		return $result ? true : false;
	}
}
