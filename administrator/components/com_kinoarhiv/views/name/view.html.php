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

use Joomla\Registry\Registry;

/**
 * View class for a name.
 *
 * @since  3.0
 */
class KinoarhivViewName extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$task = $app->input->get('task', '', 'cmd');

		switch ($task)
		{
			case 'editNameAwards':
				$this->editNameAwards('awards');
				break;
			default:
				$this->edit();
				break;
		}
	}

	/**
	 * Display the view for a name edit.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function edit()
	{
		jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$form   = $this->get('Form');
		$items  = new Registry;
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		// Set title
		$items->set('title', KAContentHelper::formatItemTitle($form->getValue('name'), $form->getValue('latin_name')));

		if (substr($params->get('media_actor_photo_root_www'), 0, 1) == '/')
		{
			$img_folder = JUri::root() . substr($params->get('media_actor_photo_root_www'), 1) . '/'
				. urlencode($form->getValue('fs_alias')) . '/' . $form->getValue('id') . '/photo/';
		}
		else
		{
			$img_folder = $params->get('media_actor_photo_root_www') . '/' . urlencode($form->getValue('fs_alias'))
				. '/' . $form->getValue('id') . '/photo/';
		}

		if ($form->getValue('filename') == '')
		{
			$items->set(
				'poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
			$items->set(
				'th_poster',
				JUri::root() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png'
			);
		}
		else
		{
			$items->set('poster', $img_folder . $form->getValue('filename'));
			$items->set('th_poster', $img_folder . 'thumb_' . $form->getValue('filename'));
		}

		$items->set('img_folder', $img_folder);
		$this->items = $items;
		$this->form = $form;
		$this->params = $params;

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar('edit');
		}

		parent::display();
		$app->input->set('hidemainmenu', true);
	}

	/**
	 * Display the view for an award edit.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   3.0
	 */
	protected function editNameAwards($tpl)
	{
		$this->form = $this->get('Form');
		$errors = $this->get('Errors');

		if (count($errors))
		{
			throw new Exception(implode("\n", $this->get('Errors')), 500);
		}

		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar('awards');
		}

		parent::display($tpl);
		JFactory::getApplication()->input->set('hidemainmenu', true);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   string  $task  Task
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function addToolbar($task = '')
	{
		if ($task == 'edit')
		{
			if ($this->form->getValue('id') != 0)
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . $this->items->get('title')), 'play');
			}
			else
			{
				JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			}

			JToolbarHelper::apply('names.apply');
			JToolbarHelper::save('names.save');
			JToolbarHelper::save2new('names.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel');
			JToolbarHelper::divider();
			JToolbarHelper::custom('gallery', 'picture', 'picture', JText::_('COM_KA_MOVIES_GALLERY'), false);

			$layout = new JLayoutFile('joomla.toolbar.modal');
			$dhtml = $layout->render(
				array('selector' => 'parserModal', 'text' => JText::_('COM_KA_PARSER_TOOLBAR_BUTTON'), 'icon' => 'database')
			);
			JToolbar::getInstance('toolbar')->appendButton('Custom', $dhtml, 'parser');
		}
		elseif ($task == 'awards')
		{
			if ($this->form->getValue('id') != 0)
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_MOVIES_AWARDS_LAYOUT_EDIT_TITLE')),
					'play'
				);
			}
			else
			{
				JToolbarHelper::title(
					JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_MOVIES_AW_LAYOUT_ADD_TITLE')),
					'play'
				);
			}

			JToolbarHelper::apply('names.saveNameAwards');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel', 'JTOOLBAR_CLOSE');
		}
		else
		{
			JToolbarHelper::title(JText::sprintf('COM_KINOARHIV', JText::_('COM_KA_NAMES_TITLE') . ': ' . JText::_('COM_KA_NEW')), 'play');
			JToolbarHelper::apply('names.apply');
			JToolbarHelper::save('names.save');
			JToolbarHelper::save2new('names.save2new');
			JToolbarHelper::divider();
			JToolbarHelper::cancel('names.cancel');
			JToolbarHelper::divider();
		}
	}
}
