CREATE TABLE IF NOT EXISTS `#__cfpartners_partners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `partner_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
