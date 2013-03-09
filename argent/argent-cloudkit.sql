-- Argent CloudKit Database Structure
-- version 1.2.0
-- http://www.argentcloudkit.com/
--

CREATE TABLE IF NOT EXISTS `tbljobs` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_accounts` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `account_status` set('NEW','ACTIVE','SUSPENDED','DELETED') NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL,
  PRIMARY KEY (`meta_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_accounts_head` (
`meta_guid` varchar(32)
,`object_id` varchar(40)
,`account_status` set('NEW','ACTIVE','SUSPENDED','DELETED')
,`meta_timestamp` datetime
,`meta_user` varchar(40)
,`meta_ip` varchar(25)
);

CREATE TABLE IF NOT EXISTS `ua_custom_fields` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_data` longtext NOT NULL,
  `ua_parent_object` varchar(40) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL,
  PRIMARY KEY (`meta_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_custom_fields_head` (
`meta_guid` varchar(32)
,`object_id` varchar(40)
,`field_name` varchar(255)
,`field_data` longtext
,`ua_parent_object` varchar(40)
,`meta_timestamp` datetime
,`meta_user` varchar(40)
,`meta_ip` varchar(25)
);

CREATE TABLE IF NOT EXISTS `ua_object_register` (
  `object_id` varchar(40) NOT NULL,
  `ua_parent_object` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_object_types` (
  `type_identifier` varchar(3) NOT NULL,
  `type_name` varchar(20) NOT NULL,
  `table` varchar(255) NOT NULL,
  PRIMARY KEY (`type_identifier`),
  UNIQUE KEY `object_identifier` (`type_identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_permissions` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `user_id` varchar(40) NOT NULL,
  `cascade` tinyint(4) NOT NULL,
  `master` varchar(32) NOT NULL,
  `create` tinyint(4) NOT NULL,
  `read` tinyint(4) NOT NULL,
  `update` tinyint(4) NOT NULL,
  `delete` tinyint(4) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ua_relationships` (
  `meta_guid` varchar(32) NOT NULL,
  `primary_object_id` varchar(40) NOT NULL,
  `secondary_object_id` varchar(40) NOT NULL,
  `relationship` varchar(255) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_rights` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `right` int(11) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_session_register` (
  `session_id` varchar(255) NOT NULL,
  `user_id` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_sessions` (
  `session_id` varchar(255) NOT NULL DEFAULT '',
  `session_name` varchar(25) NOT NULL,
  `timeout` int(11) NOT NULL,
  `last_activity` bigint(20) NOT NULL,
  `started` bigint(20) NOT NULL,
  `remote_address` varchar(50) NOT NULL,
  `secure` tinyint(4) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_users` (
  `meta_guid` varchar(32) NOT NULL,
  `object_id` varchar(40) NOT NULL,
  `email` varchar(255) NOT NULL,
  `secret` text NOT NULL,
  `display_name` varchar(255) NOT NULL,
  `ua_parent_object` varchar(40) NOT NULL,
  `meta_timestamp` datetime NOT NULL,
  `meta_user` varchar(40) NOT NULL,
  `meta_ip` varchar(25) NOT NULL,
  PRIMARY KEY (`meta_guid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ua_users_head` (
`meta_guid` varchar(32)
,`object_id` varchar(40)
,`email` varchar(255)
,`secret` text
,`display_name` varchar(255)
,`ua_parent_object` varchar(40)
,`meta_timestamp` datetime
,`meta_user` varchar(40)
,`meta_ip` varchar(25)
);DROP TABLE IF EXISTS `ua_accounts_head`;

CREATE VIEW `ua_accounts_head` AS select `ua_accounts`.`meta_guid` AS `meta_guid`,`ua_accounts`.`object_id` AS `object_id`,`ua_accounts`.`account_status` AS `account_status`,`ua_accounts`.`meta_timestamp` AS `meta_timestamp`,`ua_accounts`.`meta_user` AS `meta_user`,`ua_accounts`.`meta_ip` AS `meta_ip` from `ua_accounts` where `ua_accounts`.`meta_guid` in (select max(`ua_accounts`.`meta_guid`) AS `max(meta_guid)` from `ua_accounts` group by `ua_accounts`.`object_id`);
DROP TABLE IF EXISTS `ua_custom_fields_head`;

CREATE VIEW `ua_custom_fields_head` AS select `ua_custom_fields`.`meta_guid` AS `meta_guid`,`ua_custom_fields`.`object_id` AS `object_id`,`ua_custom_fields`.`field_name` AS `field_name`,`ua_custom_fields`.`field_data` AS `field_data`,`ua_custom_fields`.`ua_parent_object` AS `ua_parent_object`,`ua_custom_fields`.`meta_timestamp` AS `meta_timestamp`,`ua_custom_fields`.`meta_user` AS `meta_user`,`ua_custom_fields`.`meta_ip` AS `meta_ip` from `ua_custom_fields` where `ua_custom_fields`.`meta_guid` in (select max(`ua_custom_fields`.`meta_guid`) AS `max(meta_guid)` from `ua_custom_fields` group by `ua_custom_fields`.`object_id`);
DROP TABLE IF EXISTS `ua_users_head`;

CREATE VIEW `ua_users_head` AS select `ua_users`.`meta_guid` AS `meta_guid`,`ua_users`.`object_id` AS `object_id`,`ua_users`.`email` AS `email`,`ua_users`.`secret` AS `secret`,`ua_users`.`display_name` AS `display_name`,`ua_users`.`ua_parent_object` AS `ua_parent_object`,`ua_users`.`meta_timestamp` AS `meta_timestamp`,`ua_users`.`meta_user` AS `meta_user`,`ua_users`.`meta_ip` AS `meta_ip` from `ua_users` where `ua_users`.`meta_guid` in (select max(`ua_users`.`meta_guid`) AS `max(meta_guid)` from `ua_users` group by `ua_users`.`object_id`);
