CREATE TABLE IF NOT EXISTS `#__crowdf_emails` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_email` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_types` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `params` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

ALTER TABLE `#__crowdf_projects` ADD `type_id` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `catid` ;