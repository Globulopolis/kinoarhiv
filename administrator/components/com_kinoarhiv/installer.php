<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

@set_time_limit(0);

class com_kinoarhivInstallerScript
{
	function install($parent)
	{
		$db = JFactory::getDBO();

		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/');
		$form = JForm::getInstance('com_kinoarhiv.config', 'config', array('control' => 'jform', 'load_data' => array()), true, '/config');

		if (empty($form)) {
			throw new Exception('Cannot load the config.xml file!');
		}

		$data = array();

		// Get the fieldset names
		$name_fieldsets = array();
		foreach ($form->getFieldsets() as $fieldset) {
			$name_fieldsets[] = $fieldset->name;
		}

		foreach ($name_fieldsets as $fieldset_name) {
			foreach ($form->getFieldset($fieldset_name) as $field) {
				$fieldname = $field->getAttribute('name');
				$data[$fieldname] = $field->getAttribute('default');

				// Process paths for media folders
				if (strrpos($fieldname, 'media_') !== false && strrpos($fieldname, 'root_www', -8) !== false) {
					$_field_root = substr($fieldname, 0, -4);
					$data[$_field_root] = str_replace('\\', '/', JPATH_ROOT . $field->getAttribute('default'));
					$data[$fieldname] = $field->getAttribute('default');
				}

				// Process sys paths
				if ($fieldname == 'ffmpeg_path' || $fieldname == 'ffprobe_path') {
					$data[$fieldname] = str_replace('\\', '/', JPATH_ROOT . '/' . $field->getAttribute('default'));
				}

				if ($fieldname == 'def_cache') {
					$data['def_cache'] = str_replace('\\', '/', JPATH_ROOT . '/' . $field->getAttribute('default'));
				}

				if ($fieldname == 'upload_gallery_watermark_image') {
					$data['upload_gallery_watermark_image'] = str_replace('\\', '/', JPATH_ROOT . $field->getAttribute('default'));
				}
			}
		}

		$data['use_alphabet'] = 0;

		$params = json_encode($data);

		$db->setQuery("UPDATE " . $db->quoteName('#__extensions')
			. "\n SET `params` = '" . $db->escape($params) . "'"
			. "\n WHERE `element` = 'com_kinoarhiv' AND `type` = 'component'");
		$result = $db->execute();

		$parent->getParent()->setRedirectURL('index.php?option=com_kinoarhiv');
	}

	function update($parent)
	{
		$db = JFactory::getDBO();
		$params = $this->getParams();

		/* Load the config.xml file on update and compare current existing parameters from DB
		 * with the parameters from file and add new into array and store in DB.
		*/
		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/');
		$config = JForm::getInstance('com_kinoarhiv.config', 'config', array('control' => 'jform', 'load_data' => array()), true, '/config');

		if (empty($config)) {
			throw new Exception('Cannot load the config.xml file!');
		}

		// Get the fieldset names
		$name_fieldsets = array();
		foreach ($config->getFieldsets() as $fieldset) {
			$name_fieldsets[] = $fieldset->name;
		}

		foreach ($name_fieldsets as $fieldset_name) {
			foreach ($config->getFieldset($fieldset_name) as $field) {
				$fieldname = $field->getAttribute('name');

				// Add new parameter only if it's not exists in current component parameters
				if (!array_key_exists($fieldname, $params)) {
					$params[$fieldname] = $field->getAttribute('default');
				}
			}
		}

		$params = json_encode($params);

		$db->setQuery("UPDATE " . $db->quoteName('#__extensions')
			. "\n SET `params` = '" . $db->escape($params) . "'"
			. "\n WHERE `element` = 'com_kinoarhiv' AND `type` = 'component'");
		$result = $db->execute();
		/* End of loading and updating component parameters */

		// Run DB update if installed version lower than 3.0.6
		if (version_compare($parent->get('manifest')->version, '3.0.5', '>') && version_compare($parent->get('manifest')->version, '3.0.6', '=')) {
			$parent->getParent()->setRedirectURL('index.php?option=com_kinoarhiv&controller=update&version=306');

			return true;
		}

		$parent->getParent()->setRedirectURL('index.php?option=com_kinoarhiv');
	}

	function getParams($name = '')
	{
		$db = JFactory::getDBO();

		$db->setQuery("SELECT `params` FROM `#__extensions` WHERE `type` = 'component' AND `name` = 'Kinoarhiv'");
		$params = json_decode($db->loadResult(), true);

		return !empty($name) ? $params[$name] : $params;
	}
}
