<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

class KinoarhivViewNames extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;
	protected $params;
	protected $form_edit_group;
	protected $form_attribs_group;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task) {
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
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->filterForm = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
		}

		parent::display($tpl);
	}

	protected function edit($tpl)
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$form = $this->get('Form');
		$items = new Registry;

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		// Build title
		$title = '';
		if ($form->getValue('name', 'name') != '') {
			$title .= $form->getValue('name', 'name');
		}
		if ($form->getValue('name', 'name') != '' && $form->getValue('latin_name', 'name') != '') {
			$title .= ' / ';
		}
		if ($form->getValue('latin_name', 'name') != '') {
			$title .= $form->getValue('latin_name', 'name');
		}
		$items->set('title', $title);

		if ($form->getValue('filename', 'name') == '') {
			$items->set(
				'poster',
				JURI::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JURI::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
			$items->set('y_poster', '');
		} else {
			$alias = substr($form->getValue('alias', 'name'), 0, 1);
			$item_id = $form->getValue('id', 'name');
			$poster = $form->getValue('filename', 'name');

			if (substr($params->get('media_actor_photo_root_www'), 0, 1) == '/') {
				$items->set(
					'poster',
					JURI::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/' . $alias . '/' . $item_id . '/photo/' . $poster
				);
				$items->set(
					'th_poster',
					JURI::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/' . $alias . '/' . $item_id . '/photo/thumb_' . $poster
				);
			} else {
				$items->set(
					'poster',
					$params->get('media_actor_photo_root_www') . '/' . $alias . '/' . $item_id . '/photo/' . $poster
				);
				$items->set(
					'th_poster',
					$params->get('media_actor_photo_root_www') . '/' . $alias . '/' . $item_id . '/photo/thumb_' . $poster
				);
			}
			$items->set('y_poster', 'y-poster');
		}

		$this->items = $items;
		$this->form = $form;
		$this->form_edit_group = 'name';
		$this->form_attribs_group = 'attribs';
		$this->params = $params;

		if ($this->getLayout() !== 'modal') {
			$this->addToolbar($tpl);
		}

		parent::display('edit');
		$app->input->set('hidemainmenu', true);
	}

	protected function addToolbar($task = '')
	{
		$user = JFactory::getUser();

		if ($task == 'add') {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
		} elseif ($task == 'edit') {
			if ($this->form->getValue('id', $this->form_edit_group) != 0) {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . $this->items->get('title')), 'play');
			} else {
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			}
			JToolbarHelper::apply('apply');
			JToolbarHelper::save('save');
			JToolbarHelper::save2new('save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel();
			JToolbarHelper::divider();
			JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);
		} else {
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE')), 'users');
			if ($user->authorise('core.create', 'com_kinoarhiv')) {
				JToolbarHelper::addNew('add');
			}

			if ($user->authorise('core.edit', 'com_kinoarhiv')) {
				JToolbarHelper::editList('edit');
				JToolbarHelper::divider();
			}

			if ($user->authorise('core.edit.state', 'com_kinoarhiv')) {
				JToolbarHelper::publishList();
				JToolbarHelper::unpublishList();
			}

			if ($user->authorise('core.delete', 'com_kinoarhiv')) {
				JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
			}

			JToolbarHelper::divider();
			JToolbarHelper::custom('menu', 'tools', 'tools', JText::_('COM_KA_TABLES_RELATIONS'), false);
			JToolbarHelper::divider();

			if ($user->authorise('core.create', 'com_kinoarhiv') && $user->authorise('core.edit', 'com_kinoarhiv') && $user->authorise('core.edit.state', 'com_kinoarhiv')) {
				JHtml::_('bootstrap.modal', 'collapseModal');
				$title = JText::_('JTOOLBAR_BATCH');
				$layout = new JLayoutFile('joomla.toolbar.batch');

				$dhtml = $layout->render(array('title' => $title));
				JToolBar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'batch');
			}
		}
	}

	protected function getSortFields()
	{
		return array(
			'a.state'      => JText::_('JSTATUS'),
			'a.name'       => JText::_('COM_KA_FIELD_NAME'),
			'a.latin_name' => JText::_('COM_KA_FIELD_NAME_LATIN'),
			'a.access'     => JText::_('JGRID_HEADING_ACCESS'),
			'language'     => JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id'         => JText::_('JGRID_HEADING_ID')
		);
	}
}
