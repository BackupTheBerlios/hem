# MySQL-Front Dump 2.5
#
# Host: localhost   Database: usermanager_test_admin
# --------------------------------------------------------
# Server version 4.0.12-max-debug
#
# $Id: perm_db.sql,v 1.1 2004/07/16 13:58:49 mloitzl Exp $

#
# Table structure for table 'liveuser_applications'
#

DROP TABLE IF EXISTS `liveuser_applications`;
CREATE TABLE `liveuser_applications` (
  `application_id` int(10) unsigned NOT NULL default '0',
  `application_define_name` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`application_id`),
  UNIQUE KEY `application_define_name` (`application_define_name`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_area_admin_areas'
#

DROP TABLE IF EXISTS `liveuser_area_admin_areas`;
CREATE TABLE `liveuser_area_admin_areas` (
  `area_id` int(11) unsigned NOT NULL default '0',
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`area_id`,`perm_user_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_areas'
#

DROP TABLE IF EXISTS `liveuser_areas`;
CREATE TABLE `liveuser_areas` (
  `area_id` int(11) unsigned NOT NULL default '0',
  `application_id` int(11) unsigned NOT NULL default '0',
  `area_define_name` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`area_id`),
  KEY `areas_application_id` (`application_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_group_subgroups'
#

DROP TABLE IF EXISTS `liveuser_group_subgroups`;
CREATE TABLE `liveuser_group_subgroups` (
  `group_id` int(11) unsigned NOT NULL default '0',
  `subgroup_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`subgroup_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_grouprights'
#

DROP TABLE IF EXISTS `liveuser_grouprights`;
CREATE TABLE `liveuser_grouprights` (
  `group_id` int(11) unsigned NOT NULL default '0',
  `right_id` int(11) unsigned NOT NULL default '0',
  `right_level` tinyint(3) unsigned default NULL,
  PRIMARY KEY  (`group_id`,`right_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_groups'
#

DROP TABLE IF EXISTS `liveuser_groups`;
CREATE TABLE `liveuser_groups` (
  `group_id` int(10) unsigned NOT NULL default '0',
  `owner_user_id` int(11) unsigned default NULL,
  `owner_group_id` int(11) unsigned default NULL,
  `is_active` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`group_id`),
  KEY `groups_owner_user_id` (`owner_user_id`),
  KEY `groups_owner_group_id` (`owner_group_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_groupusers'
#

DROP TABLE IF EXISTS `liveuser_groupusers`;
CREATE TABLE `liveuser_groupusers` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `group_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`perm_user_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_languages'
#

DROP TABLE IF EXISTS `liveuser_languages`;
CREATE TABLE `liveuser_languages` (
  `language_id` smallint(5) unsigned NOT NULL default '0',
  `two_letter_name` char(2) default 'NU',
  `native_name` varchar(50) default NULL,
  PRIMARY KEY  (`language_id`),
  UNIQUE KEY `two_letter_name` (`two_letter_name`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_perm_users'
#

DROP TABLE IF EXISTS `liveuser_perm_users`;
CREATE TABLE `liveuser_perm_users` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `auth_user_id` varchar(32) NOT NULL default '0',
  `perm_type` tinyint(3) unsigned default NULL,
  `auth_container_name` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`perm_user_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_right_implied'
#

DROP TABLE IF EXISTS `liveuser_right_implied`;
CREATE TABLE `liveuser_right_implied` (
  `right_id` int(11) unsigned NOT NULL default '0',
  `implied_right_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`implied_right_id`,`right_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_right_scopes'
#

DROP TABLE IF EXISTS `liveuser_right_scopes`;
CREATE TABLE `liveuser_right_scopes` (
  `right_id` int(11) unsigned NOT NULL default '0',
  `right_type` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`right_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_rights'
#

DROP TABLE IF EXISTS `liveuser_rights`;
CREATE TABLE `liveuser_rights` (
  `right_id` int(10) unsigned NOT NULL default '0',
  `area_id` int(11) unsigned NOT NULL default '0',
  `right_define_name` varchar(50) NOT NULL default '',
  `has_implied` char(1) NOT NULL default 'N',
  `has_level` char(1) NOT NULL default 'N',
  `has_scope` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`right_id`),
  KEY `rights_area_id` (`area_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_translations'
#

DROP TABLE IF EXISTS `liveuser_translations`;
CREATE TABLE `liveuser_translations` (
  `section_id` int(16) unsigned NOT NULL default '0',
  `section_type` tinyint(3) unsigned NOT NULL default '0',
  `language_id` smallint(5) unsigned default NULL,
  `name` varchar(50) NOT NULL default '',
  `description` varchar(255) default NULL,
  PRIMARY KEY  (`section_id`,`section_type`, `language_id`)
) TYPE=MyISAM;



#
# Table structure for table 'liveuser_userrights'
#

DROP TABLE IF EXISTS `liveuser_userrights`;
CREATE TABLE `liveuser_userrights` (
  `perm_user_id` int(11) unsigned NOT NULL default '0',
  `right_id` int(11) unsigned NOT NULL default '0',
  `right_level` tinyint(3) default NULL,
  PRIMARY KEY  (`right_id`,`perm_user_id`)
) TYPE=MyISAM;

