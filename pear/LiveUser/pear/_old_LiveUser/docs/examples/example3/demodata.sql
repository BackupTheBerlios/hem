# $Id: demodata.sql,v 1.1 2004/07/16 13:58:47 mloitzl Exp $

CREATE TABLE liveuser_users (
auth_user_id varchar(32) NOT NULL default '0',
handle varchar(32) NOT NULL default '',
passwd varchar(32) NOT NULL default '',
lastlogin datetime default NULL,
owner_user_id bigint UNSIGNED default NULL,
owner_group_id int(11) UNSIGNED default NULL,
is_active char(1) NOT NULL default 'N',
UNIQUE KEY auth_user_id (auth_user_id,handle),
KEY auth_user_id_2 (auth_user_id,handle));

# Dumping data for table `liveuser_users`
#

INSERT INTO liveuser_users (auth_user_id, handle, passwd, lastlogin, owner_user_id, owner_group_id, is_active) VALUES ('c4ca4238a0b923820dcc509a6f75849b', 'boss', 'test', NULL, NULL, 1, 'Y');
INSERT INTO liveuser_users (auth_user_id, handle, passwd, lastlogin, owner_user_id, owner_group_id, is_active) VALUES ('c81e728d9d4c2f636f067f89cc14862c', 'hoss', 'bonanza', NULL, 1, NULL, 'Y');

#
# Dumping data for table `liveuser_areas`
#

INSERT INTO liveuser_areas (area_id, application_id, area_define_name) VALUES (1, 0, 'TestArea');
INSERT INTO liveuser_areas (area_id, application_id, area_define_name) VALUES (2, 0, 'Area51');
INSERT INTO liveuser_areas (area_id, application_id, area_define_name) VALUES (3, 0, 'Coffeemaker');

#
# Dumping data for table `liveuser_grouprights`
#

INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (1, 1, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (1, 2, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (1, 7, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (2, 3, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (2, 4, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (2, 5, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (2, 6, 1);
INSERT INTO liveuser_grouprights (group_id, right_id, right_level) VALUES (2, 7, 1);

#
# Dumping data for table `liveuser_groups`
#

INSERT INTO liveuser_groups (group_id, owner_user_id, owner_group_id, is_active) VALUES (1, 1, 1, 'Y');
INSERT INTO liveuser_groups (group_id, owner_user_id, owner_group_id, is_active) VALUES (2, 1, 1, 'Y');

#
# Dumping data for table `liveuser_groupusers`
#

INSERT INTO liveuser_groupusers (perm_user_id, group_id) VALUES (1, 1);
INSERT INTO liveuser_groupusers (perm_user_id, group_id) VALUES (1, 2);
INSERT INTO liveuser_groupusers (perm_user_id, group_id) VALUES (2, 1);

#
# Dumping data for table `liveuser_languages`
#

INSERT INTO liveuser_languages (language_id, two_letter_name, native_name) VALUES (1, 'de', 'Deutsch');
INSERT INTO liveuser_languages (language_id, two_letter_name, native_name) VALUES (2, 'en', 'English');

#
# Dumping data for table `liveuser_perm_users`
#

INSERT INTO liveuser_perm_users (perm_user_id, auth_user_id, perm_type, auth_container_name) VALUES (1, 'c4ca4238a0b923820dcc509a6f75849b', 1, '');
INSERT INTO liveuser_perm_users (perm_user_id, auth_user_id, perm_type, auth_container_name) VALUES (2, 'c81e728d9d4c2f636f067f89cc14862c', 1, '');

#
# Dumping data for table `liveuser_rights`
#

INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (1, 1, 'READ_TESTS', 'N', 'N', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (2, 1, 'WRITE_TESTS', 'N', 'N', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (3, 2, 'ACCESS', 'N', 'Y', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (4, 2, 'LAUNCH_ATOMIC_BOMB', 'N', 'N', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (5, 2, 'FLY_ALIEN_SPACE_CRAFT', 'N', 'N', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (6, 3, 'MAKE_COFFEE', 'N', 'N', 'N');
INSERT INTO liveuser_rights (right_id, area_id, right_define_name, has_implied, has_level, has_scope) VALUES (7, 3, 'DRINK_COFFEE', 'N', 'Y', 'N');

#
# Dumping data for table `liveuser_userrights`
#

INSERT INTO liveuser_userrights (perm_user_id, right_id, right_level) VALUES (1, 3, 3);
INSERT INTO liveuser_userrights (perm_user_id, right_id, right_level) VALUES (1, 7, 3);

#
# Dumping data for table `liveuser_translations`
#

INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 2, 1, 'TestGebiet', 'Ein Gebiet zum testen.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 2, 2, 'TestArea', 'An Area for testing.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 2, 1, 'Area51', 'Jeder kennt dieses Gebiet.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 2, 2, 'Area51', 'Everybody knows this area.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (3, 2, 1, 'Kaffeemaschine', 'Kaffeemaschine Typ 165-X');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (3, 2, 2, 'Coffeemaker', 'Coffeemaker type 165-X.');

INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 3, 1, 'Dummies', 'Die Dumm
y-Gruppe');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 3, 2, 'Dummies', 'The dummy group');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 3, 1, 'Genies', 'Die wahren Genies (verkannt, aber brilliant)');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 3, 2, 'Genies', 'The true genii (unrecognized but brilliant)');

INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 5, 1, 'Lesen', 'Der Benutzer darf etwas lesen.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 5, 1, 'Schreiben', 'Der Benutzer darf etwas schreiben.');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (3, 5, 1, 'Zugangang', '"Sesam ?ffne dich"-Recht');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (4, 5, 1, 'Feuer', 'Atombombe z?nden');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (5, 5, 1, 'Lift up', 'Alien Raumschiff fliegen');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (6, 5, 1, 'Kaffee machen', 'Kaffeepulver, Wasser, einschalten, warten ...');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (7, 5, 1, 'Kaffee trinken', 'Ahh, Koffein');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 5, 2, 'Read', 'Read authority');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 5, 2, 'Write', 'Write something new');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (3, 5, 2, 'Access', 'Open the doors');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (4, 5, 2, 'Fire', 'Launch the atomic bombs');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (5, 5, 2, 'Lift up', 'Let\'s fly an alian space craft');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (6, 5, 2, 'Make coffee', 'coffee, water, switch it on, wait ...');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (7, 5, 2, 'Drink coffee', 'Ahh, caffeine');

INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (1, 4, 2, 'Dutch', 'Dutch language');
INSERT INTO liveuser_translations (section_id, section_type, language_id, name, description) VALUES (2, 4, 1, 'Engels', 'Engelse taal');

