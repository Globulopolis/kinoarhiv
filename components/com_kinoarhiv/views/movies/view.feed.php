<?php defined('_JEXEC') or die;

class KinoarhivViewMovies extends JViewLegacy {
	protected $items = null;
	protected $ka_theme;
	protected $itemid;

	public function display($tpl = null) {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$items = $this->get('Items');

		if (count($errors = $this->get('Errors'))) {
			GlobalHelper::eventLog(implode("\n", $errors));
			return false;
		}

		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$feedEmail = $app->getCfg('feed_email', 'author');
		$siteEmail = $app->getCfg('mailfrom');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$this->ka_theme = $params->get('ka_theme');

		$document->setTitle(JText::_('COM_KA_MOVIES'));
		$document->setDescription($params->get('meta_description'));
		$document->link = JRoute::_('index.php?option=com_kinoarhiv&view=movies&Itemid='.$this->itemid);
		if ($params->get('generator') == 'none') {
			$document->setGenerator('');
		} elseif ($params->get('generator') == 'site') {
			$document->setGenerator($document->getGenerator());
		} else {
			$document->setGenerator($params->get('generator'));
		}

		$app->input->set('limit', $app->getCfg('feed_limit'));

		// Prepare the data
		foreach ($items as &$row) {
			$year_str = ($row->year != '0000') ? ' ('.$row->year.')' : '';
			$title = $this->escape($row->title.$year_str);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$link = JRoute::_('index.php?option=com_kinoarhiv&view=movie&id='.$row->id.'&Itemid='.$this->itemid);

			$item = new JFeedItem;
			$item->title = $title;
			$item->link = $link;
			$item->author = ($row->attribs->show_author === '' && !empty($row->username)) ? $row->username : '';

			if ($feedEmail == 'site') {
				$item->authorEmail = $siteEmail;
			} elseif ($feedEmail === 'author') {
				$item->authorEmail = $row->author_email;
			}

			// Replace country BB-code
			$row->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches) {
				$html = JText::_($matches[1]);

				$cn = preg_replace('#\[cn=(.+?)\](.+?)\[/cn\]#', '<img src="'.JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$this->ka_theme.'/images/icons/countries/$1.png" border="0" alt="$2" class="ui-icon-country" /> $2', $matches[2]);

				return $html.$cn;
			}, $row->text);

			// Replace genres BB-code
			$row->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches) {
				return JText::_($matches[1]).$matches[2];
			}, $row->text);


			// Replace person BB-code
			$row->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) {
				$html = JText::_($matches[1]);

				$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '<a href="'.JRoute::_(JUri::base().'index.php?option=com_kinoarhiv&view=name&id=$1&Itemid='.$this->itemid).'" title="$2">$2</a>', $matches[2]);

				return $html.$name;
			}, $row->text);

			if (empty($row->filename)) {
				$row->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/no_movie_cover.png';
			} else {
				$row->poster = JURI::base().$params->get('media_posters_root_www').'/'.JString::substr($row->alias, 0, 1).'/'.$row->id.'/posters/thumb_'.$row->filename;
			}

			$row->plot = '<div class="feed-plot">'.JHtml::_('string.truncate', $row->plot, $params->get('limit_text')).'</div>';
			$item->description = '<div class="feed-description">
				<div class="poster"><img src="'.$row->poster.'" border="0" /></div>
				<div class="introtext">'.$row->text.$row->plot.'</div>
			</div>';

			$document->addItem($item);
		}
	}
}
