<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivViewSettings extends JViewLegacy
{
	protected $form;
	protected $data;

	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_kinoarhiv')) {
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$this->lang = JFactory::getLanguage();
		$this->form = $this->get('Form');
		$this->data = $this->get('Settings');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->form && $this->data->params) {
			$this->form->bind($this->data->params);
		}

		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->return = $app->input->get('return', '', 'base64');
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()
	{
		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_SETTINGS_TITLE')), 'options');
		JToolbarHelper::apply('apply');
		JToolbarHelper::save('save');
		JToolbarHelper::divider();
		JToolbarHelper::custom('saveConfig', 'download', 'download', 'COM_KA_SETTINGS_BUTTON_SAVECONFIG', false);
		JToolbarHelper::custom('restoreConfigLayout', 'upload', 'upload', 'COM_KA_SETTINGS_BUTTON_RESTORECONFIG', false);
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel');
		JToolbarHelper::divider();
	}
}
