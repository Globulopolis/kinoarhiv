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

class KinoarhivViewAlbums extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $params;

	/**
	 * The JForm object
	 *
	 * @var  JForm
	 *
	 * @since 3.0
	 */
	protected $form;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed|void  A string if successful, otherwise an Error object.
	 *
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   3.1
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		if ($app->input->get('task', '') === 'showScanFolderTemplate')
		{
			$this->displayScanTemplate($tpl);

			return;
		}

		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$this->params        = JComponentHelper::getParams('com_kinoarhiv');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$tpl                 = $app->input->get('type', 'albums', 'word');

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
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed|void  A string if successful, otherwise an Error object.
	 *
	 * @see     \JViewLegacy::loadTemplate()
	 * @since   3.1
	 */
	public function displayScanTemplate($tpl = null)
	{
		$this->form = $this->get('Form');

		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		JToolbarHelper::title(
			JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE')),
			'play'
		);

		JToolbar::getInstance()->appendButton(
			'Custom',
			'<button class="btn btn-small btn-success cmd-scan-form">
				<span class="icon-tree-2" aria-hidden="true"></span> ' . JText::_('COM_KA_SCAN_BUTTON') . '
			</button>',
			'base'
		);

		JToolbar::getInstance()->appendButton(
			'Custom',
			'<button class="btn btn-small btn-danger cmd-clear-temp">
				<span class="icon-remove" aria-hidden="true"></span> ' . JText::_('COM_KA_TMP_CLEAN_BUTTON') . '
			</button>',
			'base'
		);

		parent::display($tpl);
		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	protected function addToolbar()
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($app->input->get('type', 'albums', 'word') == 'albums')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_MUSIC_ALBUMS_TITLE')),
				'play'
			);
		}
		elseif ($app->input->get('type', 'albums', 'word') == 'tracks')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_MUSIC_TITLE') . ': ' . JText::_('COM_KA_MUSIC_TRACKS_TITLE')),
				'play'
			);
		}

		if ($user->authorise('core.create', 'com_kinoarhiv'))
		{
			JToolbarHelper::addNew('albums.add');
		}

		if ($user->authorise('core.edit', 'com_kinoarhiv'))
		{
			JToolbarHelper::editList('albums.edit');
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JToolbarHelper::publishList('albums.publish');
			JToolbarHelper::unpublishList('albums.unpublish');
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv'))
		{
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'albums.remove');
		}

		JToolbarHelper::divider();

		if ($user->authorise('core.create', 'com_kinoarhiv')
			&& $user->authorise('core.edit', 'com_kinoarhiv')
			&& $user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			$title = JText::_('JTOOLBAR_BATCH');
			$layout = new JLayoutFile('joomla.toolbar.batch');

			$dhtml = $layout->render(array('title' => $title));
			JToolbar::getInstance()->appendButton('Custom', $dhtml, 'batch');

			JToolbarHelper::custom('albums.showScanFolderTemplate', 'folder-open', 'folder-open', 'JTOOLBAR_IMPORT', false);
		}

		if ($user->authorise('core.admin', 'com_kinoarhiv') || $user->authorise('core.options', 'com_kinoarhiv'))
		{
			$uri = (string) JUri::getInstance();
			$return = urlencode(base64_encode($uri));

			// Add a button linking to config for component.
			JToolbar::getInstance()->appendButton(
				'Link',
				'options',
				'JToolbar_Options',
				'index.php?option=com_kinoarhiv&amp;view=settings&amp;return=' . $return
			);
		}
	}
}
