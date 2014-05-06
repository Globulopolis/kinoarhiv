CREATE TABLE `#__ka_awards` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL DEFAULT '',
  `desc` mediumtext NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `code` char(2) NOT NULL,
  `language` char(7) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_code` (`code`),
  KEY `idx_language` (`language`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_genres` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `alias` varchar(128) NOT NULL,
  `stats` int(6) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `access` smallint(1) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_access` (`access`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_movies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `introtext` mediumtext NOT NULL COMMENT 'Use only in frontpage',
  `plot` text NOT NULL,
  `desc` text NOT NULL,
  `known` text NOT NULL,
  `year` varchar(20) NOT NULL DEFAULT '0000',
  `slogan` mediumtext NOT NULL,
  `budget` varchar(255) NOT NULL DEFAULT '',
  `age_restrict` tinyint(1) NOT NULL DEFAULT '-1',
  `ua_rate` tinyint(1) NOT NULL DEFAULT '-1',
  `mpaa` varchar(5) NOT NULL DEFAULT '',
  `length` time NOT NULL,
  `rate_loc` int(10) NOT NULL DEFAULT '0',
  `rate_sum_loc` int(10) NOT NULL DEFAULT '0',
  `imdb_votesum` varchar(5) NOT NULL DEFAULT '0',
  `imdb_votes` int(11) NOT NULL DEFAULT '0',
  `imdb_id` int(11) NOT NULL DEFAULT '0',
  `kp_votesum` varchar(5) NOT NULL DEFAULT '0',
  `kp_votes` int(11) NOT NULL DEFAULT '0',
  `kp_id` int(11) NOT NULL DEFAULT '0',
  `rate_fc` tinyint(2) NOT NULL DEFAULT '0',
  `rottentm_id` varchar(128) NOT NULL DEFAULT '',
  `rate_custom` text NOT NULL,
  `urls` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_by` int(10) unsigned NOT NULL DEFAULT '0',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `metadata` text NOT NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_state` (`state`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_movies_gallery` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL DEFAULT '',
  `dimension` varchar(10) NOT NULL DEFAULT '',
  `movie_id` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-wallpapers, 2-posters, 3-screenshots, 4-soundtracks album cover',
  `poster_frontpage` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_type` (`type`),
  KEY `idx_poster` (`poster_frontpage`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_music` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `artist_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `genre_rel_id` int(11) NOT NULL,
  `xgenre_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `composer` varchar(255) NOT NULL DEFAULT '',
  `publisher` varchar(255) NOT NULL DEFAULT '',
  `performer` varchar(255) NOT NULL DEFAULT '',
  `label` varchar(128) NOT NULL DEFAULT '',
  `isrc` char(12) NOT NULL,
  `length` char(6) NOT NULL DEFAULT '0',
  `cd_number` char(12) NOT NULL DEFAULT '0',
  `track_number` smallint(3) NOT NULL DEFAULT '1',
  `filename` varchar(64) NOT NULL,
  `access` int(11) NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_music_albums` (
  `a_id` int(11) NOT NULL AUTO_INCREMENT,
  `a_title` varchar(255) NOT NULL,
  `a_alias` varchar(255) NOT NULL,
  `a_year` year(4) NOT NULL,
  `a_desc` text NOT NULL,
  `a_covers_path` varchar(255) NOT NULL,
  `a_tracks_path` text NOT NULL,
  `a_tracks_preview_path` varchar(255) NOT NULL,
  `a_access` int(11) NOT NULL,
  `a_language` char(7) NOT NULL,
  `a_state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_music_genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `language` char(7) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_music_rel_albums` (
  `album_id` int(11) NOT NULL,
  `track_id` bigint(20) NOT NULL,
  PRIMARY KEY (`album_id`,`track_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_music_rel_genres` (
  `genre_id` int(11) NOT NULL,
  `item_id` bigint(20) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-album, 1-track, 2-artist',
  PRIMARY KEY (`genre_id`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_names` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asset_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  `latin_name` varchar(128) NOT NULL DEFAULT '',
  `alias` varchar(255) NOT NULL DEFAULT '',
  `date_of_birth` date NOT NULL DEFAULT '0000-00-00',
  `date_of_death` date NOT NULL DEFAULT '0000-00-00',
  `birthplace` varchar(255) NOT NULL DEFAULT '',
  `birthcountry` int(10) NOT NULL DEFAULT '0',
  `gender` tinyint(1) unsigned NOT NULL COMMENT '0-female, 1-male',
  `height` varchar(6) NOT NULL DEFAULT '',
  `desc` text NOT NULL,
  `attribs` varchar(5120) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `metakey` text NOT NULL,
  `metadesc` text NOT NULL,
  `metadata` text NOT NULL,
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_country` (`birthcountry`),
  KEY `idx_state` (`state`),
  KEY `idx_access` (`access`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_names_career` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `is_mainpage` tinyint(1) NOT NULL DEFAULT '0',
  `is_amplua` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_is_mainpage` (`is_mainpage`),
  KEY `idx_is_amplua` (`is_amplua`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_names_gallery` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL DEFAULT '',
  `dimension` varchar(10) NOT NULL DEFAULT '',
  `name_id` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1-wallpapers, 2-posters, 3-photo',
  `photo_frontpage` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_name_id` (`name_id`),
  KEY `idx_type` (`type`),
  KEY `idx_state` (`state`),
  KEY `idx_frontpage` (`photo_frontpage`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_premieres` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `premiere_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `country_id` int(11) NOT NULL DEFAULT '0',
  `info` mediumtext NOT NULL,
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_vendor_id` (`vendor_id`),
  KEY `idx_country_id` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_awards` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `award_id` int(11) NOT NULL DEFAULT '0',
  `desc` mediumtext NOT NULL,
  `year` year(4) NOT NULL DEFAULT '0000',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-movie, 1-people',
  PRIMARY KEY (`id`),
  KEY `idx_item_id` (`item_id`),
  KEY `idx_award_id` (`award_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_countries` (
  `country_id` int(11) NOT NULL DEFAULT '0',
  `movie_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  KEY `idx_movie` (`movie_id`),
  KEY `idx_country` (`country_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_genres` (
  `genre_id` int(11) NOT NULL DEFAULT '0',
  `movie_id` int(11) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`genre_id`,`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_names` (
  `name_id` int(11) NOT NULL DEFAULT '0',
  `movie_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '0',
  `role` varchar(1024) NOT NULL DEFAULT '',
  `dub_id` int(11) NOT NULL DEFAULT '0',
  `is_actors` tinyint(1) NOT NULL DEFAULT '0',
  `voice_artists` tinyint(1) NOT NULL DEFAULT '0',
  `is_directors` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `desc` mediumtext NOT NULL,
  PRIMARY KEY (`name_id`,`movie_id`,`dub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_names_career` (
  `career_id` int(11) NOT NULL DEFAULT '0',
  `name_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`career_id`,`name_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_rel_names_genres` (
  `genre_id` int(11) NOT NULL DEFAULT '0',
  `name_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`genre_id`,`name_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_releases` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `media_type` tinyint(1) NOT NULL DEFAULT '0',
  `release_date` date NOT NULL DEFAULT '0000-00-00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_country` (`country_id`),
  KEY `idx_vendor` (`vendor_id`),
  KEY `idx_movie` (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_reviews` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) unsigned NOT NULL,
  `movie_id` int(11) unsigned NOT NULL,
  `review` text NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(64) NOT NULL DEFAULT '',
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0-premod, 1-published',
  PRIMARY KEY (`id`),
  KEY `idx_state` (`state`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_user_id` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_trailers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `embed_code` mediumtext NOT NULL,
  `screenshot` varchar(128) NOT NULL COMMENT 'screenshot filename',
  `urls` mediumtext NOT NULL,
  `filename` mediumtext NOT NULL,
  `resolution` varchar(10) NOT NULL,
  `dar` varchar(5) NOT NULL DEFAULT '16:9',
  `duration` time NOT NULL DEFAULT '00:00:00',
  `_subtitles` mediumtext NOT NULL,
  `_chapters` mediumtext NOT NULL,
  `frontpage` tinyint(1) NOT NULL DEFAULT '0',
  `access` int(10) unsigned NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '0',
  `language` char(7) NOT NULL,
  `is_movie` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_movie_id` (`movie_id`),
  KEY `idx_frontpage` (`frontpage`),
  KEY `idx_access` (`access`),
  KEY `idx_state` (`state`),
  KEY `idx_is_movie` (`is_movie`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_user_marked_movies` (
  `uid` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `favorite` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `watched` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`uid`,`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_user_marked_names` (
  `uid` int(11) NOT NULL,
  `name_id` int(11) NOT NULL,
  `favorite` tinyint(1) unsigned NOT NULL DEFAULT '0',
  KEY `idx_user_id` (`uid`),
  KEY `idx_name_id` (`name_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_user_votes` (
  `uid` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `vote` smallint(2) NOT NULL DEFAULT '0',
  `_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`uid`,`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ka_vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_name` varchar(255) NOT NULL DEFAULT '',
  `company_name_intl` varchar(255) DEFAULT '',
  `company_name_alias` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `language` char(7) NOT NULL,
  `state` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_language` (`language`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
