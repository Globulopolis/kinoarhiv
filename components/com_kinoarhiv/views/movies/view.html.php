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
use Joomla\String\StringHelper;

/**
 * Movies View class
 *
 * @since  3.0
 */
class KinoarhivViewMovies extends JViewLegacy
{
	protected $items = null;

	protected $pagination = null;

	protected $params;

	protected $user;

	protected $itemid;

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
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$user              = JFactory::getUser();
		$app               = JFactory::getApplication();
		$params            = JComponentHelper::getParams('com_kinoarhiv');
		$lang              = JFactory::getLanguage();
		$this->filtersData = $this->get('FiltersData');
		$this->items       = $this->get('Items');
		$this->pagination  = $this->get('Pagination');
		$this->itemid      = $app->input->get('Itemid', 0, 'int');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$menus      = $app->getMenu();
		$menu       = $menus->getActive();
		$this->menu = $menu;
		$menuParams = new Registry;

		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->params = $mergedParams;

		// Get proper itemid for &view=?&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');
		$this->moviesItemid = $this->itemid;

		$itemid         = $this->itemid;
		$throttleEnable = $this->params->get('throttle_image_enable', 0);
		$introtextLinks = $this->params->get('introtext_links', 1);

		// Prepare the data
		foreach ($this->items as $item)
		{
			$item->attribs = json_decode($item->attribs);

			// Replace country BB-code
			$item->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches)
			{
				$html = JText::_($matches[1]);

				$cn = preg_replace('#\[cn=(.+?)\](.+?)\[/cn\]#', '<img src="media/com_kinoarhiv/images/icons/countries/$1.png" alt="$2" class="ui-icon-country" /> $2', $matches[2]);

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
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid, $introtextLinks)
			{
				$html = JText::_($matches[1]);

				if ($introtextLinks)
				{
					$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $namesItemid, false) . '" title="$2">$2</a>', $matches[2]);
				}
				else
				{
					$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '$2', $matches[2]);
				}

				return $html . $name;
			},
				$item->text
			);

			if ($throttleEnable == 0)
			{
				$checkingPath = JPath::clean(
					$this->params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename
				);

				if (!is_file($checkingPath))
				{
					$item->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png',
						false
					);
					$item->poster_width = $dimension['width'];
					$item->poster_height = $dimension['height'];
				}
				else
				{
					$item->fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($this->params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$item->poster = JUri::base() . StringHelper::substr($this->params->get('media_posters_root_www'), 1) . '/'
							. $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename;
					}
					else
					{
						$item->poster = $this->params->get('media_posters_root_www') . '/' . $item->fs_alias . '/' . $item->id . '/posters/thumb_' . $item->filename;
					}

					$dimension = KAContentHelper::getImageSize(
						$item->poster,
						true,
						(int) $this->params->get('size_x_posters'),
						$item->dimension
					);
					$item->poster_width = $dimension['width'];
					$item->poster_height = $dimension['height'];
				}
			}
			else
			{
				$item->poster = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $itemid . '&thumbnail=1'
				);
				$dimension = KAContentHelper::getImageSize(
					JUri::base() . $item->poster,
					true,
					(int) $this->params->get('size_x_posters'),
					$item->dimension
				);
				$item->poster_width = $dimension['width'];
				$item->poster_height = $dimension['height'];
			}

			$item->plot = JHtml::_('string.truncate', $item->plot, $this->params->get('limit_text'));

			if ($this->params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
				{
					$plural = $lang->getPluralSuffixes($item->rate_loc);
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

			$item->event  = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movies', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->user = $user;
		$this->lang = $lang;
		$this->view = $app->input->getWord('view');

		$this->prepareDocument();

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
		$app        = JFactory::getApplication();
		$pathway    = $app->getPathway();
		$menuParams = $this->menu->getParams();
		$title      = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_MOVIES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=movies&Itemid=' . $this->itemid
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

		if ($this->menu && $menuParams->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menuParams->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($this->menu && $menuParams->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($this->menu && $menuParams->get('robots') != '')
		{
			$this->document->setMetadata('robots', $menuParams->get('robots'));
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

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			$link = 'index.php?option=com_kinoarhiv&view=movies&Itemid=' . $this->itemid . '&format=feed';

			$this->document->addHeadLink(
				JRoute::_($link . '&type=rss'),
				'alternate',
				'rel',
				array('type' => 'application/rss+xml', 'title' => 'RSS 2.0')
			)->addHeadLink(
				JRoute::_($link . '&type=atom'),
				'alternate',
				'rel',
				array('type' => 'application/atom+xml', 'title' => 'Atom 1.0')
			);
		}
	}
}
