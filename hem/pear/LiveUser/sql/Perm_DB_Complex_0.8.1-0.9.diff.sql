#
# MySQL Diff 1.3.3
#
# http://www.mysqldiff.com
# (c) 2001-2003 Lippe-Net Online-Service
#
# Create time: 06.10.2003 19:52
#
# --------------------------------------------------------
# Source info
# Host: localhost
# Database: lu_source
# --------------------------------------------------------
# Target info
# Host: localhost
# Database: lu_destination
# --------------------------------------------------------
#

#
# DDL START
#
ALTER TABLE `liveuser_applications`
    DROP `application_comment`;

ALTER TABLE `liveuser_area_admin_areas`
    ADD `perm_user_id` bigint(20) NOT NULL DEFAULT '0' AFTER area_id,
    DROP `user_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`area_id`, `perm_user_id`);

ALTER TABLE `liveuser_areas`
    DROP `area_comment`;

ALTER TABLE `liveuser_groups`
    ADD `owner_perm_user_id` bigint(20) NOT NULL DEFAULT '0' AFTER group_id,
    DROP `owner_user_id`,
    DROP `group_comment`,
    ADD INDEX `IDX_liveuser_groups_owner_perm_user_id` (`owner_perm_user_id`),
    DROP INDEX IDX_liveuser_groups_owner_user_id;

ALTER TABLE `liveuser_groupusers`
    ADD `perm_user_id` bigint(20) NOT NULL DEFAULT '0' FIRST,
    DROP `user_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`group_id`, `perm_user_id`);

ALTER TABLE `liveuser_languages`
    ALTER `native_name`  DROP DEFAULT;

ALTER TABLE `liveuser_perm_users`
    ADD `perm_user_id` bigint(20) unsigned NOT NULL DEFAULT '0' FIRST,
    DROP `user_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`perm_user_id`);

ALTER TABLE `liveuser_rights`
    DROP `right_comment`;

ALTER TABLE `liveuser_userrights`
    ADD `perm_user_id` bigint(20) NOT NULL DEFAULT '0' FIRST,
    DROP `user_id`,
    DROP PRIMARY KEY,
    ADD PRIMARY KEY (`right_id`, `perm_user_id`);

#
# DDL END
#
