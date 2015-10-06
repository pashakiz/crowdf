CREATE TABLE IF NOT EXISTS `#__crowdf_comments` (
  `id` int(10) unsigned NOT NULL,
  `comment` varchar(1024) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_countries` (
  `id` smallint(5) unsigned NOT NULL,
  `name` varchar(128) NOT NULL,
  `code` char(2) NOT NULL,
  `code4` varchar(5) NOT NULL DEFAULT '' COMMENT 'A code with 4 letters.',
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  `currency` char(3) DEFAULT NULL,
  `timezone` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_currencies` (
  `id` smallint(6) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `code` char(3) NOT NULL,
  `symbol` char(3) NOT NULL DEFAULT '',
  `position` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_emails` (
  `id` tinyint(3) unsigned NOT NULL,
  `title` varchar(128) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_email` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_followers` (
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_images` (
  `id` smallint(6) unsigned NOT NULL,
  `image` varchar(24) NOT NULL,
  `thumb` varchar(24) NOT NULL,
  `project_id` smallint(6) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_intentions` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_locations` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(200) NOT NULL,
  `latitude` varchar(64) NOT NULL,
  `longitude` varchar(64) NOT NULL,
  `country_code` char(2) NOT NULL,
  `state_code` char(4) NOT NULL DEFAULT '',
  `timezone` varchar(40) NOT NULL,
  `published` tinyint(3) unsigned NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_logs` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `data` text,
  `type` varchar(64) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_payment_sessions` (
  `id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `unique_key` varchar(32) NOT NULL DEFAULT '' COMMENT 'A unique key from a gateway.',
  `gateway` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is the name of the Payment Service.',
  `gateway_data` varchar(2048) DEFAULT NULL COMMENT 'Contains a specific data for some gateways.',
  `auser_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'It is a hash ID of an anonymous user.',
  `session_id` varchar(32) NOT NULL DEFAULT '' COMMENT 'Session ID of the payment process.',
  `intention_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_projects` (
  `id` smallint(6) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `alias` varchar(48) NOT NULL DEFAULT '',
  `short_desc` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(64) NOT NULL DEFAULT '',
  `image_square` varchar(64) NOT NULL DEFAULT '',
  `image_small` varchar(64) NOT NULL DEFAULT '',
  `location_id` int(10) unsigned NOT NULL DEFAULT '0',
  `goal` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `funded` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `funding_type` enum('FIXED','FLEXIBLE') NOT NULL DEFAULT 'FIXED',
  `funding_start` date NOT NULL DEFAULT '0000-00-00',
  `funding_end` date NOT NULL DEFAULT '0000-00-00',
  `funding_days` smallint(5) unsigned NOT NULL DEFAULT '0',
  `pitch_video` varchar(255) NOT NULL DEFAULT '',
  `pitch_image` varchar(255) NOT NULL DEFAULT '',
  `hits` smallint(5) unsigned NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `featured` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `catid` int(11) unsigned NOT NULL DEFAULT '0',
  `type_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_reports` (
  `id` int(10) unsigned NOT NULL,
  `subject` varchar(128) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `email` varchar(128) DEFAULT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_rewards` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(500) NOT NULL,
  `amount` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `number` smallint(5) unsigned NOT NULL DEFAULT '0',
  `distributed` smallint(5) unsigned NOT NULL DEFAULT '0',
  `delivery` date NOT NULL DEFAULT '0000-00-00' COMMENT 'Estimated delivery',
  `shipping` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `image` varchar(32) DEFAULT NULL,
  `image_thumb` varchar(32) DEFAULT NULL,
  `image_square` varchar(32) DEFAULT NULL,
  `published` tinyint(3) NOT NULL DEFAULT '1',
  `project_id` int(10) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_transactions` (
  `id` int(11) unsigned NOT NULL,
  `txn_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `txn_amount` decimal(10,3) unsigned NOT NULL DEFAULT '0.000',
  `txn_currency` varchar(64) NOT NULL DEFAULT '',
  `txn_status` enum('pending','completed','canceled','refunded','failed') NOT NULL DEFAULT 'pending',
  `txn_id` varchar(64) NOT NULL DEFAULT '',
  `parent_txn_id` varchar(64) NOT NULL DEFAULT '' COMMENT 'Transaction id of an pre authorized transaction.',
  `extra_data` varchar(2048) DEFAULT NULL COMMENT 'Additional information about transaction.',
  `status_reason` varchar(32) NOT NULL DEFAULT '' COMMENT 'This is a reason of the status in few words.',
  `project_id` int(10) unsigned NOT NULL,
  `reward_id` int(10) unsigned NOT NULL DEFAULT '0',
  `investor_id` int(10) unsigned NOT NULL COMMENT 'The backer of the project.',
  `receiver_id` int(10) unsigned NOT NULL COMMENT 'The owner of the project.',
  `service_provider` varchar(32) NOT NULL,
  `reward_state` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `fee` decimal(10,2) unsigned NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_types` (
  `id` tinyint(3) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) DEFAULT NULL,
  `params` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__crowdf_updates` (
  `id` int(10) unsigned NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `state` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `#__crowdf_comments`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_countries`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_crowdf_ccode` (`code`);

ALTER TABLE `#__crowdf_emails`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_followers`
  ADD PRIMARY KEY (`user_id`,`project_id`);

ALTER TABLE `#__crowdf_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cfimg_pid` (`project_id`);

ALTER TABLE `#__crowdf_intentions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cfints_usr_proj` (`user_id`,`project_id`);

ALTER TABLE `#__crowdf_locations`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_logs`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_payment_sessions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `catid` (`catid`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `alias` (`alias`),
  ADD KEY `location_id` (`location_id`);

ALTER TABLE `#__crowdf_reports`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_rewards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

ALTER TABLE `#__crowdf_transactions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_types`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__crowdf_updates`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `#__crowdf_comments`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_countries`
  MODIFY `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_currencies`
  MODIFY `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_emails`
  MODIFY `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_images`
  MODIFY `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_intentions`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_locations`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_logs`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_payment_sessions`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_projects`
  MODIFY `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_reports`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_rewards`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_transactions`
  MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_types`
  MODIFY `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT;
ALTER TABLE `#__crowdf_updates`
  MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;

