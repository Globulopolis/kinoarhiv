<?php defined('_JEXEC') or die;

class KinoarhivViewGenres extends JViewLegacy {
	protected $state = null;
	protected $items = null;
	protected $pagination = null;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();

		$state = $this->get('State');
		$items = $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
			return false;
		}

		$params = $app->getParams('com_kinoarhiv');
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
		$title = null;
		$menu = $menus->getActive();
		$pathway = $app->getPathway();
		$view = $app->input->get('view', 'movies', 'CMD');

		if ($menu) {
			$this->document->setTitle($menu->title);
		} else {
			$this->document->setTitle($this->params->get('page_title').' - '.JText::_('COM_KA_GENRES'));
		}

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
			$link = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
