
ALTER TABLE `#__ka_movies` ADD COLUMN `metacritics` TINYINT(2) DEFAULT 0 NOT NULL AFTER `rottentm_id`, ADD COLUMN `metacritics_id` VARCHAR(128) DEFAULT '' NOT NULL AFTER `metacritics`;
