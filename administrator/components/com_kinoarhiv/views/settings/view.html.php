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

/**
 * View class for component settings.
 *
 * @since  3.0
 */
class KinoarhivViewSettings extends JViewLegacy
{
	protected $form;

	protected $data;

	protected $user;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->user = JFactory::getUser();
		$this->lang = JFactory::getLanguage();
		$this->form = $this->get('Form');
		$this->data = $this->get('Settings');
		$errors     = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->form && $this->data->params)
		{
			$this->form->bind($this->data->params);
		}

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->return = JFactory::getApplication()->input->get('return', '', 'base64');
		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_SETTINGS_TITLE')), 'options');
		JToolbarHelper::apply('settings.apply');
		JToolbarHelper::save('settings.save');
		JToolbarHelper::divider();
		JToolbarHelper::custom('settings.saveConfig', 'download', 'download', 'COM_KA_SETTINGS_BUTTON_SAVECONFIG', false);
		JToolbarHelper::custom('restoreConfigLayout', 'upload', 'upload', 'COM_KA_SETTINGS_BUTTON_RESTORECONFIG', false);
		JToolbarHelper::divider();
		JToolbarHelper::cancel('settings.cancel', 'JTOOLBAR_CLOSE');
		JToolbarHelper::divider();
	}
}
