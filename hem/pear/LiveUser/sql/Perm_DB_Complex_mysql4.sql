#========================================================================== #
# Project Filename:    C:\web\pear\Perm_LiveUser\sql\perm_db_erd_mysql4.dez #
# Project Name:        LiveUser ERD                                         #
# Author:                                                                   #
# DBMS:                MySQL 4                                              #
# Copyright:                                                                #
# Generated on:        2004-04-07 02:50:54                                  #
#========================================================================== #

#========================================================================== #
#  Tables                                                                   #
#========================================================================== #

CREATE TABLE liveuser_area_admin_areas (
    area_id INTEGER(11) UNSIGNED NOT NULL,
    perm_user_id INTEGER(11) UNSIGNED NOT NULL,
    CONSTRAINT pk_liveuser_area_admin_areas PRIMARY KEY (area_id, perm_user_id),
    KEY admin_areas_area_id(area_id),
    KEY admin_areas_user_id(perm_user_id)
);

CREATE TABLE liveuser_areas (
    area_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    application_id INTEGER(11) UNSIGNED NOT NULL,
    area_define_name VARCHAR(20) NOT NULL,
    CONSTRAINT pk_liveuser_areas PRIMARY KEY (area_id),
    KEY areas_application_id(application_id)
);

CREATE TABLE liveuser_right_implied (
    right_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    implied_right_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT pk_liveuser_right_implied PRIMARY KEY (right_id, implied_right_id),
    KEY right_implied_right_id(right_id),
    KEY implied_implied_right_id(implied_right_id)
);

CREATE TABLE liveuser_right_scopes (
    right_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_type TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT pk_liveuser_right_scopes PRIMARY KEY (right_id),
    KEY right_scopes_right_id(right_id)
);

CREATE TABLE liveuser_rights (
    right_id INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
    area_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_define_name VARCHAR(50) NOT NULL,
    has_implied CHAR(1) NOT NULL DEFAULT 'N',
    has_level CHAR(1) NOT NULL DEFAULT 'N',
    has_scope CHAR(1) NOT NULL DEFAULT 'N',
    CONSTRAINT pk_liveuser_rights PRIMARY KEY (right_id),
    UNIQUE right_define_name (right_define_name),
    KEY rights_area_id(area_id)
);

CREATE TABLE liveuser_group_subgroups (
    group_id INTEGER(11) UNSIGNED NOT NULL,
    subgroup_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT pk_liveuser_group_subgroups PRIMARY KEY (group_id, subgroup_id),
    KEY subgroups_group_id(group_id),
    KEY subgroups_subgroup_id(subgroup_id)
);

CREATE TABLE liveuser_grouprights (
    group_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_level TINYINT(3) UNSIGNED DEFAULT NULL,
    CONSTRAINT pk_liveuser_grouprights PRIMARY KEY (group_id, right_id),
    KEY grouprights_group_id(group_id),
    KEY grouprights_right_id(right_id)
);

CREATE TABLE liveuser_groups (
    group_id INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
    owner_user_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    owner_group_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    is_active CHAR(1) NOT NULL DEFAULT 'N',
    group_define_name VARCHAR(32) NOT NULL,
    CONSTRAINT pk_liveuser_groups PRIMARY KEY (group_id),
    KEY groups_owner_user_id(owner_user_id),
    KEY groups_owner_group_id(owner_group_id)
);

CREATE TABLE liveuser_groupusers (
    perm_user_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    group_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT pk_liveuser_groupusers PRIMARY KEY (perm_user_id, group_id),
    KEY groupusers_user_id(perm_user_id),
    KEY groupusers_group_id(group_id)
);

CREATE TABLE liveuser_languages (
    language_id SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
    native_name VARCHAR(50),
    two_letter_name CHAR(2),
    CONSTRAINT pk_liveuser_languages PRIMARY KEY (language_id),
    UNIQUE two_letter_name (two_letter_name)
);

CREATE TABLE liveuser_userrights (
    perm_user_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    right_level TINYINT(3) UNSIGNED DEFAULT NULL,
    CONSTRAINT pk_liveuser_userrights PRIMARY KEY (perm_user_id, right_id),
    KEY idx_userrights_user_id(perm_user_id),
    KEY idx_userrights_right_id(right_id)
);

CREATE TABLE liveuser_perm_users (
    perm_user_id INTEGER(11) UNSIGNED NOT NULL DEFAULT 0,
    auth_user_id VARCHAR(32) NOT NULL DEFAULT '0',
    perm_type TINYINT(3) UNSIGNED DEFAULT NULL,
    auth_container_name VARCHAR(32) NOT NULL,
    CONSTRAINT pk_liveuser_perm_users PRIMARY KEY (perm_user_id)
);

CREATE TABLE liveuser_applications (
    application_id INTEGER(10) UNSIGNED NOT NULL DEFAULT 0,
    application_define_name VARCHAR(20) NOT NULL,
    CONSTRAINT pk_liveuser_applications PRIMARY KEY (application_id),
    UNIQUE (application_define_name)
);

CREATE TABLE liveuser_translations (
    section_id INTEGER(16) UNSIGNED NOT NULL DEFAULT 0,
    section_type TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
    language_id SMALLINT(5) UNSIGNED DEFAULT NULL,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    CONSTRAINT pk_liveuser_translations PRIMARY KEY (section_id, section_type),
    UNIQUE uc_ltranslations_section_id (section_id),
    UNIQUE uc_translations_section_type (section_type)
);

#========================================================================== #
#  Foreign Keys                                                             #
#========================================================================== #

ALTER TABLE liveuser_area_admin_areas
    ADD CONSTRAINT fk_areas_area_admin_areas FOREIGN KEY (area_id) REFERENCES liveuser_areas (area_id);

ALTER TABLE liveuser_area_admin_areas
    ADD CONSTRAINT fk_perm_users_area_admin_areas FOREIGN KEY (perm_user_id) REFERENCES liveuser_perm_users (perm_user_id);

ALTER TABLE liveuser_areas
    ADD CONSTRAINT fk_applications_areas FOREIGN KEY (application_id) REFERENCES liveuser_applications (application_id);

ALTER TABLE liveuser_right_implied
    ADD CONSTRAINT fk_rights_right_implied FOREIGN KEY (right_id) REFERENCES liveuser_rights (right_id);

ALTER TABLE liveuser_right_implied
    ADD CONSTRAINT fk_rights_implied_right_implied FOREIGN KEY (implied_right_id) REFERENCES liveuser_rights (right_id);

ALTER TABLE liveuser_right_scopes
    ADD CONSTRAINT fk_rights_right_scopes FOREIGN KEY (right_id) REFERENCES liveuser_rights (right_id);

ALTER TABLE liveuser_rights
    ADD CONSTRAINT liveuser_areas_liveuser_rights FOREIGN KEY (area_id) REFERENCES liveuser_areas (area_id);

ALTER TABLE liveuser_group_subgroups
    ADD CONSTRAINT fk_groups_group_subgroups_group FOREIGN KEY (group_id) REFERENCES liveuser_groups (group_id);

ALTER TABLE liveuser_group_subgroups
    ADD CONSTRAINT fk_groups_group_subgroups FOREIGN KEY (subgroup_id) REFERENCES liveuser_groups (group_id);

ALTER TABLE liveuser_grouprights
    ADD CONSTRAINT fk_groups_grouprights FOREIGN KEY (group_id) REFERENCES liveuser_groups (group_id);

ALTER TABLE liveuser_grouprights
    ADD CONSTRAINT fk_rights_grouprights FOREIGN KEY (right_id) REFERENCES liveuser_rights (right_id);

ALTER TABLE liveuser_groups
    ADD CONSTRAINT fk_perm_users_groups FOREIGN KEY (owner_user_id) REFERENCES liveuser_perm_users (perm_user_id);

ALTER TABLE liveuser_groups
    ADD CONSTRAINT fk_groups_groups FOREIGN KEY (owner_group_id) REFERENCES liveuser_groups (group_id);

ALTER TABLE liveuser_groupusers
    ADD CONSTRAINT fk_perm_users_groupusers FOREIGN KEY (perm_user_id) REFERENCES liveuser_perm_users (perm_user_id);

ALTER TABLE liveuser_groupusers
    ADD CONSTRAINT fk_groups_groupusers FOREIGN KEY (group_id) REFERENCES liveuser_groups (group_id);

ALTER TABLE liveuser_userrights
    ADD CONSTRAINT fk_perm_users_userrights FOREIGN KEY (perm_user_id) REFERENCES liveuser_perm_users (perm_user_id);

ALTER TABLE liveuser_userrights
    ADD CONSTRAINT fk_rights_userrights FOREIGN KEY (right_id) REFERENCES liveuser_rights (right_id);

ALTER TABLE liveuser_translations
    ADD FOREIGN KEY (language_id) REFERENCES liveuser_languages (language_id);
