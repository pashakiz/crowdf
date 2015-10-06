ALTER TABLE `#__crowdf_intentions` ADD `auser_id` VARCHAR( 32 ) NOT NULL DEFAULT '' COMMENT 'It is a hash ID of an anonymous user.' AFTER `gateway` ;

CREATE TABLE IF NOT EXISTS `#__crowdf_images` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(24) NOT NULL,
  `thumb` varchar(24) NOT NULL,
  `project_id` smallint(6) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cfimg_pid` (`project_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;