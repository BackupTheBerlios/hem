/*==========================================================================*/
/* Project Filename:    C:\web\pear\Perm_LiveUser\sql\perm_db_erd_postgresql.dez*/
/* Project Name:        LiveUser ERD                                        */
/* Author:                                                                  */
/* DBMS:                PostgreSQL 7                                        */
/* Copyright:                                                               */
/* Generated on:        2004-04-07 02:49:37                                 */
/*==========================================================================*/

/*==========================================================================*/
/*  Tables                                                                  */
/*==========================================================================*/

CREATE TABLE liveuser_area_admin_areas (
    area_id INT4 NOT NULL,
    perm_user_id INT4 NOT NULL,
    CONSTRAINT pk_liveuser_area_admin_areas PRIMARY KEY (area_id, perm_user_id)
);

CREATE TABLE liveuser_areas (
    area_id INT4 DEFAULT 0 NOT NULL,
    application_id INT4 NOT NULL,
    area_define_name VARCHAR(20) NOT NULL,
    CONSTRAINT pk_liveuser_areas PRIMARY KEY (area_id)
);

CREATE TABLE liveuser_right_implied (
    right_id INT4 DEFAULT 0 NOT NULL,
    implied_right_id INT4 DEFAULT 0 NOT NULL,
    CONSTRAINT pk_liveuser_right_implied PRIMARY KEY (right_id, implied_right_id)
);

CREATE TABLE liveuser_right_scopes (
    right_id INT4 DEFAULT 0 NOT NULL,
    right_type INT2 DEFAULT 0 NOT NULL,
    CONSTRAINT pk_liveuser_right_scopes PRIMARY KEY (right_id)
);

CREATE TABLE liveuser_rights (
    right_id INT4 DEFAULT 0 NOT NULL,
    area_id INT4 DEFAULT 0 NOT NULL,
    right_define_name VARCHAR(50) NOT NULL,
    has_implied CHAR(1) DEFAULT 'N' NOT NULL,
    has_level CHAR(1) DEFAULT 'N' NOT NULL,
    has_scope CHAR(1) DEFAULT 'N' NOT NULL,
    CONSTRAINT pk_liveuser_rights PRIMARY KEY (right_id),
    CONSTRAINT right_define_name UNIQUE (right_define_name)
);

CREATE TABLE liveuser_group_subgroups (
    group_id INT4 NOT NULL,
    subgroup_id INT4 DEFAULT 0 NOT NULL,
    CONSTRAINT pk_liveuser_group_subgroups PRIMARY KEY (group_id, subgroup_id)
);

CREATE TABLE liveuser_grouprights (
    group_id INT4 DEFAULT 0 NOT NULL,
    right_id INT4 DEFAULT 0 NOT NULL,
    right_level INT2 DEFAULT NULL,
    CONSTRAINT pk_liveuser_grouprights PRIMARY KEY (group_id, right_id)
);

CREATE TABLE liveuser_groups (
    group_id INT8 DEFAULT 0 NOT NULL,
    owner_user_id INT4 DEFAULT 0 NOT NULL,
    owner_group_id INT4 DEFAULT 0 NOT NULL,
    is_active CHAR(1) DEFAULT 'N' NOT NULL,
    group_define_name VARCHAR(32) NOT NULL,
    CONSTRAINT pk_liveuser_groups PRIMARY KEY (group_id)
);

CREATE TABLE liveuser_groupusers (
    perm_user_id INT4 DEFAULT 0 NOT NULL,
    group_id INT4 DEFAULT 0 NOT NULL,
    CONSTRAINT pk_liveuser_groupusers PRIMARY KEY (perm_user_id, group_id)
);

CREATE TABLE liveuser_languages (
    language_id INT2 DEFAULT 0 NOT NULL,
    native_name VARCHAR(50),
    two_letter_name CHAR(2),
    CONSTRAINT pk_liveuser_languages PRIMARY KEY (language_id),
    CONSTRAINT two_letter_name UNIQUE (two_letter_name)
);

CREATE TABLE liveuser_userrights (
    perm_user_id INT4 DEFAULT 0 NOT NULL,
    right_id INT4 DEFAULT 0 NOT NULL,
    right_level INT2 DEFAULT NULL,
    CONSTRAINT pk_liveuser_userrights PRIMARY KEY (perm_user_id, right_id)
);

CREATE TABLE liveuser_perm_users (
    perm_user_id INT4 DEFAULT 0 NOT NULL,
    auth_user_id VARCHAR(32) DEFAULT '0' NOT NULL,
    perm_type INT2 DEFAULT NULL,
    auth_container_name VARCHAR(32) NOT NULL,
    CONSTRAINT pk_liveuser_perm_users PRIMARY KEY (perm_user_id)
);

CREATE TABLE liveuser_applications (
    application_id INT4 DEFAULT 0 NOT NULL,
    application_define_name VARCHAR(20) NOT NULL UNIQUE,
    CONSTRAINT pk_liveuser_applications PRIMARY KEY (application_id)
);

CREATE TABLE liveuser_translations (
    section_id INT8 DEFAULT 0 NOT NULL CONSTRAINT uc_ltranslations_section_id UNIQUE,
    section_type INT2 DEFAULT 0 NOT NULL CONSTRAINT uc_translations_section_type UNIQUE,
    language_id INT4 DEFAULT NULL,
    name VARCHAR(50) NOT NULL,
    description VARCHAR(255),
    CONSTRAINT pk_liveuser_translations PRIMARY KEY (section_id, section_type)
);

/*==========================================================================*/
/*  Foreign Keys                                                            */
/*==========================================================================*/

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

/*==========================================================================*/
/*  Indexes                                                                 */
/*==========================================================================*/

CREATE INDEX admin_areas_area_id ON liveuser_area_admin_areas (area_id);

CREATE INDEX admin_areas_user_id ON liveuser_area_admin_areas (perm_user_id);

CREATE INDEX areas_application_id ON liveuser_areas (application_id);

CREATE INDEX right_implied_right_id ON liveuser_right_implied (right_id);

CREATE INDEX implied_implied_right_id ON liveuser_right_implied (implied_right_id);

CREATE INDEX right_scopes_right_id ON liveuser_right_scopes (right_id);

CREATE INDEX rights_area_id ON liveuser_rights (area_id);

CREATE INDEX subgroups_group_id ON liveuser_group_subgroups (group_id);

CREATE INDEX subgroups_subgroup_id ON liveuser_group_subgroups (subgroup_id);

CREATE INDEX grouprights_group_id ON liveuser_grouprights (group_id);

CREATE INDEX grouprights_right_id ON liveuser_grouprights (right_id);

CREATE INDEX groups_owner_user_id ON liveuser_groups (owner_user_id);

CREATE INDEX groups_owner_group_id ON liveuser_groups (owner_group_id);

CREATE INDEX groupusers_user_id ON liveuser_groupusers (perm_user_id);

CREATE INDEX groupusers_group_id ON liveuser_groupusers (group_id);

CREATE INDEX idx_userrights_user_id ON liveuser_userrights (perm_user_id);

CREATE INDEX idx_userrights_right_id ON liveuser_userrights (right_id);

/*==========================================================================*/
/*  Sequences                                                               */
/*==========================================================================*/

/*==========================================================================*/
/*  Views                                                                   */
/*==========================================================================*/

/*==========================================================================*/
/*  Procedures                                                              */
/*==========================================================================*/

/*==========================================================================*/
/*  Triggers                                                                */
/*==========================================================================*/

/*==========================================================================*/
/*  Comments                                                                */
/*==========================================================================*/
