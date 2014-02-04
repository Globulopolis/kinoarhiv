<?php defined('_JEXEC') or die;

class KinoarhivModelMovie extends JModelForm {
	protected $cache = array();
	protected $context = null;
	protected $filter_fields = array();
	protected $query = array();

	public function __construct($config = array()) {
		parent::__construct($config);

		if (isset($config['filter_fields'])) {
			$this->filter_fields = $config['filter_fields'];
		}

		if (empty($this->context)) {
			$this->context = strtolower($this->option . '.' . $this->getName());
		}
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.reviews', 'reviews', array('control' => '', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		return $this->getFormData();
	}

	protected function getFormData() {
		$app = JFactory::getApplication();
		$itemid = $app->input->get('Itemid', 0, 'int');
		$id = $app->input->get('id', 0, 'int');

		return array('Itemid'=>$itemid, 'id'=>$id);
	}

	public function getData() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`plot`, `m`.`desc`, `m`.`known`, `m`.`slogan`, `m`.`budget`, `m`.`age_restrict`, `m`.`ua_rate`, `m`.`mpaa`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rate_fc`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`urls`, `m`.`length`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`metakey`, `m`.`metadesc`, `m`.`metadata`, `g`.`filename`");
		$query->from($db->quoteName('#__ka_movies').' AS `m`');
		$query->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`, `u`.`watched`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');

			$query->select(' `v`.`vote` AS `my_vote`, `v`.`_datetime`');
			$query->leftJoin($db->quoteName('#__ka_user_votes').' AS `v` ON `v`.`movie_id` = `m`.`id` AND `v`.`uid` = '.$user->get('id'));
		}

		$query->where('`m`.`id` = '.(int)$id.' AND `m`.`state` = 1 AND `access` IN ('.$groups.') AND `language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')');

		$db->setQuery($query);
		$result = $db->loadObject();

		// Selecting countries
		$db->setQuery("SELECT `c`.`id`, `c`.`name`, `c`.`code`, `t`.`ordering`"
			. "\n FROM ".$db->quoteName('#__ka_countries')." AS `c`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_countries')." AS `t` ON `t`.`country_id` = `c`.`id` AND `t`.`movie_id` = ".(int)$id
			. "\n WHERE `id` IN (SELECT `country_id` FROM ".$db->quoteName('#__ka_rel_countries')." WHERE `movie_id` = ".(int)$id.") AND `state` = 1"
			. "\n ORDER BY `ordering` ASC");
		$result->countries = $db->loadObjectList();

		// Selecting genres
		$db->setQuery("SELECT `g`.`id`, `g`.`name`, `g`.`alias`, `t`.`ordering`"
			. "\n FROM ".$db->quoteName('#__ka_genres')." AS `g`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_genres')." AS `t` ON `t`.`genre_id` = `g`.`id` AND `t`.`movie_id` = ".(int)$id
			. "\n WHERE `id` IN (SELECT `genre_id` FROM ".$db->quoteName('#__ka_rel_genres')." WHERE `movie_id` = ".(int)$id.") AND `state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').")"
			. "\n ORDER BY `ordering` ASC");
		$result->genres = $db->loadObjectList();

		// Get cast and crew
		$careers = array();
		$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." WHERE `is_mainpage` = 1 AND `is_amplua` = 0 ORDER BY `ordering` ASC");
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career) {
			$careers[$career->id] = $career->title;
		}

		$db->setQuery("SELECT `n`.`id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, `t`.`type`, `t`.`is_actors`, `t`.`voice_artists`"
			. "\n FROM ".$db->quoteName('#__ka_names')." AS `n`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `t` ON `t`.`name_id` = `n`.`id`"
			. "\n WHERE `id` IN (SELECT `name_id` FROM ".$db->quoteName('#__ka_rel_names')." WHERE `movie_id` = ".(int)$id.") AND `state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').")"
			. "\n ORDER BY `t`.`ordering` ASC");
		$crew = $db->loadObjectList();

		$_result = array();
		foreach ($crew as $key=>$value) {
			foreach (explode(',', $value->type) as $k=>$type) {
				if (isset($careers[$type]) && $value->is_actors == 0 && $value->voice_artists == 0) {
					$_result['crew'][$type]['career'] = $careers[$type];
					$_result['crew'][$type]['items'][] = array(
						'id'	=> $value->id,
						'name'	=> !empty($value->name) ? $value->name : $value->latin_name,
						'alias'	=> $value->alias
					);
				}

				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0) {
					$_result['cast'][$type]['career'] = $careers[$type];
					$_result['cast'][$type]['items'][] = array(
						'id'	=> $value->id,
						'name'	=> !empty($value->name) ? $value->name : $value->latin_name,
						'alias'	=> $value->alias
					);
				}
			}
		}

		if (!empty($_result['crew'])) {
			ksort($_result['crew']);

			foreach ($_result['crew'] as $row) {
				$row['total_items'] = count($row['items']);
				if ($row['total_items'] > 0) {
					$row['items'] = array_slice($row['items'], 0, 3);
				}
				$result->crew[] = $row;
			}
		}

		if (!empty($_result['cast'])) {
			foreach ($_result['cast'] as $row) {
				$row['total_items'] = count($row['items']);
				if ($row['total_items'] > 0) {
					$row['items'] = array_slice($row['items'], 0, 3);
				}
				$result->cast[] = $row;
			}
		}

		// Selecting premiere dates
		$db->setQuery("SELECT `p`.`premiere_date`, `p`.`info`, `c`.`name` AS `country`, `v`.`company_name`, `v`.`company_name_intl`"
			. "\n FROM ".$db->quoteName('#__ka_premieres')." AS `p`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_vendors')." AS `v` ON `v`.`id` = `p`.`vendor_id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `c` ON `c`.`id` = `p`.`country_id`"
			. "\n WHERE `movie_id` = 2"
			. "\n LIMIT 2");
		$result->premieres = $db->loadObjectList();

		// Selecting release dates
		$db->setQuery("SELECT `r`.`id`, `r`.`media_type`, `r`.`release_date`, `c`.`name` AS `country`, `v`.`company_name`, `v`.`company_name_intl`"
			. "\n FROM ".$db->quoteName('#__ka_releases')." AS `r`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `c` ON `c`.`id` = `r`.`country_id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_vendors')." AS `v` ON `v`.`id` = `r`.`vendor_id`"
			. "\n WHERE `movie_id` = 2"
			. "\n ORDER BY `ordering` ASC"
			. "\n LIMIT 2");
		$result->releases = $db->loadObjectList();

		// Get trailer and movie state for wath buttons
		if ($params->get('watch_trailer_button') == 1 || $params->get('watch_movie_button') == 1) {
			$db->setQuery("SELECT"
				. "\n (SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_trailers')." WHERE `is_movie` = 0 AND `movie_id` = ".(int)$id." AND `frontpage` = 1 AND `access` IN (".$groups.") AND `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').") LIMIT 1) AS `total_trailers`,"
				. "\n (SELECT COUNT(`id`) FROM ".$db->quoteName('#__ka_trailers')." WHERE `is_movie` = 1 AND `movie_id` = ".(int)$id." AND `frontpage` = 1 AND `access` IN (".$groups.") AND `state` = 1 AND `language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').") LIMIT 1) AS `total_movies`"
				. "\n FROM ".$db->quoteName('#__ka_trailers')
				. "\n GROUP BY `total_trailers`, `total_movies`");
			$result->total_video = $db->loadObject();
		}

		return $result;
	}

	public function getMovieData() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');

		$db->setQuery("SELECT `id`, `title`, `alias`, `year`, DATE_FORMAT(`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`modified`, '%Y-%m-%d') AS `modified`, `metakey`, `metadesc`, `metadata`"
			. "\n FROM ".$db->quoteName('#__ka_movies')
			. "\n WHERE `id` = ".(int)$id." AND `state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
		$result = $db->loadObject();

		return $result;
	}

	public function getCast() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getMovieData();

		$careers = array();
		$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." ORDER BY `ordering` ASC");
		$_careers = $db->loadObjectList();

		foreach ($_careers as $career) {
			$careers[$career->id] = $career->title;
		}

		$db->setQuery("SELECT `n`.`id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, `n`.`url_photo`, `n`.`gender`, `t`.`type`, `t`.`role`, `t`.`is_actors`, `t`.`voice_artists`, `d`.`id` AS `dub_id`, `d`.`name` AS `dub_name`, `d`.`latin_name` AS `dub_latin_name`, `d`.`alias` AS `dub_alias`, `d`.`url_photo` AS `dub_url_photo`, `d`.`gender` AS `dub_gender`, GROUP_CONCAT(`r`.`role` SEPARATOR ', ') AS `dub_role`"
			. "\n FROM ".$db->quoteName('#__ka_names')." AS `n`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `t` ON `t`.`name_id` = `n`.`id`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_names')." AS `d` ON `d`.`id` = `t`.`dub_id` AND `d`.`state` = 1 AND `d`.`access` IN (".$groups.") AND `d`.`language` IN (".$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').")"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `r` ON `r`.`dub_id` = `n`.`id`"
			. "\n WHERE `n`.`id` IN (SELECT `name_id` FROM ".$db->quoteName('#__ka_rel_names')." WHERE `movie_id` = ".(int)$id.")"
			. "\n AND `n`.`state` = 1 AND `n`.`access` IN (".$groups.") AND `n`.`language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').")"
			. "\n GROUP BY `n`.`id`"
			. "\n ORDER BY `t`.`ordering` ASC");
		$crew = $db->loadObjectList();

		$_result = array();
		$_careers_crew = array();
		foreach ($crew as $key=>$value) {
			foreach (explode(',', $value->type) as $k=>$type) {
				// Crew
				if (isset($careers[$type]) && $value->is_actors == 0 && $value->voice_artists == 0) {
					$_result['crew'][$type]['career'] = $careers[$type];
					$_careers_crew[] = $careers[$type];

					if (empty($value->url_photo)) {
						$ftype = $value->gender == 1 ? 'no_name_cover_small_m.png' : 'no_name_cover_small_f.png';
						$value->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/'.$ftype;
						$value->y_poster = '';
					} else {
						$value->poster = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($value->alias, 0, 1).'/'.$value->id.'/'.$value->url_photo;
						$value->y_poster = ' y-poster';
					}

					$_result['crew'][$type]['items'][] = array(
						'id'=>			$value->id,
						'name'=>		$value->name,
						'latin_name'=>	$value->latin_name,
						'alias'=>		$value->alias,
						'poster'=>		$value->poster,
						'y_poster'=>	$value->y_poster,
						'role'=>		$value->role
					);
				}

				// Cast
				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 0) {
					$_result['cast'][$type]['career'] = $careers[$type];
					$_careers_cast = $careers[$type]; // Only one value for actors. So we don't need build an array of items

					if (empty($value->url_photo)) {
						$ftype = $value->gender == 1 ? 'no_name_cover_small_m.png' : 'no_name_cover_small_f.png';
						$value->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/'.$ftype;
						$value->y_poster = '';
					} else {
						$value->poster = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($value->alias, 0, 1).'/'.$value->id.'/'.$value->url_photo;
						$value->y_poster = ' y-poster';
					}

					if (empty($value->dub_url_photo)) {
						$ftype = $value->dub_gender == 1 ? 'no_name_cover_small_m.png' : 'no_name_cover_small_f.png';
						$value->dub_url_photo = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/'.$ftype;
						$value->dub_y_poster = '';
					} else {
						$value->dub_url_photo = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($value->dub_alias, 0, 1).'/'.$value->dub_id.'/'.$value->dub_url_photo;
						$value->dub_y_poster = ' y-poster';
					}

					$_result['cast'][$type]['items'][] = array(
						'id'=>			$value->id,
						'name'=>		$value->name,
						'latin_name'=>	$value->latin_name,
						'alias'=>		$value->alias,
						'poster'=>		$value->poster,
						'y_poster'=>	$value->y_poster,
						'role'=>		$value->role,
						'dub_id'=>		$value->dub_id,
						'dub_name'=>	$value->dub_name,
						'dub_latin_name'=>$value->dub_latin_name,
						'dub_alias'=>	$value->dub_alias,
						'dub_url_photo'=>$value->dub_url_photo,
						'dub_y_poster'=>$value->dub_y_poster,
						'dub_gender'=>	$value->dub_gender,
						'dub_role'=>	$value->dub_role
					);
				}

				// Dub
				if (isset($careers[$type]) && $value->is_actors == 1 && $value->voice_artists == 1) {
					$_result['dub'][$type]['career'] = $careers[$type];
					$_careers_dub = $careers[$type];

					if (empty($value->url_photo)) {
						$ftype = $value->dub_gender == 1 ? 'no_name_cover_small_m.png' : 'no_name_cover_small_f.png';
						$value->poster = JURI::base().'components/com_kinoarhiv/assets/themes/component/'.$params->get('ka_theme').'/images/'.$ftype;
						$value->y_poster = '';
					} else {
						$value->poster = JURI::base().$params->get('media_actor_photo_root_www').'/'.JString::substr($value->alias, 0, 1).'/'.$value->id.'/'.$value->url_photo;
						$value->y_poster = ' y-poster';
					}

					$_result['dub'][$type]['items'][] = array(
						'id'=>			$value->id,
						'name'=>		$value->name,
						'latin_name'=>	$value->latin_name,
						'alias'=>		$value->alias,
						'poster'=>		$value->poster,
						'y_poster'=>	$value->y_poster,
						'role'=>		$value->dub_role
					);
				}
			}
		}

		ksort($_result['crew']);
		$result->crew = $_result['crew'];
		$result->cast = $_result['cast'];
		$result->dub = $_result['dub'];

		// Creating new array with name career, remove duplicate items and sort it
		$new_careers = array_unique($_careers_crew, SORT_STRING);
		foreach ($new_careers as $row) {
			$result->careers['crew'][] = $row;
		}

		$result->careers['cast'] = $_careers_cast;
		$result->careers['dub'] = $_careers_dub;

		return $result;
	}

	public function getTrailers() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getMovieData();

		$db->setQuery("SELECT `tr`.`id`, `tr`.`title`, `tr`.`embed_code`, `tr`.`screenshot`, `tr`.`filename`, `tr`.`duration`, `tr`.`_subtitles`, `tr`.`_chapters`, `tr`.`is_movie`, `m`.`alias`"
			. "\n FROM ".$db->quoteName('#__ka_trailers')." AS `tr`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `tr`.`movie_id`"
			. "\n WHERE `tr`.`movie_id` = ".(int)$id." AND `tr`.`state` = 1 AND `tr`.`access` IN (".$groups.") AND `tr`.`language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
		$result->trailers = $db->loadObjectList();

		foreach ($result->trailers as $key=>$value) {
			$result->trailers[$key]->path = JURI::base().$params->get('media_trailers_root_www').'/'.JString::substr($result->alias, 0, 1).'/'.$id.'/';
			$result->trailers[$key]->player_width = $params->get('player_width');

			/*if ($value->w_h != '') {
				$wh = explode('x', $value->w_h);
				if ($wh[0] > $params->get('player_width') || $wh[0] < $params->get('player_width')) {
					$result->trailers[$key]->player_height = floor(($wh[1]*(int)$params->get('player_width'))/$wh[0]);
				}
			}*/
		}

		return $result;
	}

	/**
	 * Method to get trailer or movie
	 */
	public function getTrailer() {
		jimport('joomla.filesystem.file');

		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');
		if ($app->input->get('watch', 'trailer', 'cmd') == 'movie') {
			$is_movie = 1;
			$frontpage = "";
		} else {
			$is_movie = 0;
			$frontpage = " AND `tr`.`frontpage` = 1";
		}

		$db->setQuery("SELECT `tr`.`id`, `tr`.`title`, `tr`.`embed_code`, `tr`.`screenshot`, `tr`.`urls`, `tr`.`filename`, `tr`.`duration`, `tr`.`_subtitles`, `tr`.`_chapters`, `m`.`alias`"
			. "\n FROM ".$db->quoteName('#__ka_trailers')." AS `tr`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `tr`.`movie_id`"
			. "\n WHERE `tr`.`movie_id` = ".(int)$id." AND `tr`.`state` = 1 AND `tr`.`access` IN (".$groups.") AND `tr`.`language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `tr`.`is_movie` = ".$is_movie.$frontpage
			. "\n LIMIT 1");
		$result = $db->loadObject();

		if (count($result) < 1) {
			return array();
		}

		$result->player_width = $params->get('player_width');
//echo '<pre>';
		if (!empty($result->urls)) {
			$urls_arr = explode("\n", $result->urls);
			print_r($urls_arr);
			$result->files['video'] = array();
		} else {
			$result->path = JURI::base().$params->get('media_trailers_root_www').'/'.JString::substr($result->alias, 0, 1).'/'.$id.'/';
			$result->files['video'] = json_decode($result->filename, true);
			$result->files['video_links'] = array();
			$_resolution = '';

			// Checking video extentions
			foreach ($result->files['video'] as $key=>$value) {
				if (!in_array(JFile::getExt($value['src']), explode(',', $params->get('upload_mime_video')))) {
					$result->files['video_links'][] = $result->files['video'][$key];
					unset($result->files['video'][$key]);
				}
				$_resolution = $value['resolution'];
			}

			if (isset($result->files['video'][0]['resolution'])) {
				$resolution = $result->files['video'][0]['resolution'];
			} else {
				if ($_resolution != '' && $_resolution != 'x') {
					$resolution = $_resolution;
				} else {
					$resolution = '1280x720';
				}
			}

			$tr_resolution = explode('x', $resolution);
			$tr_height = $tr_resolution[1];
			$result->player_height = floor(($tr_height * $result->player_width) / $tr_resolution[0]);

			$result->files['subtitles'] = json_decode($result->_subtitles, true);
			$result->files['chapters'] = json_decode($result->_chapters, true);
		}

		return $result;
	}

	public function getAwards() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getMovieData();

		$db->setQuery("SELECT `a`.`desc`, `a`.`year`, `aw`.`title` AS `aw_title`, `aw`.`desc` AS `aw_desc`"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `a`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `aw` ON `aw`.`id` = `a`.`award_id`"
			. "\n WHERE `type` = 0 AND `item_id` = ".(int)$id
			. "\n ORDER BY `year` ASC");
		$result->awards = $db->loadObjectList();

		return $result;
	}

	public function getSoundtracks() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getMovieData();

		/*$db->setQuery("SELECT `a`.`desc`, `a`.`year`, `aw`.`title` AS `aw_title`, `aw`.`desc` AS `aw_desc`"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `a`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `aw` ON `aw`.`id` = `a`.`award_id`"
			. "\n WHERE `type` = 0 AND `item_id` = ".(int)$id
			. "\n ORDER BY `year` ASC");
		$result->soundtracks = $db->loadObjectList();*/

		$result->soundtracks = array();

		return $result;
	}

	/**
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array
	 *
	*/
	public function getDimensionFilters() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$tab = $app->input->get('tab', null, 'cmd');
		$filter = $app->input->get('dim_filter', 0, 'string');

		if ($tab == 'wallpp') {
			$db->setQuery("SELECT `dimension` AS `value`, `dimension` AS `title`, SUBSTRING_INDEX(`dimension`, 'x', 1) AS `width`"
				. "\n FROM ".$db->quoteName('#__ka_movies_gallery')
				. "\n WHERE `type` = 1"
				. "\n GROUP BY `width`"
				. "\n ORDER BY `width` DESC");
			$result = $db->loadAssocList();
		}

		return $result;
	}

	public function voted() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$movie_id = $app->input->get('id', 0, 'int');
		$value = $app->input->get('value', -1, 'int');

		if ($value == '-1') {
			// Something went wrong
			$result = array('success'=>false, 'message'=>JText::_('COM_KA_REQUEST_ERROR'));
		} elseif ($value == 0) {
			// Remove vote and update rating
			$db->setQuery("SELECT `vote` FROM ".$db->quoteName('#__ka_user_votes')." WHERE `movie_id` = ".(int)$movie_id." AND `uid` = ".$user->get('id'));
			$vote_result = $db->loadResult();

			if (!empty($vote_result)) {
				$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `rate_loc` = `rate_loc`-1, `rate_sum_loc` = `rate_sum_loc`-".(int)$vote_result." WHERE `id` = ".(int)$movie_id);
				$query = $db->execute();

				$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_user_votes')." WHERE `movie_id` = ".(int)$movie_id." AND `uid` = ".$user->get('id'));
				$_result = $db->execute();
				$result = ($_result === true) ? array('success'=>true, 'message'=>JText::_('COM_KA_RATE_REMOVED')) : array('success'=>false, 'message'=>JText::_('COM_KA_REQUEST_ERROR'));
			} else {
				$result = array('success'=>false, 'message'=>JText::_('COM_KA_RATE_NOTRATED'));
			}
		} else {
			// Update rating and insert or update user vote in #__ka_user_votes
			// Check if value in range from 1 to 10
			if ($value >= 1 || $value <= 10) {
				// At first we check if user allready voted and when just update the rating and vote
				$db->setQuery("SELECT `v`.`vote`, `r`.`rate_sum_loc`"
					. "\n FROM ".$db->quoteName('#__ka_user_votes')." AS `v`"
					. "\n LEFT JOIN ".$db->quoteName('#__ka_movies')." AS `r` ON `r`.`id` = `v`.`movie_id`"
					. "\n WHERE `movie_id` = ".(int)$movie_id." AND `uid` = ".$user->get('id'));
				$vote_result = $db->loadObject();

				if (!empty($vote_result->vote)) { // User allready voted
					$rate_sum_loc = ($vote_result->rate_sum_loc - $vote_result->vote) + $value;
					$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `rate_sum_loc` = ".(int)$rate_sum_loc." WHERE `id` = ".(int)$movie_id);
					$query = $db->execute();

					if ($query) {
						$db->setQuery("UPDATE ".$db->quoteName('#__ka_user_votes')." SET `vote` = '".(int)$value."', `_datetime` = NOW() WHERE `movie_id` = ".(int)$movie_id." AND `uid` = ".$user->get('id'));
						$db->execute();

						$result = array('success'=>true, 'message'=>JText::_('COM_KA_RATE_RATED'));
					} else {
						$result = array('success'=>false, 'message'=>JText::_('COM_KA_REQUEST_ERROR'));
					}
				} else {
					$db->setQuery("UPDATE ".$db->quoteName('#__ka_movies')." SET `rate_loc` = `rate_loc`+1, `rate_sum_loc` = `rate_sum_loc`+".(int)$value." WHERE `id` = ".(int)$movie_id);
					$query = $db->execute();

					$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_user_votes')." (`uid`, `movie_id`, `vote`, `_datetime`) VALUES ('".$user->get('id')."', '".$movie_id."', '".$value."', NOW())");
					$_result = $db->execute();
					$result = array('success'=>false, 'message'=>JText::_('COM_KA_RATE_RATED'));
				}
			} else {
				$result = array('success'=>false, 'message'=>JText::_('COM_KA_REQUEST_ERROR'));
			}
		}

		return $result;
	}

	protected function _getListQuery() {
		static $lastStoreId;

		$currentStoreId = $this->getStoreId();

		if ($lastStoreId != $currentStoreId || empty($this->query)) {
			$lastStoreId = $currentStoreId;
			$this->query = $this->getListQuery();
		}

		return $this->query;
	}

	public function getItems() {
		$store = $this->getStoreId();

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();

		try {
			$items = $this->_getList($query, $this->getStart(), $this->getState('list.limit'));
		} catch (RuntimeException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		$this->cache[$store] = $items;

		return $this->cache[$store];
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', 0, 'int');
		$tab = $app->input->get('tab', 'reviews', 'cmd');
		$filter = $app->input->get('dim_filter', '0', 'string');

		$query = $db->getQuery(true);

		if ($tab == 'wallpp') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_movies_gallery'));

			if ($filter != '0') {
				$where = " AND `dimension` LIKE ".$db->quote($db->escape($filter, true)."%", false);
			} else {
				$where = "";
			}

			$query->where('`movie_id` = '.(int)$id.' AND `state` = 1 AND `type` = 1'.$where);
		} elseif ($tab == 'posters') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_movies_gallery'));
			$query->where('`movie_id` = '.(int)$id.' AND `state` = 1 AND `type` = 2');
		} elseif ($tab == 'screenshots') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_movies_gallery'));
			$query->where('`movie_id` = '.(int)$id.' AND `state` = 1 AND `type` = 3');
		} else {
			// Select reviews
			$review_id = $app->input->get('review', null, 'int');
			if (!empty($review_id) && $review_id > 0) {
				$this->setState('list.start', $review_id - 1);
			}

			$query->select('`rev`.`id`, `rev`.`review`, `rev`.`r_datetime` AS `review_date`, `rev`.`type`, `u`.`name`, `u`.`username`');
			$query->from($db->quoteName('#__ka_reviews').' AS `rev`');
			$query->leftJoin($db->quoteName('#__users').' AS `u` ON `u`.`id` = `rev`.`uid`');
			$query->where('`movie_id` = '.(int)$id.' AND `state` = 1 AND `u`.`id` != 0');
			$query->order('`r_datetime` DESC');
		}

		return $query;
	}

	public function getPagination() {
		JLoader::register('KAPagination', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'pagination.php');

		$app = JFactory::getApplication();
		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		if ($app->input->get('review')) {
			$page->setAdditionalUrlParam('review', 0);
		}

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	protected function getStoreId($id = '') {
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return md5($this->context . ':' . $id);
	}

	public function getTotal() {
		$store = $this->getStoreId('getTotal');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$query = $this->_getListQuery();
		try {
			$total = (int) $this->_getListCount($query);
		} catch (RuntimeException $e) {
			$this->setError($e->getMessage());
			return false;
		}

		$this->cache[$store] = $total;

		return $this->cache[$store];
	}

	public function getStart() {
		$store = $this->getStoreId('getstart');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();
		if ($start > $total - $limit) {
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

	protected function populateState($ordering = null, $direction = null) {
		if ($this->context) {
			$app = JFactory::getApplication();

			$value = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);
			if (!in_array($value, $this->filter_fields)) {
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}
			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $direction);
			if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) {
				$value = $direction;
				$app->setUserState($this->context . '.orderdirn', $value);
			}
			$this->setState('list.direction', $value);
		} else {
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true) {
		$app = JFactory::getApplication();
		$old_state = $app->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $app->input->get($request, null, $type);

		if (($cur_state != $new_state) && ($resetPage)) {
			$app->input->set('limitstart', 0);
		}

		if ($new_state !== null) {
			$app->setUserState($key, $new_state);
		} else {
			$new_state = $cur_state;
		}

		return $new_state;
	}
}
