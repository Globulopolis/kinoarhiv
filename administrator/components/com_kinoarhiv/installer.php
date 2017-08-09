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

@set_time_limit(0);

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since  3.0
 */
class Com_KinoarhivInstallerScript
{
	/**
	 * Function to perform changes during install
	 *
	 * @param   JInstallerAdapterComponent  $installer  The class calling this method
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install($installer)
	{
		$db   = JFactory::getDbo();
		$form = $this->loadForm();
		$data = array();

		// Get the fieldset names
		$name_fieldsets = array();

		foreach ($form->getFieldsets() as $fieldset)
		{
			$name_fieldsets[] = $fieldset->name;
		}

		// Fill paths to folders
		foreach ($name_fieldsets as $fieldset_name)
		{
			foreach ($form->getFieldset($fieldset_name) as $field)
			{
				$fieldname = $field->getAttribute('name');
				$data[$fieldname] = $field->getAttribute('default');

				// Process paths for media folders
				if (strrpos($fieldname, 'media_') !== false && strrpos($fieldname, 'root_www', -8) !== false)
				{
					$_field_root = substr($fieldname, 0, -4);
					$data[$_field_root] = str_replace('\\', '/', JPATH_ROOT . $field->getAttribute('default'));
					$data[$fieldname] = $field->getAttribute('default');
				}

				// Process sys paths
				if ($fieldname == 'ffmpeg_path' || $fieldname == 'ffprobe_path')
				{
					$data[$fieldname] = str_replace('\\', '/', JPATH_ROOT . '/' . $field->getAttribute('default'));
				}

				if ($fieldname == 'def_cache')
				{
					$data['def_cache'] = str_replace('\\', '/', JPATH_ROOT . '/' . $field->getAttribute('default'));
				}

				if ($fieldname == 'upload_gallery_watermark_image')
				{
					$data['upload_gallery_watermark_image'] = str_replace('\\', '/', JPATH_ROOT . $field->getAttribute('default'));
				}
			}
		}

		$data['use_alphabet'] = 0;
		$params = json_encode($data);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . " = '" . $db->escape($params) . "'")
			->where($db->quoteName('element') . " = 'com_kinoarhiv'")
			->where($db->quoteName('type') . " = 'component'");

		$db->setQuery($query);
		$db->execute();

		// Call JInstaller->setRedirectURL()
		$installer->getParent()->setRedirectURL('index.php?option=com_kinoarhiv&view=settings');
	}

	/**
	 * Method to update component.
	 *
	 * @param   JInstallerAdapterComponent  $installer  The class calling this method.
	 *
	 * @return  void
	 */
	public function update($installer)
	{
		$db = JFactory::getDbo();
		$form = $this->loadForm();
		$params = $this->getParams();

		/* Load the config.xml file on update and compare current existing parameters from DB
		 * with the parameters from file and add new into array and store in DB.
		*/
		// Get the fieldset names
		$name_fieldsets = array();

		foreach ($form->getFieldsets() as $fieldset)
		{
			$name_fieldsets[] = $fieldset->name;
		}

		foreach ($name_fieldsets as $fieldset_name)
		{
			foreach ($form->getFieldset($fieldset_name) as $field)
			{
				$fieldname = $field->getAttribute('name');

				// Add new parameter only if it's not exists in current component parameters
				if (!array_key_exists($fieldname, $params))
				{
					$params[$fieldname] = $field->getAttribute('default');
				}
			}
		}

		$params = json_encode($params);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . " = '" . $db->escape($params) . "'")
			->where($db->quoteName('element') . " = 'com_kinoarhiv'")
			->where($db->quoteName('type') . " = 'component'");

		$db->setQuery($query);
		$db->execute();
		/* End of loading and updating component parameters */

		$this->updateDatabase();

		// Call JInstaller->setRedirectURL()
		$installer->getParent()->setRedirectURL('index.php?option=com_kinoarhiv');
	}

	/**
	 * Method to update Database
	 *
	 * @return  void
	 */
	protected function updateDatabase()
	{
		if (JFactory::getDbo()->getServerType() === 'mysql')
		{
			return;
		}
	}

	/**
	 * Get component parameters.
	 *
	 * @return  array
	 *
	 * @since   3.0
	 */
	public function getParams()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . " = 'component'")
			->where($db->quoteName('name') . " = 'Kinoarhiv'");

		$db->setQuery($query);
		$params = json_decode($db->loadResult(), true);

		return $params;
	}

	/**
	 * Get component parameters.
	 *
	 * @return  JForm  JForm instance.
	 *
	 * @since   3.0
	 */
	private function loadForm()
	{
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/');
		$form = JForm::getInstance('com_kinoarhiv.config', 'config', array('control' => 'jform', 'load_data' => array()), true, '/config');

		return $form;
	}
}
