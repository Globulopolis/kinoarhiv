<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewProfile extends JViewLegacy {
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		if ($user->get('guest')) {
			$err = JText::_('JGLOBAL_AUTH_ACCESS_DENIED').'. '.JText::_('JERROR_ALERTNOAUTHOR');
			$app->enqueueMessage($err, 'error');
			GlobalHelper::eventLog($err);
			return false;
		}

		$this->page = $app->input->get('page', '', 'cmd');
		$this->tab = $app->input->get('tab', 'movies', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		switch ($this->page) {
			case 'reviews': $this->reviews(); break;
			case 'favorite': $this->favorite(); break;
			case 'watched': $this->watched(); break;
			case 'votes': $this->votes(); break;
			default:
				parent::display($tpl);
				break;
		}
	}

	protected function favorite() {
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($this->tab == '' || $this->tab == 'movies') {
			foreach ($items as &$item) {
				$item->year_str = ($item->year != '0000') ? '&nbsp;('.$item->year.')' : '';
			}
		} elseif ($this->tab == 'names') {
			foreach ($items as &$item) {
				$item->title = '';
				if ($item->name != '') {
					$item->title .= $item->name;
				}

				if ($item->name != '' && $item->latin_name != '') {
					$item->title .= ' / ';
				}

				if ($item->latin_name != '') {
					$item->title .= $item->latin_name;
				}

				$item->year_str = ($item->date_of_birth != '0000-00-00') ? '&nbsp;('.JHTML::_('date', $item->date_of_birth).')' : '';
			}
		}

		$this->params = &$params;
		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->lang = &$lang;

		$pathway = $app->getPathway();

		if ($this->page == '') {
			$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_FAVORITE'));

			$pathway->addItem(JText::_('COM_KA_FAVORITE'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid='.$this->itemid));
		} elseif ($this->page == 'movies') {
			$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_FAVORITE').' - '.JText::_('COM_KA_MOVIES'));

			$pathway->addItem(JText::_('COM_KA_FAVORITE'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid='.$this->itemid));
			$pathway->addItem(JText::_('COM_KA_MOVIES'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=movies&Itemid='.$this->itemid));
		} elseif ($this->page == 'names') {
			$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_FAVORITE').' - '.JText::_('COM_KA_PERSONS'));

			$pathway->addItem(JText::_('COM_KA_FAVORITE'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid='.$this->itemid));
			$pathway->addItem(JText::_('COM_KA_PERSONS'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=names&Itemid='.$this->itemid));
		}

		parent::display('favorite');
	}

	protected function watched() {
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		foreach ($items as &$item) {
			$item->year_str = ($item->year != '0000') ? '&nbsp;('.$item->year.')' : '';
		}

		$this->params = &$params;
		$this->items = &$items;
		$this->pagination = &$pagination;
		$this->lang = &$lang;

		$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_WATCHED'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_WATCHED'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=watched&Itemid='.$this->itemid));

		parent::display('watched');
	}

	protected function votes() {
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		foreach ($items as &$item) {
			$item->year_str = ($item->year != '0000') ? '&nbsp;('.$item->year.')' : '';

			if (!empty($item->rate_sum_loc) && !empty($item->rate_loc)) {
				$item->rate_loc = round($item->rate_sum_loc / $item->rate_loc, (int)$params->get('vote_summ_precision'));
				$item->rate_loc_label = $item->rate_loc.' '.JText::_('COM_KA_FROM').' '.(int)$params->get('vote_summ_num');
			} else {
				$item->rate_loc = 0;
				$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
			}
		}

		$this->params = &$params;
		$this->items = &$items;
		$this->pagination = &$pagination;

		$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_PROFILE_VOTES'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_PROFILE_VOTES'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid='.$this->itemid));

		parent::display('votes');
	}

	protected function reviews() {
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		foreach ($items as &$item) {
			$item->year_str = ($item->year != '0000') ? '&nbsp;('.$item->year.')' : '';
			$item->ip = !empty($item->ip) ? $item->ip : JText::_('COM_KA_REVIEWS_IP_NULL');
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$this->params = &$params;
		$this->items = &$items;
		$this->pagination = &$pagination;

		$this->document->setTitle($this->document->getTitle().' - '.JText::_('COM_KA_REVIEWS'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_REVIEWS'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&Itemid='.$this->itemid));

		parent::display('reviews');
	}
}
