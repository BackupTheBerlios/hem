# $Id: Auth_DB.sql,v 1.1 2004/07/16 13:58:49 mloitzl Exp $
# LiveUser user table
CREATE TABLE liveuser_users (
  `auth_user_id` varchar(32) NOT NULL default '0',
  `handle` varchar(32) NOT NULL default '',
  `passwd` varchar(32) NOT NULL default '',
  `lastlogin` datetime default NULL,
  `owner_user_id` int(11) unsigned default NULL,
  `owner_group_id` int(11) unsigned default NULL,
  `is_active` char(1) NOT NULL default 'N',
  PRIMARY KEY auth_user_id (`auth_user_id`, `handle`),
  KEY `users_owner_user_id` (`owner_user_id`),
  KEY `users_owner_group_id` (`owner_group_id`)
) TYPE=MyISAM;