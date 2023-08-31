CREATE TABLE IF NOT EXISTS `#__qf3_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `access` tinyint(2) NOT NULL default '0',
  `published` tinyint(1) NOT NULL DEFAULT '1' ,
  `title` varchar(255) NOT NULL default '',
  `params` text,
  `language` char(7) NOT NULL default '',
  `hits` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_access` (`access`),
  KEY `idx_state` (`published`),
  KEY `idx_language` (`language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__qf3_forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL default '',
  `fields` text,
  `projectid` int(11) NOT NULL default '0',
  `def` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_projectid` (`projectid`),
  KEY `idx_def` (`def`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__qf3_ps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `st_title` varchar(256) NOT NULL default '',
  `st_formid` int(11) NOT NULL default '0',
  `st_form` text,
  `st_date` varchar(50) NOT NULL default '',
  `st_status` tinyint(1) NOT NULL default '0',
  `st_ip` varchar(128) NOT NULL default '',
  `st_user` int(11) NOT NULL default '0',
  `st_desk` text,
  `params` text,
  PRIMARY KEY  (`id`),
  KEY `idx_st_formid` (`st_formid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
