<?php
/*Create tables in database during pluin activation*/
function ntzcrm_installer()
{
	global $wpdb;
	$sql1 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . NTZCRMPRIFIX . "membership_tags` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL, 
			`plan_link` varchar(255) NULL DEFAULT NULL, 
			`category_id` int(11) NULL DEFAULT NULL,
			`status` TINYINT(2) NOT NULL DEFAULT '1',
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";

	$sql2 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . NTZCRMPRIFIX . "user_tags` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`tag_id` bigint(20) NOT NULL,
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";

	$sql3 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . NTZCRMPRIFIX . "post_tags` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`post_id` bigint(20) NOT NULL,
			`tag_id` bigint(20) NOT NULL,
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";

	$sql4 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . NTZCRMPRIFIX . "user_logs` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL, 
			`login` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`logout` datetime DEFAULT NULL,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";

	$sql5 = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . NTZCRMPRIFIX . "user_activites` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`post_id` bigint(20) NOT NULL,
			`view_count` int(11) NOT NULL DEFAULT '0',
			`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";

		$sql6 ="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.NTZCRMPRIFIX."deleted_tags` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`tag_id` bigint(20) NOT NULL,
			`user_tags` TEXT NULL DEFAULT NULL,
			`post_tags` TEXT NULL DEFAULT NULL, 
			`modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci AUTO_INCREMENT=1;";			

		$sql7="CREATE TABLE IF NOT EXISTS `".$wpdb->prefix.NTZCRMPRIFIX."user_profile_logs` (
			`id` bigint(20) NOT NULL AUTO_INCREMENT,
			`user_id` bigint(20) NOT NULL,
			`old_user_email` varchar(255)  NULL DEFAULT NULL,
			`user_new_email` varchar(255)  NULL DEFAULT NULL,  
			`created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql1);
	dbDelta($sql2);
	dbDelta($sql3);
	dbDelta($sql4);
	dbDelta($sql5);
	dbDelta($sql6);
	dbDelta($sql7);
	// add option is plugin activate or not 
	ntzcrm_add_option();
	ntzcrm_pages();
	ntzcrm_media(NTZCRM_PLUGIN_URL . 'images/pub-access-disabled.png');
	ntzcrm_media(NTZCRM_PLUGIN_URL . 'images/pub-access-enabled.png');
}

function ntzcrm_pages()
{
	$pages = array(
		array(
			'post_title'    => 'CRM Memberships - Login',
			'post_content'  => '[' . NTZCRMPRIFIX . 'login]',
			'post_status'   => 'publish',
			'post_type'     => 'page',
		), array(
			'post_title'    => 'CRM Memberships - Publications',
			'post_content'  => '[' . NTZCRMPRIFIX . 'publications]',
			'post_status'   => 'publish',
			'post_type'     => 'page',
		)
	);
	foreach ($pages as $key => $page) {
		if (!post_exists($page['post_title'])) {
			$postId = wp_insert_post($page);
		}
	}
}

function ntzcrm_media($imageUrl)
{
	global $wpdb;
	try {
	 	$filename = basename($imageUrl);
		$postType = 'attachment';
		$checkAttachment = $wpdb->get_var($wpdb->prepare("SELECT count(ID) FROM $wpdb->posts WHERE post_title = %s AND post_type = %s ",$filename,$postType));
		if (empty($checkAttachment) || $checkAttachment == 0) {
			$upload_file = wp_upload_bits($filename, null, file_get_contents($imageUrl));
			if (!$upload_file['error']) {
				// Check the type of file. We'll use this as the 'post_mime_type'.
				$filetype = wp_check_filetype(basename($filename), null);

				// Get the path to the upload directory.
				$wp_upload_dir = wp_upload_dir();

				// Prepare an array of post data for the attachment.
				$attachment = array(
					'guid' => $wp_upload_dir['url'] . '/' . basename($filename),
					'post_title' => sanitize_text_field(basename($filename)),
					'post_excerpt' => sanitize_text_field(basename($filename) . ' sample icon'),
					'post_content' => sanitize_text_field(basename($filename) . ' sample icon'),
					'post_mime_type' => $filetype['type'],
					'post_status'    => 'inherit',
				);

				// Insert the attachment.
				$attach_id = wp_insert_attachment($attachment, $upload_file['file'], 0);

				if (!is_wp_error($attach_id)) {

					// Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
					require_once(ABSPATH . 'wp-admin/includes/image.php');
					require_once(ABSPATH . 'wp-admin/includes/media.php');

					// Generate the metadata for the attachment, and update the database record.
					$attach_data = wp_generate_attachment_metadata($attach_id, $upload_file['file']);
					wp_update_attachment_metadata($attach_id, $attach_data);
				}
			}
		}
	} catch (Exception $ex) {
		// ignore 
	}
}

function ntzcrm_add_option()
{
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	if (get_option('is_actived_ntzcrm') !== false) {
		update_option('is_actived_ntzcrm', '1');
	} else {
		add_option('is_actived_ntzcrm', '1', null, 'no');
	}



	if (empty(get_option('ntzcrm_welcome_mail_template'))) {
		// $ntzcrmDefaultWelcomeMailTemplate="<p> Hi <b> #USERNAME# </b>,</p>
		// <p> Thanks for your subscription with $blogname </p>
		// <p> Click here to login </p>
		// <br/><p> #LOGINBTN# </p> <br/>
		// <p> Thanks,</p> <br/>
		// $blogname Team
		// ";

		$message = __('<p>Welcome, <strong>#USERNAME#</strong>!</p>');
		$message .= __('<p> Thanks for your subscription with ' . $blogname . "</p>");
		$message .= __('<p> We are thrilled to have you on board.</p>');
		$message .= __("<p> Thanks,</p> $blogname Team");

		if (get_option('ntzcrm_welcome_mail_template') !== false) {
			update_option('ntzcrm_welcome_mail_template', $message);
		} else {
			add_option('ntzcrm_welcome_mail_template', $message, null, 'no');
		}
	}

	if (empty(get_option('ntzcrmCreatePasswordMailTemplate'))) {
		// $ntzcrmDefaultResetMailTemplate="<p>Hi <b>#USERNAME#</b></p>
		// <p>Your Account has been created. <p/>
		// <p>Click on the below link to create your password.</p>
		// <br/><p> #CREATEPASSWORDLINK# </p>  <br/>
		// <p> Thanks,</p> <br/>
		// $blogname Team
		// ";
		$message = __('<p>Welcome, <strong>#USERNAME#</strong>!</p>');
		$message .= __('<p> Your Account has been created.<p/>');
		$message .= __('<p> Click on the below link to create your password.<p/>');
		$message .= __("<br/><p>#CREATEPASSWORDLINK#<p/><br/>");
		$message .= __("<p> Thanks,</p> $blogname Team");

		if (get_option('ntzcrmCreatePasswordMailTemplate') !== false) {
			update_option('ntzcrmCreatePasswordMailTemplate', $message);
		} else {
			add_option('ntzcrmCreatePasswordMailTemplate', $message, null, 'no');
		}
	}

	if (empty(get_option('ntzcrm_resetpassword_mail_template'))) {
		// $val="<p> Hi <b>#USERNAME#</b>,</p> 
		// <p> You have requested to reset your password. </p>
		// <p> To reset your password, visit the following address:</p>
		// <br/><p> #RESTPASSWORDLINK# </p><br/>
		// <p> Thanks,</p>
		// $blogname Team";

		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$message = __('<p>Welcome, <strong>#USERNAME#</strong>!</p>');
		$message .= __('<p>You have requested to reset password.</p>');
		$message .= __('<p> To reset your password, visit the following address: </p>');
		$message .= __("<br/><p>#RESTPASSWORDLINK#<p/><br/>");
		$message .= __('<p> If this was a mistake, ignore this email and nothing will happen.</p>');
		$message .= __("<p> Thanks,</p> $blogname Team");

		if (get_option('ntzcrm_resetpassword_mail_template') !== false) {
			update_option('ntzcrm_resetpassword_mail_template', $message);
		} else {
			add_option('ntzcrm_resetpassword_mail_template', $message, null, 'no');
		}
	}
}


function ntzcrm_deactivation()
{
	// add option is plugin activate or not 
	update_option('is_actived_ntzcrm', '0');
}
