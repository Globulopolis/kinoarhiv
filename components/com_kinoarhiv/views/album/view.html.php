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

use Joomla\String\StringHelper;
use Joomla\Registry\Registry;

/**
 * Movie View class
 *
 * @since  3.0
 */
class KinoarhivViewAlbum extends JViewLegacy
{
	/**
	 * The form object for the reviews item
	 *
	 * @var    JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The item object details
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $item;

	/**
	 * The items details
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $items;

	protected $filters = null;

	protected $pagination = null;

	/**
	 * Component config object
	 *
	 * @var    object
	 * @since  3.0
	 */
	protected $params;

	/**
	 * The Joomla config object
	 *
	 * @var    object
	 * @since  3.0
	 */
	protected $config;

	protected $user;

	protected $itemid;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed|void
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$user         = JFactory::getUser();
		$app          = JFactory::getApplication();
		$lang         = JFactory::getLanguage();
		$item         = $this->get('Data');
		$items        = $this->get('Items');
		$form         = $this->get('Form');
		$pagination   = $this->get('Pagination');
		$this->itemid = $app->input->get('Itemid');

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$config = JFactory::getConfig();
		$throttleEnable = $params->get('throttle_image_enable', 0);
		$checkingPath = JPath::clean($item->covers_path . '/' . $item->cover_filename);

		// Prepare the data
		// Workaround for plugin interaction. Article must contain $text item.
		$item->text = '';

		if ($throttleEnable == 0)
		{
			if (!is_file($checkingPath))
			{
				$item->cover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
				$dimension   = KAContentHelper::getImageSize(
					JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png',
					false
				);
				$item->coverWidth  = $dimension['width'];
				$item->coverHeight = $dimension['height'];
			}
			else
			{
				$item->cover = $item->covers_path_www . '/' . $item->cover_filename;
				$dimension   = KAContentHelper::getImageSize(
					$checkingPath,
					true,
					(int) $params->get('music_covers_size')
				);
				$item->coverWidth  = $dimension['width'];
				$item->coverHeight = $dimension['height'];
			}
		}
		else
		{
			$item->itemid = $this->itemid;
			$item->cover  = KAContentHelper::getAlbumCoverLink($item);
			$dimension    = KAContentHelper::getImageSize(
				$checkingPath,
				true,
				(int) $params->get('music_covers_size')
			);
			$item->coverWidth  = $dimension['width'];
			$item->coverHeight = $dimension['height'];
		}

		if (!empty($item->desc))
		{
			$item->desc = str_replace("\n", "<br />", $item->desc);
		}

		$item->_length = $item->length;
		list($hours, $minutes) = explode(':', $item->_length);
		$item->_hr_length = $hours * 60 + $minutes;

		if (!empty($item->rate_sum) && !empty($item->rate))
		{
			$plural = $lang->getPluralSuffixes($item->rate);
			$item->rate = round($item->rate_sum / $item->rate, (int) $params->get('vote_summ_precision'));
			$item->rate_label = JText::sprintf('COM_KA_RATE_LOCAL_' . $plural[0], $item->rate, (int) $params->get('vote_summ_num'));
			$item->rate_label_class = ' has-rating';
		}
		else
		{
			$item->rate = 0;
			$item->rate_label = JText::_('COM_KA_RATE_NO');
			$item->rate_label_class = ' no-rating';
		}

		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->config = $config;
		$this->item = $item;

		// Reviews
		$this->items = $items;

		$this->user = $user;
		$this->pagination = $pagination;
		$this->metadata = json_decode($item->metadata);
		$this->form = $form;
		$this->lang = $lang;

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display();
	}

	/**
	 * Method to get and show awards.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function awards()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Awards');

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_awards === '' && $params->get('tab_movie_awards') === '0') || $item->attribs->tab_movie_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Prepare the data
		$item->text = '';
		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_AWARDS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('awards');
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	protected function prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title && $menu->link == 'index.php?option=com_kinoarhiv&view=albums') ? $menu->title : JText::_('COM_KA_MOVIES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=albums&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$titleAdd = empty($this->page) ? '' : ' - ' . JText::_('COM_KA_MOVIE_TAB_' . StringHelper::ucwords($this->page));
		$this->document->setTitle(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year) . $titleAdd);

		if ($menu && $menu->params->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu && $menu->params->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu && $menu->params->get('robots') != '')
		{
			$this->document->setMetadata('robots', $menu->params->get('robots'));
		}
		else
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($this->params->get('generator') == 'none')
		{
			$this->document->setGenerator('');
		}
		elseif ($this->params->get('generator') == 'site')
		{
			$this->document->setGenerator($this->document->getGenerator());
		}
		else
		{
			$this->document->setGenerator($this->params->get('generator'));
		}
	}
}
