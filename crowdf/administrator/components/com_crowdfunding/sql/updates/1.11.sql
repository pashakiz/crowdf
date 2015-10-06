CREATE TABLE IF NOT EXISTS `#__crowdf_reports` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(128) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `#__crowdf_projects` CHANGE `location` `location_id` INT( 10 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `#__crowdf_projects` DROP INDEX `location`, ADD INDEX `location_id` (`location_id`);