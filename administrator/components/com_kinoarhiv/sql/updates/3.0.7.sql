
ALTER TABLE `#__ka_music_genres` 
  CHANGE `title` `name` VARCHAR (255) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL,
  ADD COLUMN `stats` INT (6) DEFAULT 0 NOT NULL AFTER `alias`,
  ADD COLUMN `access` SMALLINT (1) DEFAULT 0 NOT NULL AFTER `state`,
  CHANGE `language` `language` CHAR(7) CHARSET utf8 COLLATE utf8_general_ci NOT NULL AFTER `access`,
  ADD INDEX `idx_state` (`state`),
  ADD INDEX `idx_access` (`access`),
  ADD INDEX `idx_language` (`language`);

UPDATE `#__ka_music_genres` SET `access` = '1';

ALTER TABLE `#__ka_music_albums` 
  ADD COLUMN `asset_id` INT (10) UNSIGNED NOT NULL AFTER `id`,
  ADD COLUMN `buy_url` VARCHAR (255) NOT NULL AFTER `tracks_preview_path`,
  ADD COLUMN `fs_alias` VARCHAR (255) NOT NULL COMMENT 'Is the same as alias but only in latin charset' AFTER `alias`,
  ADD COLUMN `composer` VARCHAR (255) NOT NULL AFTER `fs_alias`,
  ADD COLUMN `attribs` VARCHAR (5120) NOT NULL AFTER `buy_url`,
  ADD COLUMN `created` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `attribs`,
  ADD COLUMN `created_by` INT(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `created`,
  ADD COLUMN `modified` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `created_by`,
  ADD COLUMN `length` VARCHAR (16) DEFAULT '' NOT NULL AFTER `year`,
  ADD COLUMN `isrc` VARCHAR(16) NOT NULL AFTER `length`,
  ADD COLUMN `ordering` INT (11) DEFAULT 0 NOT NULL AFTER `tracks_preview_path`,
  ADD COLUMN `metakey` TEXT NOT NULL AFTER `ordering`,
  ADD COLUMN `metadesc` TEXT NOT NULL AFTER `metakey`,
  ADD COLUMN `rate` INT (10) DEFAULT 0 NOT NULL AFTER `desc`,
  ADD COLUMN `rate_sum` INT (10) DEFAULT 0 NOT NULL AFTER `rate`,
  ADD COLUMN `metadata` TEXT NOT NULL AFTER `access`,
  CHANGE `id` `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  CHANGE `year` `year` DATE DEFAULT '0000-00-00' NOT NULL,
  CHANGE `state` `state` TINYINT (3) UNSIGNED DEFAULT 0 NOT NULL,
  ADD INDEX `idx_access` (`access`),
  ADD INDEX `idx_state` (`state`),
  ADD INDEX `idx_language` (`language`);

CREATE TABLE IF NOT EXISTS `#__ka_music_gallery` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filename` varchar(128) NOT NULL DEFAULT '',
  `dimension` varchar(10) NOT NULL DEFAULT '',
  `item_id` bigint(19) NOT NULL DEFAULT '0',
  `poster_frontpage` tinyint(1) unsigned DEFAULT '0',
  `state` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_itemid` (`item_id`),
  KEY `idx_poster` (`poster_frontpage`),
  KEY `idx_state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__ka_movies` 
  ADD COLUMN `buy_urls` TEXT NOT NULL AFTER `urls`;

CREATE TABLE `#__ka_music_rel_composers` (
  `name_id` int(11) NOT NULL DEFAULT '0',
  `album_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '0',
  `role` varchar(1024) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `desc` mediumtext NOT NULL,
  PRIMARY KEY (`name_id`,`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
