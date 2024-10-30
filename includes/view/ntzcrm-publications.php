<?php 
$userId=null;
if (!is_user_logged_in() || (is_user_logged_in() && !current_user_can('administrator'))) {  
	$userId = get_current_user_id();
	$newUser = get_userdata($userId);
 } 

$welcomeTitle = get_option("ntzcrm_publ_welcome_title");
echo "<div class='ntzcrm-row'> <div class='ntzcrm-col-sm-12'>";
if ($welcomeTitle) {
	echo '<h2 class="text-center driptitle">' . esc_html($welcomeTitle) . '</h2>';
}
$welcomeText = get_option("ntzcrm_publ_welcome_text");
if ($welcomeText) {
	echo '<p class="text-center driptext">' . esc_html($welcomeText) . '</p>';
}
echo "</div></div>";
if (!empty($results)) {
	$count = 1;
	echo "<div class='ntzcrm-row'> <div class='ntzcrm-col-sm-12'>";
	foreach ($results as $result) {
		$post_id = $result['ID'];
		$link = true;
		$img_url = '';
		$link_target = '_self';
		$isLoginRequired = get_post_meta($post_id, "is_ntzcrm_login_required", true);
		$accessIcon = (get_post_meta($post_id, "ntzcrm_access_icon", true)) ? get_post_meta($post_id, "ntzcrm_access_icon", true) : "";
		$noAccessIcon = (get_post_meta($post_id, "ntzcrm_noaccess_icon", true)) ? get_post_meta($post_id, "ntzcrm_noaccess_icon", true) : "";

		if (is_user_logged_in()) { //&&current_user_can('administrator')
			$userId = get_current_user_id();
			$isAllAccess = get_user_meta($userId, 'all_access', true);
			if (!empty($isAllAccess) && $isAllAccess == "yes") {
				$isLoginRequired = "no";
			}
		}
		$img_url = (!empty($accessIcon)) ? $accessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-enabled.png";
		if (!empty($isLoginRequired) && $isLoginRequired == "yes") {
			$link = false;
			$img_url = (!empty($noAccessIcon)) ? $noAccessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-disabled.png";

			$postTags = ntzcrm_dbquery::_getPostTagList($post_id);
			if (empty($postTags)) {
				$postTags = (!empty($oldNtzCrmMembershipPost['required-tag-ids']))?$oldNtzCrmMembershipPost['required-tag-ids']:[];
			}
			if (!empty($postTags)) {
					$userTags = ntzcrm_dbquery::_getUserTagList($userId);
				$matched_result = array_intersect($postTags, $userTags);
				if (!empty($matched_result)) {
					$img_url = (!empty($accessIcon)) ? $accessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-enabled.png";
					$link = true;
				}
			}
		}
		// If partial view enabled then the post link and icon will be enabled.
		$isAllowedPartialView = get_option('ntzcrm_enable_partial_view');
		if (!empty($isAllowedPartialView) && $isAllowedPartialView == "yes") {
			$img_url = (!empty($accessIcon)) ? $accessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-enabled.png";
			$link = true;
		}

		$content_html = '';
		if ($img_url) {
			$content_html = '<div class="ntzcrm-img-icon"><img src="' . esc_url($img_url) . '" class="ntzcrm-icon-img" width="100%" /></div>';
		} 
		if ($count % 5 == 0) {
			echo "</div></div><div class='ntzcrm-row'> <div class='ntzcrm-col-sm-12'>";
		}

		if ($link) {
			echo  '<div class="ntzcrm-mt-2 ntzcrm-col-md-3 ntzcrm-col-sm-6 ntzcrm-col-xs-12"><a href="' . esc_url(get_permalink($post_id)) . '" target="' . esc_html($link_target) . '" class="ntzcrm-icon-link">' .$content_html . '<div class="ntzcrm-pub-lable text-center">' . esc_html($result["post_title"]) . '</div></a></div>';
			$count++;
			continue;
		} elseif (!$link) {
			echo '<div class="ntzcrm-mt-2 ntzcrm-col-md-3 ntzcrm-col-sm-6 ntzcrm-col-xs-12">' . $content_html . '<div class="ntzcrm-pub-lable text-center">' . esc_html($result["post_title"]) . '</div></div>';
			$count++;
			continue;
		}
		echo '<div class="ntzcrm-mt-2 ntzcrm-col-md-3 ntzcrm-col-sm-6 ntzcrm-col-xs-12"><div class="ntzcrm-icon-link"><a href="' . esc_url($link) . '" target="' . esc_html($link_target) . '" class="ntzcrm-icon-link">' . $content_html . '<div class="text-center ntzcrm-pub-lable">' . esc_html($result["post_title"]) . '</div></div></a></div>';
		$count++;
	}
	echo "</div></div>";
} else {
	if (!is_user_logged_in() || (is_user_logged_in() && !current_user_can('administrator'))) {  ?>
		<?php esc_html_e("Welcome message for Visitor.") ?>
	<?php } else { ?>
		<?php esc_html_e("Publication button and instractions for administrator.") ?>
		
<?php }
}
?>