<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Profile View class
 *
 * @since  3.0
 */
class KinoarhivViewProfile extends JViewLegacy
{
	protected $itemid = null;

	/**
	 * The items details
	 *
	 * @var    JObject
	 * @since  1.6
	 */
	protected $items = null;

	protected $pagination = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		if ($user->get('guest'))
		{
			$err = JText::_('JGLOBAL_AUTH_ACCESS_DENIED') . '. ' . JText::_('JERROR_ALERTNOAUTHOR');
			$app->enqueueMessage($err, 'error');
			KAComponentHelper::eventLog($err);

			return;
		}

		$this->page = $app->input->get('page', '', 'cmd');
		$this->tab = $app->input->get('tab', 'movies', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		switch ($this->page)
		{
			case 'reviews':
				$this->reviews();
				break;
			case 'favorite':
				$this->favorite();
				break;
			case 'watched':
				$this->watched();
				break;
			case 'votes':
				$this->votes();
				break;
			default:
				parent::display($tpl);
				break;
		}
	}

	protected function favorite()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$pathway = $app->getPathway();

		if ($this->tab == 'movies')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_MOVIES'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MOVIES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=movies&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'names')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_PERSONS'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_PERSONS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=names&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'albums')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_MUSIC_ALBUMS'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MUSIC_ALBUMS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=albums&Itemid=' . $this->itemid)
			);
		}

		parent::display('favorite_' . $this->tab);
	}

	protected function watched()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_WATCHED'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_WATCHED'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=watched&Itemid=' . $this->itemid));

		parent::display('watched');
	}

	protected function votes()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->lang = JFactory::getLanguage();
		$pathway = $app->getPathway();

		if ($this->tab == 'movies')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_PROFILE_VOTES') . ' - ' . JText::_('COM_KA_MOVIES'));

			$pathway->addItem(
				JText::_('COM_KA_PROFILE_VOTES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MOVIES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=movies&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'albums')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_PROFILE_VOTES') . ' - ' . JText::_('COM_KA_MUSIC_ALBUMS'));

			$pathway->addItem(
				JText::_('COM_KA_PROFILE_VOTES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MUSIC_ALBUMS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=albums&Itemid=' . $this->itemid)
			);
		}

		parent::display('votes_' . $this->tab);
	}

	protected function reviews()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_REVIEWS'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_REVIEWS'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&Itemid=' . $this->itemid));

		parent::display('reviews');
	}
}
