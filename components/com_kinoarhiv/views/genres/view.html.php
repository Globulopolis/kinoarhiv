<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewGenres extends JViewLegacy {
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();

		$items = $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		$this->params = &$params;
		$this->items = &$items;

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$title = ($menu && $menu->link == 'index.php?option=com_kinoarhiv&view=genres') ? JText::_('COM_KA_GENRES') : $menu->title;
		$view = $app->input->get('view', 'movies', 'CMD');

		$this->document->setTitle($title);

		if ($menu->params->get('menu-meta_description') != '') {
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		} else {
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu->params->get('menu-meta_keywords') != '') {
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		} else {
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu->params->get('robots') != '') {
			$this->document->setMetadata('robots', $menu->params->get('robots'));
		} else {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($this->params->get('generator') == 'none') {
			$this->document->setGenerator('');
		} elseif ($this->params->get('generator') == 'site') {
			$this->document->setGenerator($this->document->getGenerator());
		} else {
			$this->document->setGenerator($this->params->get('generator'));
		}

		// Add feed links
		if ($this->params->get('show_feed_link', 1)) {
			$link = 'index.php?option=com_kinoarhiv&view=movies&format=feed&Itemid='.$this->itemid.'&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
