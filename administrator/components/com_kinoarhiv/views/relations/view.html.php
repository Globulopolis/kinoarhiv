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

class KinoarhivViewRelations extends JViewLegacy
{
	protected $form;

	protected $task = null;

	protected $user;

	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->user = JFactory::getUser();
		$this->task = $app->input->get('task', '', 'cmd');
		$this->id = $app->input->get('id', 0, 'int');
		$this->movie_id = $app->input->get('mid', 0, 'int');
		$this->name_id = $app->input->get('nid', 0, 'int');
		$this->element = $app->input->get('element', 'movies', 'word');

		switch ($this->task)
		{
			case 'countries':
				JToolbarHelper::custom('menu', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
				$this->countries($tpl);
				break;
			case 'genres':
				JToolbarHelper::custom('menu', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
				$this->genres($tpl);
				break;
			case 'awards':
				JToolbarHelper::custom('menu', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
				$this->awards($tpl);
				break;
			case 'careers':
				JToolbarHelper::custom('menu', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);
				$this->careers($tpl);
				break;
			case 'add':
			case 'edit':
				$this->edit();
				break;
			default:
				JToolbarHelper::title(JText::_('COM_KINOARHIV_CP') . ': ' . JText::_('COM_KA_TABLES_RELATIONS'), 'link');
				JToolbarHelper::custom('menu', 'link', 'link', JText::_('COM_KA_TABLES_RELATIONS'), false);

				parent::display($tpl);
				break;
		}
	}

	protected function countries($tpl)
	{
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id))
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_COUNTRIES_TITLE') . ': ID ' . $id), 'link');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_COUNTRIES_TITLE')), 'link');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('countries', 'location', 'location', JText::_('COM_KA_COUNTRIES_TITLE'), false);

		parent::display($tpl);
	}

	protected function genres($tpl)
	{
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id))
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_GENRES_TITLE') . ': ID ' . $id), 'link');
		}
		else
		{
			if ($app->input->get('mid', 0, 'int') != 0)
			{
				$title = ' - ' . JText::_('COM_KA_MOVIES_TITLE');
			}
			elseif ($app->input->get('nid', 0, 'int') != 0)
			{
				$title = ' - ' . JText::_('COM_KA_NAMES_TITLE');
			}
			else
			{
				if ($app->input->get('element', 'movies', 'word') == 'movies')
				{
					$title = ' - ' . JText::_('COM_KA_MOVIES_TITLE');
				}
				elseif ($app->input->get('element', 'movies', 'word') == 'names')
				{
					$title = ' - ' . JText::_('COM_KA_NAMES_TITLE');
				}
				else
				{
					$title = '';
				}
			}

			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_GENRES_TITLE') . $title), 'link');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('genres', 'smiley-2', 'smiley-2', JText::_('COM_KA_GENRES_TITLE'), false);

		parent::display($tpl);
	}

	protected function awards($tpl)
	{
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');
		$award_type = $app->input->get('award_type', 0, 'int');

		if ($award_type == 0)
		{
			$award_type_title = JText::_('COM_KA_AW_RELATION_TO_MOVIES');
		}
		elseif ($award_type == 1)
		{
			$award_type_title = JText::_('COM_KA_AW_RELATION_TO_NAMES');
		}
		else
		{
			$award_type_title = '';
		}

		if (!empty($id))
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE') . ': ' . JText::_('COM_KA_TABLES_RELATIONS') . $award_type_title . ': ID ' . $id), 'link');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_AWARDS_TITLE') . ': ' . JText::_('COM_KA_TABLES_RELATIONS') . $award_type_title), 'link');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('awards', 'asterisk', 'asterisk', JText::_('COM_KA_AWARDS_TITLE'), false);

		$this->award_type = $award_type;

		parent::display($tpl);
	}

	protected function careers($tpl)
	{
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		if (!empty($id))
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_CAREERS_TITLE')) . ': ID ' . $id, 'link');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_CAREERS_TITLE')), 'link');
		}

		JToolbarHelper::addNew('relations_add');
		JToolbarHelper::custom('relations_edit', 'edit', 'edit', JText::_('JTOOLBAR_EDIT'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('relations_remove', 'delete', 'delete', JText::_('JTOOLBAR_REMOVE'), false);
		JToolbarHelper::divider();
		JToolbarHelper::custom('careers', 'address', 'address', JText::_('COM_KA_CAREERS_TITLE'), false);

		parent::display($tpl);
	}

	protected function edit()
	{
		$app = JFactory::getApplication();
		$param = $app->input->get('param', '', 'cmd');

		JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_TABLES_RELATIONS') . ': ' . JText::_('COM_KA_' . strtoupper($param) . '_TITLE') . ': ' . JText::_('COM_KA_EDIT')), 'link');
		JToolbarHelper::apply('apply');
		JToolbarHelper::save('save');
		JToolbarHelper::save2new('save2new');
		JToolbarHelper::divider();
		JToolbarHelper::cancel();

		$this->form = $this->get('Form');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->param = $param;

		// We need a custom edit template for awards
		if ($param == 'awards')
		{
			$award_type = $app->input->get('award_type', 0, 'int');
			$this->award_type = $award_type;

			parent::display('relations_edit_awards');
			$app->input->set('hidemainmenu', true);
		}
		else
		{
			parent::display('relations_edit');
			$app->input->set('hidemainmenu', true);
		}
	}
}
