<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Kinoarhiv.
 *
 * @since  3.1
 */
class JFormFieldSubtitleLanguage extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $type = 'SubtitleLanguage';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		jimport('administrator.components.com_kinoarhiv.libraries.language', JPATH_ROOT);

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), KALanguage::listOfLanguages());

		return $options;
	}
}
