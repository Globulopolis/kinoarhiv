<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

@set_time_limit(0);

class com_kinoarhivInstallerScript {
	public function postflight($type, $parent) {
		$db = JFactory::getDBO();

		JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_kinoarhiv/');
		$form = JForm::getInstance('com_kinoarhiv.config', 'config', array('control' => 'jform', 'load_data' => array()), true, '/config');

		if (empty($form)) {
			throw new Exception('Cannot load the config.xml form!');
			return false;
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
					$data[$_field_root] = str_replace('\\', '/', JPATH_ROOT.$field->getAttribute('default'));
					$data[$fieldname] = $field->getAttribute('default');
				}

				// Process sys paths
				if ($fieldname == 'ffmpeg_path' || $fieldname == 'ffprobe_path') {
					$data[$fieldname] = str_replace('\\', '/', JPATH_ROOT.'/'.$field->getAttribute('default'));
				}

				if ($fieldname == 'def_cache') {
					$data['def_cache'] = str_replace('\\', '/', JPATH_ROOT.'/'.$field->getAttribute('default'));
				}

				if ($fieldname == 'upload_gallery_watermark_image') {
					$data['upload_gallery_watermark_image'] = str_replace('\\', '/', JPATH_ROOT.$field->getAttribute('default'));
				}
			}
		}

		$data['use_alphabet'] = 0;

		$params = json_encode($data);

		$db->setQuery("UPDATE ".$db->quoteName('#__extensions')
			. "\n SET `params` = '".$db->escape($params)."'"
			. "\n WHERE `element` = 'com_kinoarhiv' AND `type` = 'component'");
		$result = $db->execute();

		JFactory::getApplication()->redirect('index.php?option=com_kinoarhiv');
	}
}
