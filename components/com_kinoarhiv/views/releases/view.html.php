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

use Joomla\Registry\Registry;

/**
 * Releases View class
 *
 * @since  3.0
 */
class KinoarhivViewReleases extends JViewLegacy
{
	protected $itemid = null;

	protected $items = null;

	protected $pagination = null;

	protected $params;

	/**
	 * The menu object
	 *
	 * @var    JMenuItem
	 * @since  3.1
	 */
	protected $menu;

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
		$app        = JFactory::getApplication();
		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$menu       = $app->getMenu()->getActive();
		$itemType   = (int) $menu->getParams()->get('item_type');
		$this->menu = $menu;
		$menuParams = new Registry;

		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->params = $mergedParams;

		if ($itemType === 0)
		{
			$this->displayMovies($tpl);
		}
		elseif ($itemType === 1)
		{
			$this->displayAlbums($tpl);
		}
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed|void
	 *
	 * @since  3.0
	 */
	public function displayMovies($tpl = null)
	{
		$this->user       = JFactory::getUser();
		$app              = JFactory::getApplication();
		$this->lang       = JFactory::getLanguage();
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->itemid     = $app->input->get('Itemid', 0, 'int');
		$this->pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Get proper itemid for &view=names&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');
		$this->moviesItemid = KAContentHelper::getItemid('movies');

		// Prepare the data
		foreach ($this->items as $item)
		{
			$item->attribs = json_decode($item->attribs);

			// Replace country BB-code
			$item->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches)
			{
				$html = JText::_($matches[1]);

				$cn = preg_replace(
					'#\[cn=(.+?)\](.+?)\[/cn\]#',
					'<img src="media/com_kinoarhiv/images/icons/countries/$1.png" alt="$2" class="ui-icon-country" /> $2',
					$matches[2]
				);

				return $html . $cn;
			},
				$item->text
			);

			// Replace genres BB-code
			$item->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
				$item->text
			);

			// Replace person BB-code
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid)
			{
				$html = JText::_($matches[1]);

				$name = preg_replace(
					'#\[name=(.+?)\](.+?)\[/name\]#',
					'<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $namesItemid, false) . '" title="$2">$2</a>',
					$matches[2]
				);

				return $html . $name;
			},
				$item->text
			);

			$item->poster = KAContentHelper::getMoviePoster($item, $this->params);
			$item->plot = JHtml::_('string.truncate', $item->plot, $this->params->get('limit_text'));

			if ($this->params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
				{
					$plural = $this->lang->getPluralSuffixes($item->rate_loc);
					$item->rate_loc_value = round($item->rate_sum_loc / $item->rate_loc, (int) $this->params->get('vote_summ_precision'));
					$item->rate_loc_label = JText::sprintf(
						'COM_KA_RATE_LOCAL_' . $plural[0],
						$item->rate_loc_value,
						(int) $this->params->get('vote_summ_num'),
						$item->rate_loc
					);
					$item->rate_loc_label_class = ' has-rating';
				}
				else
				{
					$item->rate_loc_value = 0;
					$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
					$item->rate_loc_label_class = ' no-rating';
				}
			}

			$item->event = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=release&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.releases', &$item, &$this->params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.releases', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.releases', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.releases', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->view   = $app->input->getWord('view');

		$this->prepareDocument();

		parent::addTemplatePath(JPath::clean(JPATH_COMPONENT . '/views/movies/tmpl'));
		parent::display($tpl);
	}

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed|void
	 *
	 * @since  3.0
	 */
	public function displayAlbums($tpl = null)
	{
		$this->user        = JFactory::getUser();
		$app               = JFactory::getApplication();
		$lang              = JFactory::getLanguage();
		$this->filtersData = $this->get('FiltersData');
		$this->items       = $this->get('Items');
		$pagination        = $this->get('Pagination');
		$this->itemid      = $app->input->get('Itemid', 0, 'int');
		$pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Get proper itemid for &view=?&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');
		$this->albumsItemid = KAContentHelper::getItemid('albums');

		$introtextLinks = $this->params->get('introtext_links', 1);

		// Prepare the data
		foreach ($this->items as $item)
		{
			$item->attribs = json_decode($item->attribs);
			$item->cover   = KAContentHelper::getAlbumCover($item, $this->params);

			// Replace genres BB-code
			$item->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
				$item->text
			);

			// Replace person BB-code
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid, $introtextLinks)
			{
				$html = JText::_($matches[1]) . ': ';

				if ($introtextLinks)
				{
					$name = preg_replace(
						'#\[name=(.+?)\](.+?)\[/name\]#',
						'<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $namesItemid, false) . '" title="$2">$2</a>',
						$matches[2]
					);
				}
				else
				{
					$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '$2', $matches[2]);
				}

				return $html . $name . '<br/>';
			},
				$item->text
			);

			if ($this->params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum) && !empty($item->rate))
				{
					$plural = $lang->getPluralSuffixes($item->rate);
					$item->rate_value = round($item->rate_sum / $item->rate, (int) $this->params->get('vote_summ_precision'));
					$item->rate_label = JText::sprintf(
						'COM_KA_RATE_LOCAL_' . $plural[0],
						$item->rate_value,
						(int) $this->params->get('vote_summ_num'),
						$item->rate
					);
					$item->rate_label_class = ' has-rating';
				}
				else
				{
					$item->rate_value = 0;
					$item->rate_label = JText::_('COM_KA_RATE_NO');
					$item->rate_label_class = ' no-rating';
				}
			}

			$item->event  = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$this->params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.albums', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.albums', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.albums', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->pagination = $pagination;
		$this->lang       = $lang;
		$this->view       = $app->input->getWord('view');

		$this->prepareDocument();

		parent::addTemplatePath(JPath::clean(JPATH_COMPONENT . '/views/albums/tmpl'));
		parent::display($tpl);
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
		$app     = JFactory::getApplication();
		$pathway = $app->getPathway();
		$title   = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_RELEASES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=releases&Itemid=' . $this->itemid
		);

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
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
