CREATE TABLE IF NOT EXISTS `#__jdownloads_categories` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `cat_dir` text NOT NULL,
	  `cat_dir_parent` text NOT NULL,
	  `parent_id` int(11) unsigned NOT NULL DEFAULT '0',
	  `lft` int(11) NOT NULL DEFAULT '0',
	  `rgt` int(11) NOT NULL DEFAULT '0',
	  `level` int(10) unsigned NOT NULL DEFAULT '0',
	  `title` varchar(255) NOT NULL DEFAULT '',
	  `alias` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	  `description` text NOT NULL,
	  `pic` varchar(255) NOT NULL DEFAULT '',
	  `access` int(10) unsigned NOT NULL DEFAULT '0',
	  `user_access` int(11) unsigned NOT NULL DEFAULT '0',
	  `metakey` text NOT NULL,
	  `metadesc` text NOT NULL,
	  `robots` varchar(255) NOT NULL DEFAULT '',
	  `created_user_id` int(10) unsigned NOT NULL DEFAULT '0',
	  `created_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `modified_user_id` int(10) NOT NULL DEFAULT '0',
	  `modified_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `language` char(7) NOT NULL DEFAULT '',
	  `notes` text NOT NULL,
	  `views` int(10) unsigned NOT NULL DEFAULT '0',
	  `params` text NOT NULL,
	  `password` varchar(100) NOT NULL DEFAULT '',
	  `password_md5` varchar(100) NOT NULL DEFAULT '',
	  `ordering` int(11) NOT NULL DEFAULT '0',
	  `published` tinyint(1) NOT NULL DEFAULT '0',
	  `checked_out` int(11) NOT NULL DEFAULT '0',
	  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
	  PRIMARY KEY (`id`),
	  KEY `idx_access` (`access`),
	  KEY `idx_checked_out` (`checked_out`),
	  KEY `idx_left_right` (`lft`,`rgt`),
	  KEY `idx_alias` (`alias`(100)),
	  KEY `idx_published` (`published`),
	  KEY `idx_language` (`language`)
	) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
	
	INSERT INTO `#__jdownloads_categories` (`id`, `cat_dir`, `cat_dir_parent`, `parent_id`, `lft`, `rgt`, `level`, `title`, `alias`, `description`, `pic`, `access`, `metakey`, `metadesc`, `robots`, `created_user_id`, `created_time`, `modified_user_id`, `modified_time`, `language`, `notes`, `views`, `params`, `password`, `password_md5`, `ordering`, `published`, `checked_out`, `checked_out_time`, `asset_id`) VALUES
(1, '', '', 0, 0, 1, 0, 'ROOT', 'root', '', '', 1, '', '', '', 0, '0000-00-00 00:00:00', 0, '0000-00-00 00:00:00', '*', '', 0, '', '', '', 0, 1, 0, '0000-00-00 00:00:00', 0) ON DUPLICATE KEY UPDATE `id` = 1;	
		
	CREATE TABLE IF NOT EXISTS `#__jdownloads_files` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(255) NOT NULL DEFAULT '',
	  `alias` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '',
	  `description` longtext NOT NULL,
	  `description_long` longtext NOT NULL,
	  `file_pic` varchar(255) NOT NULL DEFAULT '',
	  `images` text NOT NULL,
	  `price` varchar(20) NOT NULL DEFAULT '',
	  `release` varchar(255) NOT NULL DEFAULT '',
	  `file_language` int(3) NOT NULL DEFAULT '0',
	  `system` int(3) NOT NULL DEFAULT '0',
	  `license` varchar(255) NOT NULL DEFAULT '',
	  `url_license` varchar(255) NOT NULL DEFAULT '',
	  `license_agree` tinyint(1) NOT NULL DEFAULT '0',
	  `size` varchar(255) NOT NULL DEFAULT '',
	  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `file_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `use_timeframe` tinyint(1) NOT NULL DEFAULT '0',
	  `url_download` varchar(400) NOT NULL DEFAULT '' COMMENT 'contains only the assigned filename',
	  `preview_filename` varchar(400) NOT NULL DEFAULT '',
      `other_file_id` int(11) NOT NULL DEFAULT '0',
	  `md5_value` varchar(100) NOT NULL DEFAULT '',
	  `sha1_value` varchar(100) NOT NULL DEFAULT '',
	  `extern_file` varchar(600) NOT NULL DEFAULT '',
	  `extern_site` tinyint(1) NOT NULL DEFAULT '0',
	  `mirror_1` varchar(600) NOT NULL DEFAULT '',
	  `mirror_2` varchar(600) NOT NULL DEFAULT '',
	  `extern_site_mirror_1` tinyint(1) NOT NULL DEFAULT '0',
	  `extern_site_mirror_2` tinyint(1) NOT NULL DEFAULT '0',
	  `url_home` varchar(255) NOT NULL DEFAULT '',
	  `author` varchar(255) NOT NULL DEFAULT '',
	  `url_author` varchar(255) NOT NULL DEFAULT '',
	  `created_by` int(11) NOT NULL DEFAULT '0',
	  `created_mail` varchar(255) NOT NULL DEFAULT '',
	  `modified_by` int(11) NOT NULL DEFAULT '0',
	  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `submitted_by` int(11) NOT NULL DEFAULT '0',
	  `set_aup_points` tinyint(1) NOT NULL DEFAULT '0',
	  `downloads` int(11) NOT NULL DEFAULT '0',
	  `catid` int(11) NOT NULL DEFAULT '0',
	  `notes` text NOT NULL,
	  `changelog` text NOT NULL,
	  `password` varchar(100) NOT NULL DEFAULT '',
	  `password_md5` varchar(100) NOT NULL DEFAULT '',
	  `views` int(11) NOT NULL DEFAULT '0',
	  `metakey` text NOT NULL,
	  `metadesc` text NOT NULL,
	  `robots` varchar(255) NOT NULL DEFAULT '',
	  `update_active` tinyint(1) NOT NULL DEFAULT '0',
	  `access` int(10) unsigned NOT NULL DEFAULT '0',
	  `user_access` int(11) unsigned NOT NULL DEFAULT '0',
	  `language` char(7) NOT NULL DEFAULT '',
	  `ordering` int(11) NOT NULL DEFAULT '0',
	  `featured` tinyint(1) NOT NULL DEFAULT '0',
	  `published` tinyint(1) NOT NULL DEFAULT '0',
	  `checked_out` int(11) NOT NULL DEFAULT '0',
	  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `asset_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'FK to the #__assets table',
	  PRIMARY KEY (`id`),
	  KEY `idx_catid` (`catid`),
	  KEY `idx_access` (`access`),
	  KEY `idx_user_access` (`user_access`),
	  KEY `idx_published` (`published`),
	  KEY `idx_checked_out` (`checked_out`),
	  KEY `idx_alias` (`alias`(191)),
	  KEY `idx_created_by` (`created_by`),
	  KEY `idx_language` (`language`),
	  KEY `idx_featured` (`featured`)	  
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

	CREATE TABLE IF NOT EXISTS `#__jdownloads_licenses` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(255) NOT NULL DEFAULT '',
	  `alias` varchar(255) NOT NULL DEFAULT '',
	  `description` longtext NOT NULL,
	  `url` varchar(255) NOT NULL DEFAULT '',
	  `language` char(7) NOT NULL DEFAULT '',
	  `checked_out` int(11) NOT NULL DEFAULT '0',
	  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `published` tinyint(1) NOT NULL DEFAULT '0',
	  `ordering` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
 	  KEY `idx_checked_out` (`checked_out`),
  	  KEY `idx_language` (`language`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

	CREATE TABLE IF NOT EXISTS `#__jdownloads_templates` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `template_name` varchar(255) NOT NULL DEFAULT '',
	  `template_typ` tinyint(2) NOT NULL DEFAULT '0',
	  `template_header_text` longtext NOT NULL,
	  `template_subheader_text` longtext NOT NULL,
	  `template_footer_text` longtext NOT NULL,
	  `template_before_text` text NOT NULL,
	  `template_text` longtext NOT NULL,
	  `template_after_text` text NOT NULL,
	  `template_active` tinyint(1) NOT NULL DEFAULT '0',
	  `locked` tinyint(1) NOT NULL DEFAULT '0',
	  `note` text NOT NULL,
	  `cols` tinyint(1) NOT NULL DEFAULT '1',
	  `uses_bootstrap` tinyint(1) NOT NULL DEFAULT '0',
	  `uses_w3css` tinyint(1) NOT NULL DEFAULT '0',
	  `checkbox_off` tinyint(1) NOT NULL DEFAULT '0',
	  `use_to_view_subcats` tinyint(1) NOT NULL DEFAULT '0',
	  `symbol_off` tinyint(1) NOT NULL DEFAULT '0',
	  `language` char(7) NOT NULL DEFAULT '*',
	  `checked_out` int(11) NOT NULL DEFAULT '0',
	  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `preview_id` int(3) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
   	  KEY `idx_checked_out` (`checked_out`),
  	  KEY `idx_template_typ` (`template_typ`),
	  KEY `idx_language` (`language`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
		
	CREATE TABLE IF NOT EXISTS `#__jdownloads_logs` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `type` tinyint(1) NOT NULL DEFAULT '1',
	  `log_file_id` int(11) NOT NULL DEFAULT '0',
	  `log_file_size` varchar(20) NOT NULL DEFAULT '',
	  `log_file_name` varchar(255) NOT NULL DEFAULT '',
	  `log_title` varchar(255) NOT NULL DEFAULT '',
	  `log_ip` varchar(25) NOT NULL DEFAULT '',
	  `log_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `log_user` int(11) NOT NULL DEFAULT '0',
	  `log_browser` varchar(255) NOT NULL DEFAULT '',
	  `language` char(7) NOT NULL DEFAULT '',
	  `ordering` int(11) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`),
	  KEY `idx_type` (`type`),
	  KEY `idx_log_user` (`log_user`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;

    CREATE TABLE IF NOT EXISTS `#__jdownloads_ratings` (
        `file_id` int(11) NOT NULL default '0',
        `rating_sum` int(11) unsigned NOT NULL default '0',
        `rating_count` int(11) unsigned NOT NULL default '0',
        `lastip` varchar(50) NOT NULL default '',
        PRIMARY KEY  (`file_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;
		
	CREATE TABLE IF NOT EXISTS `#__jdownloads_usergroups_limits` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`importance` SMALLINT( 6 ) NOT NULL DEFAULT '0',
	`group_id` INT( 10 ) NOT NULL DEFAULT '0',
	`download_limit_daily` INT(10) NOT NULL DEFAULT '0',
	`download_limit_daily_msg` text NOT NULL,
	`download_limit_weekly` INT(10) NOT NULL DEFAULT '0',
	`download_limit_weekly_msg` text NOT NULL,
	`download_limit_monthly` INT(10) NOT NULL DEFAULT '0',
	`download_limit_monthly_msg` text NOT NULL,
	`download_volume_limit_daily` INT(10) NOT NULL DEFAULT '0',
	`download_volume_limit_daily_msg` text NOT NULL,
	`download_volume_limit_weekly` INT(10) NOT NULL DEFAULT '0',
	`download_volume_limit_weekly_msg` text NOT NULL,
	`download_volume_limit_monthly` INT(10) NOT NULL DEFAULT '0',
	`download_volume_limit_monthly_msg` text NOT NULL,
	`how_many_times` INT( 10 ) NOT NULL DEFAULT '0',
	`how_many_times_msg` text NOT NULL,
	`download_limit_after_this_time` INT( 4 ) NOT NULL DEFAULT '60',
	`transfer_speed_limit_kb` INT(10) NOT NULL DEFAULT '0',
	`upload_limit_daily` INT(10) NOT NULL DEFAULT '0',
	`upload_limit_daily_msg` text NOT NULL,
	`view_captcha` tinyint(1) NOT NULL DEFAULT '0',
	`view_inquiry_form` tinyint(1) NOT NULL DEFAULT '0',
	`view_report_form` tinyint(1) NOT NULL DEFAULT '0',
	`must_form_fill_out` tinyint(1) NOT NULL DEFAULT '0',
	`form_fieldset` CHAR( 100 ) NOT NULL DEFAULT '',
	`countdown_timer_duration` INT(10) NOT NULL DEFAULT '0',
	`countdown_timer_msg` text NOT NULL,
	`may_edit_own_downloads` tinyint(1) NOT NULL DEFAULT '0',
	`may_edit_all_downloads` tinyint(1) NOT NULL DEFAULT '0',
	`use_private_area` tinyint(1) NOT NULL DEFAULT '0',
	`view_user_his_limits` tinyint(1) NOT NULL DEFAULT '0',
	`view_user_his_limits_msg` text NOT NULL,
	`uploads_only_in_cat_id` INT( 11 ) NOT NULL DEFAULT '0',
	`uploads_auto_publish` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`uploads_allowed_types` TEXT NOT NULL,
	`uploads_use_editor` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`uploads_use_tabs` TINYINT( 1 ) NOT NULL DEFAULT '1',	
	`uploads_allowed_preview_types` TEXT NOT NULL,
	`uploads_maxfilesize_kb` CHAR( 15 ) NOT NULL DEFAULT '2048',
	`uploads_form_text` TEXT NOT NULL,
	`uploads_max_amount_images` INT( 3 ) NOT NULL DEFAULT '3',
	`uploads_can_change_category` tinyint(1) NOT NULL DEFAULT '1',	
	`uploads_default_access_level` INT( 10 ) NOT NULL DEFAULT '0',
	`uploads_view_upload_icon` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`uploads_allow_custom_tags` TINYINT( 1 ) NOT NULL DEFAULT '1',	
	`form_title` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_alias` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_alias_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_version` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_version_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_access` TINYINT( 1 ) NOT NULL DEFAULT '0',	
	`form_access_x` TINYINT( 1 ) NOT NULL DEFAULT '0',	
	`form_user_access` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_update_active` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_file_language` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_file_language_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_file_system` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_file_system_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_license` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_license_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_confirm_license` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_short_desc` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_short_desc_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_long_desc` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_long_desc_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_changelog` TINYINT( 1 ) NOT NULL DEFAULT '1',		
	`form_changelog_x` TINYINT( 1 ) NOT NULL DEFAULT '0',		
	`form_category` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_view_access` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_language` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_language_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_published` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_featured` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_creation_date` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_creation_date_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_modified_date` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_timeframe` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_views` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_downloaded` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_ordering` TINYINT( 1 ) NOT NULL DEFAULT '0', 
	`form_password` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_password_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_price` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_price_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_website` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_website_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_author_name` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_author_name_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_author_mail` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_author_mail_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_file_pic` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_file_pic_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_select_main_file` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_select_main_file_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_file_size` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_file_date` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_file_date_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_select_preview_file` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_select_preview_file_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_external_file` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_external_file_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_mirror_1` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_mirror_1_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_mirror_2` TINYINT NOT NULL DEFAULT '1',
	`form_mirror_2_x` TINYINT NOT NULL DEFAULT '0',
	`form_images` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_images_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_meta_desc` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_meta_key` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_robots` TINYINT( 1 ) NOT NULL DEFAULT '1',
	`form_tags` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_select_from_other` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_created_id` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`form_created_id_x` TINYINT( 1 ) NOT NULL DEFAULT '0',
	`notes` text NOT NULL,
    `checked_out` int(11) NOT NULL default '0',
	`checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',	
	PRIMARY KEY (`id`),
	KEY `idx_checked_out` (`checked_out`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;