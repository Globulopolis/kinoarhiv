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
 * Albums feed View class
 *
 * @since  3.1
 */
class KinoarhivViewAlbums extends JViewLegacy
{
	/**
	 * Albums data object
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $items = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  false|void
	 *
	 * @since  3.1
	 */
	public function display($tpl = null)
	{
		$items = $this->get('Items');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors));

			return false;
		}

		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$params   = JComponentHelper::getParams('com_kinoarhiv');
		$itemid   = $app->input->get('Itemid', 0, 'int');

		$document->setTitle(JText::_('COM_KA_MUSIC_ALBUMS'));
		$document->setDescription($params->get('meta_description'));
		$document->link = JRoute::_('index.php?option=com_kinoarhiv&view=albums');

		if ($params->get('generator') == 'none')
		{
			$document->setGenerator('');
		}
		elseif ($params->get('generator') == 'site')
		{
			$document->setGenerator($document->getGenerator());
		}
		else
		{
			$document->setGenerator($params->get('generator'));
		}

		$app->input->set('limit', $app->get('feed_limit'));

		// Prepare the data
		foreach ($items as $row)
		{
			$title   = $this->escape(KAContentHelper::formatItemTitle($row->title, '', $row->year));
			$link    = JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $row->id . '&Itemid=' . $itemid);
			$attribs = json_decode($row->attribs);

			$item         = new JFeedItem;
			$item->title  = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$item->link   = $link;
			$item->author = ($attribs->show_author === '' && !empty($row->username)) ? $row->username : '';
			$checkingPath = JPath::clean($row->covers_path . '/' . $row->cover_filename);

			if (!is_file($checkingPath))
			{
				$row->cover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
			}
			else
			{
				$row->cover = $row->covers_path_www . '/' . $row->cover_filename;
			}

			$item->description = '<div class="feed-description">
				<div class="poster"><img src="' . $row->cover . '" alt="" /></div>
			</div>';

			/** @var $document JDocumentFeed */
			$document->addItem($item);
		}
	}
}
