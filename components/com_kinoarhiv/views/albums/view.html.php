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

/**
 * Albums View class
 *
 * @since  3.0
 */
class KinoarhivViewAlbums extends JViewLegacy
{
	protected $state = null;

	protected $items = null;

	protected $pagination = null;

	protected $filtersData = null;

	/**
	 * Component parameters object
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $params;

	protected $user;

	protected $itemid;

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
		$lang              = JFactory::getLanguage();
		$state             = $this->get('State');
		$this->filtersData = $this->get('FiltersData');
		$this->items       = $this->get('Items');
		$pagination        = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->menuParams = &$state->menuParams;
		$this->params     = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid     = $app->input->get('Itemid', 0, 'int');
		$itemid           = $this->itemid;
		$throttleEnable   = $this->params->get('throttle_image_enable', 0);

		// Prepare the data
		foreach ($this->items as $item)
		{
			$item->attribs = json_decode($item->attribs);
			$item->text    = '';
			$poster        = KAContentHelper::getAlbumCover($item);
			$item->poster  = $poster['th_poster'];
			$item->poster_width  = $poster['size']->width;
			$item->poster_height = $poster['size']->height;

			echo '<pre>';
			print_r(KAContentHelper::getAlbumCover($item));
			echo '</pre>';

			/*if ($throttleEnable == 0)
			{
				$checking_path = JPath::clean(
					$this->params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename
				);

				if (!is_file($checking_path))
				{
					$item->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png',
						false
					);
					$item->poster_width = $dimension->width;
					$item->poster_height = $dimension->height;
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
					$item->poster_width = $dimension->width;
					$item->poster_height = $dimension->height;
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
				$item->poster_width = $dimension->width;
				$item->poster_height = $dimension->height;
			}*/

			if ($this->params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum) && !empty($item->rate))
				{
					$plural = $lang->getPluralSuffixes($item->rate);
					$item->rate_loc_c = round($item->rate_sum / $item->rate, (int) $this->params->get('vote_summ_precision'));
					$item->rate_loc_label = JText::sprintf(
						'COM_KA_RATE_LOCAL_' . $plural[0], $item->rate_loc_c,
						(int) $this->params->get('vote_summ_num'), $item->rate
					);
					$item->rate_loc_label_class = ' has-rating';
				}
				else
				{
					$item->rate_loc_c = 0;
					$item->rate_loc_label = '<br />' . JText::_('COM_KA_RATE_NO');
					$item->rate_loc_label_class = ' no-rating';
				}
			}

			$item->event = new stdClass;
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
		$this->user       = $user;
		$this->lang       = $lang;

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
		$app     = JFactory::getApplication();
		$menu    = $app->getMenu()->getActive();
		$pathway = $app->getPathway();
		$title   = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_MUSIC_ALBUMS');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=albums&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

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

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			$link = 'index.php?option=com_kinoarhiv&view=albums&Itemid=' . $this->itemid . '&format=feed';

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
