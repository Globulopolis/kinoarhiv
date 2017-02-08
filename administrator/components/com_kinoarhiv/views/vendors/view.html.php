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

class KinoarhivViewVendors extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task)
		{
			case 'add':
				$this->edit($tpl);
				break;
			case 'edit':
				$this->edit($tpl);
				break;
			default:
				$this->_display($tpl);
				break;
		}
	}

	protected function _display($tpl)
	{
		$user = JFactory::getUser();

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

		$this->canEdit = $user->authorise('core.edit.vendor', 'com_kinoarhiv');
		$this->canEditState = $user->authorise('core.edit.state.vendor', 'com_kinoarhiv');

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if (!$user->authorise('core.create.vendor', 'com_kinoarhiv') && !$user->authorise('core.edit.vendor', 'com_kinoarhiv'))
		{
			throw new Exception(JText::_('COM_KA_NO_ACCESS_RIGHTS'), 403);
		}

		$this->form = $this->get('Form');

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task = '')
	{
		$user = JFactory::getUser();

		if ($task == 'add')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_VENDORS_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'basket');
			JToolbarHelper::apply('vendors.apply');
			JToolbarHelper::save('vendors.save');
			JToolbarHelper::save2new('vendors.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('vendors.cancel');
		}
		elseif ($task == 'edit')
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_VENDORS_TITLE') . ': ' . $this->form->getValue('company_name')), 'basket');
			JToolbarHelper::apply('vendors.apply');
			JToolbarHelper::save('vendors.save');
			JToolbarHelper::save2new('vendors.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('vendors.cancel');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_VENDORS_TITLE')), 'basket');

			if ($user->authorise('core.create.vendor', 'com_kinoarhiv'))
			{
				JToolbarHelper::addNew('vendors.add');
			}

			if ($user->authorise('core.edit.vendor', 'com_kinoarhiv'))
			{
				JToolbarHelper::editList('vendors.edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state.vendor', 'com_kinoarhiv'))
			{
				JToolbarHelper::publishList('vendors.publish');
				JToolbarHelper::unpublishList('vendors.unpublish');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.delete.vendor', 'com_kinoarhiv'))
			{
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'vendors.remove');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.create.vendor', 'com_kinoarhiv')
				&& $user->authorise('core.edit.vendor', 'com_kinoarhiv')
				&& $user->authorise('core.edit.state.vendor', 'com_kinoarhiv'))
			{
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}
}
