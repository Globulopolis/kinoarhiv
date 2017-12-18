
ALTER TABLE `#__ka_movies` ADD COLUMN `metacritics` TINYINT(2) DEFAULT 0 NOT NULL AFTER `rottentm_id`, ADD COLUMN `metacritics_id` VARCHAR(128) DEFAULT '' NOT NULL AFTER `metacritics`;

ALTER TABLE `#__ka_premieres` ADD COLUMN `language` CHAR(7) NOT NULL AFTER `info`, ADD INDEX `idx_language` (`language`);
