ALTER TABLE `#__crowdf_projects` ADD `featured` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `created`;
ALTER TABLE `#__crowdf_transactions` ADD `reward_state` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0' AFTER `service_provider`;


CREATE TABLE IF NOT EXISTS `#__crowdf_intentions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cfints_usr_proj` (`user_id`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;