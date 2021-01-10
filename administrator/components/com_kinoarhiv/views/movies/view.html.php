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
 * View class for list of movies.
 *
 * @since  3.0
 */
class KinoarhivViewMovies extends JViewLegacy
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

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors')))
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

		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MOVIES_TITLE')), 'play');

		if ($user->authorise('core.create', 'com_kinoarhiv'))
		{
			JToolbarHelper::addNew('movies.add');
		}

		if ($user->authorise('core.edit', 'com_kinoarhiv'))
		{
			JToolbarHelper::editList('movies.edit');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JToolbarHelper::publishList('movies.publish');
			JToolbarHelper::unpublishList('movies.unpublish');
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv'))
		{
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'movies.remove');
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

		if ($user->authorise('core.admin', 'com_kinoarhiv') || $user->authorise('core.options', 'com_kinoarhiv'))
		{
			$uri = (string) JUri::getInstance();
			$return = urlencode(base64_encode($uri));

			// Add a button linking to config for component.
			JToolbar::getInstance('toolbar')->appendButton(
				'Link',
				'options',
				'JToolbar_Options',
				'index.php?option=com_kinoarhiv&amp;view=settings&amp;return=' . $return
			);
		}
	}
}
