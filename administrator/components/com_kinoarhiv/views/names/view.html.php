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

/**
 * View class for list of names.
 *
 * @since  3.0
 */
class KinoarhivViewNames extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
		}

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
		$user = JFactory::getUser();

		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE')), 'users');

		if ($user->authorise('core.create', 'com_kinoarhiv'))
		{
			JToolbarHelper::addNew('names.add');
		}

		if ($user->authorise('core.edit', 'com_kinoarhiv'))
		{
			JToolbarHelper::editList('names.edit');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JToolbarHelper::publishList('names.publish');
			JToolbarHelper::unpublishList('names.unpublish');
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv'))
		{
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'names.remove');
		}

		JToolbarHelper::divider();

		if ($user->authorise('core.create', 'com_kinoarhiv')
			&& $user->authorise('core.edit', 'com_kinoarhiv')
			&& $user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			$title = JText::_('JTOOLBAR_BATCH');
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
		}
	}
}
