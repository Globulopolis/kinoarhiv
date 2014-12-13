<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivViewAwards extends JViewLegacy {
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$id = $app->input->get('id', null, 'int');

		if (!empty($id)) {
			$this->award();
		} else {
			$this->awards();
		}
	}

	protected function awards() {
		$app = JFactory::getApplication();

		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		$this->params = &$params;
		$this->items = &$items;
		$this->pagination = &$pagination;

		$this->_prepareDocument();

		parent::display();
	}

	protected function award() {
		$app = JFactory::getApplication();

		$item = $this->get('Item');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors), 'ui');
			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		$this->params = &$params;
		$this->item = &$item;

		$this->_prepareDocument();

		parent::display('award');
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = '';
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = JText::_('COM_KA_AWARDS_TITLE');
		// Create a new pathway object
		$path = (object)array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=awards&Itemid='.$this->itemid
		);

		$pathway->setPathway(array($path));
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
