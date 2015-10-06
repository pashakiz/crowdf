ALTER TABLE `#__crowdf_transactions` CHANGE `txn_status` `txn_status` ENUM( 'pending', 'completed', 'canceled', 'refunded', 'failed' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'pending';

CREATE TABLE IF NOT EXISTS `#__crowdf_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `data` text,
  `type` varchar(64) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;