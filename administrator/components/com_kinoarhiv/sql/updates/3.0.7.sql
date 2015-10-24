
/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

ALTER TABLE `#__ka_music_genres` 
  CHANGE `title` `name` VARCHAR (255) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '' NOT NULL,
  ADD COLUMN `stats` INT (6) DEFAULT 0 NOT NULL AFTER `alias`,
  ADD COLUMN `access` SMALLINT (1) DEFAULT 0 NOT NULL AFTER `state`,
  CHANGE `language` `language` CHAR(7) CHARSET utf8 COLLATE utf8_general_ci NOT NULL AFTER `access`,
  ADD INDEX `idx_state` (`state`),
  ADD INDEX `idx_access` (`access`),
  ADD INDEX `idx_language` (`language`);

UPDATE `#__ka_music_genres` SET `access` = '1';

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

ALTER TABLE `#__ka_music_albums` 
  ADD COLUMN `asset_id` INT (10) UNSIGNED NOT NULL AFTER `id`,
  ADD COLUMN `buy_url` VARCHAR (255) NOT NULL AFTER `tracks_preview_path`,
  ADD COLUMN `fs_alias` VARCHAR (3) NOT NULL COMMENT 'Is the same as alias but only in latin charset' AFTER `alias`,
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
  ADD COLUMN `cover_filename` VARCHAR(128) DEFAULT '' NOT NULL AFTER `rate_sum`,
  ADD COLUMN `metadata` TEXT NOT NULL AFTER `access`,
  ADD COLUMN `covers_path_www` VARCHAR(255) NOT NULL AFTER `covers_path`,
  CHANGE `id` `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
  CHANGE `year` `year` DATE DEFAULT '0000-00-00' NOT NULL,
  CHANGE `state` `state` TINYINT (3) UNSIGNED DEFAULT 0 NOT NULL,
  ADD INDEX `idx_access` (`access`),
  ADD INDEX `idx_state` (`state`),
  ADD INDEX `idx_language` (`language`);

ALTER TABLE `#__ka_movies` 
  ADD COLUMN `buy_urls` TEXT NOT NULL AFTER `urls`,
  ADD COLUMN `modified_by` INT(10) UNSIGNED DEFAULT 0 NOT NULL AFTER `modified`,
  ADD COLUMN `publish_up` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `modified_by`,
  ADD COLUMN `publish_down` DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL AFTER `publish_up`,
  ADD COLUMN `fs_alias` VARCHAR(3) DEFAULT '' NOT NULL AFTER `alias`;

CREATE TABLE IF NOT EXISTS `#__ka_music_rel_composers` (
  `name_id` int(11) NOT NULL DEFAULT '0',
  `album_id` int(11) NOT NULL DEFAULT '0',
  `type` varchar(16) NOT NULL DEFAULT '0',
  `role` varchar(1024) NOT NULL DEFAULT '',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `desc` mediumtext NOT NULL,
  PRIMARY KEY (`name_id`,`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__ka_rel_names`
  ADD INDEX `idx_dub_id` (`dub_id`);

ALTER TABLE `#__ka_rel_names` DROP PRIMARY KEY, ADD PRIMARY KEY (`name_id`, `movie_id`);

ALTER TABLE `#__ka_names`
  ADD COLUMN `fs_alias` VARCHAR(3) DEFAULT '' NOT NULL AFTER `alias`;

CREATE TABLE IF NOT EXISTS `#__ka_media_types` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL DEFAULT '',
  `language` CHAR(7) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO `#__ka_media_types`(`id`,`title`,`language`) VALUES (1,'CAMRip (CAM)','*'),(2,'Telesync (TS)','*'),(3,'Super Telesync (SuperTS, Super-TS, Оцифровка)','*'),(4,'Telecine (TC)','*'),(5,'VHS-Rip (VHSRip)','*'),(6,'DVD-Screener (DVDScr) (SCR)','*'),(7,'SCREENER (SCR), VHS-SCREENER (VHSScr)','*'),(8,'TV-Rip (TVRip)','*'),(9,'SAT-Rip (SATRip)','*'),(10,'DVD-Rip (DVDRip)','*'),(11,'DVD5 (DVD-5)','*'),(12,'DVD9 (DVD-9)','*'),(13,'HDTV-Rip (HDTVRip)','*'),(14,'BD-Rip (BDRip)','*'),(15,'Blu-Ray','*'),(16,'HDDVD','*'),(17,'Workprint (WP)','*'),(18,'Laserdisc-RIP (LDRip)','*'),(19,'HDDVD-Rip (HDDVDRip)','*'),(20,'Другое','*');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
