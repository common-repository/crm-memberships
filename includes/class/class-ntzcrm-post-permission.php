<?php

/**
 * 
 */
class NtzCrmPostPermission
{

	public $db = "";
	public $blogname = '';
	function __construct()
	{
		global $wpdb;
		$this->db = $wpdb;
		$this->blogname = (is_multisite()) ? $GLOBALS['current_site']->site_name : wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
	}

	public function add_shortcodes()
	{
		add_shortcode('ntzcrm_icon', array($this, 'ntzcrmShortcode'));
		add_shortcode('ntzcrm_login', array($this, 'ntzcrmLogin'));
		add_shortcode('ntzcrm_restrict', array($this, 'ntzcrmRestrict'));
		add_shortcode('ntzcrm_testdesign', array($this, 'ntzcrmTestDesign'));
		add_shortcode('ntzcrm_publications', array($this, 'ntzcrmPublications'));
		if (get_option('ntzcrm_opt_default_navlinks') != false && !empty(get_option('ntzcrm_opt_default_navlinks')) && get_option('ntzcrm_opt_default_navlinks') == "yes") {
			add_filter('wp_nav_menu_items', array($this, 'ntzcrmNavMenuItems'));
		}
		if (get_option('ntzcrm_enable_partial_view') != false && get_option('ntzcrm_enable_partial_view') != "") {
			add_filter('the_content', array($this, 'partialViewContent'));
			add_filter('body_class', array($this, 'addClassInBody'));
		}
	}

	public function addClassInBody($classes)
	{
		global $post;
		$accessStatus = $this->checkPermissionForPartialView($post);
		if (!$accessStatus['accessStatus']) {
			$classes[] = 'ntzcrmpartialview';
		}
		return $classes;
	}

	private function defaultPartialViewPopContent()
	{
		return '<div class="crm-subscribe-title">!! CRM Partial View Content !!</div>
				<ul>
					<li>To change the content follow these steps:</li>
					<li>Login with administrator user.</li>
					<li>Go to the CRM Membership -> setting -> Partial View Settings.</li>
					<li>Update the content Partial View Text for Login and Partial View Text for subscribe</li>
					
				</ul> ';
		// <li><a style="" herf="'.admin_url('/admin.php?page=ntzcrm-settings#').'" >Edit Content</a></li>
	}
	private function getPartialViewContent($getContentType, $isLoginRequired, $post, $postTags = [])
	{
		$content = (!empty(get_option($getContentType))) ? get_option($getContentType) : $this->defaultPartialViewPopContent();
		$userId = get_current_user_id();
		if (!empty($userId)) {

			$defaultSubscribeButtonLink = (!empty(get_option("ntzcrm_subscribe_button_link"))) ? get_option("ntzcrm_subscribe_button_link") : "";
			$buttonLink = ntzcrm_dbquery::getMembershipTagPlanLink($postTags);
			$buttonLink = (!empty($buttonLink)) ? $buttonLink : $defaultSubscribeButtonLink;
			$button = (!empty($postTags)) ? '<div class="crm-subscribe-link"><a class="crm-subscribe-two" href ="' . esc_url($buttonLink) . '">Subscribe now</a></div>' : "";
		} else { // login button 
			$reirectTo = (!empty($post->post_name)) ? $post->post_name : "";
			$defaultLoginButtonLink = (get_option('ntzcrm_login_url') != false & !empty(get_option('ntzcrm_login_url'))) ? esc_url(get_option('ntzcrm_login_url') . "?redirecto=" . $reirectTo) : esc_url(site_url("/crm-memberships-login/?redirecto=" . $reirectTo));
			$button = (empty($userId)) ? '<div class="crm-subscribe-link"><a class="crm-subscribe-one" href ="' . esc_url($defaultLoginButtonLink) . '">Login now</a></div>' : "";
		}
		$html = '<div class="crm-subscribe-box">
				<div class="crm-subscribe-wrapper">
					' . $content . '
					' . $button . '
				</div>
			</div>';

		return $html;
	}

	private function checkPermissionForPartialView($post, $content = '')
	{
		$isAccessContent = false;
		$limitedContent = '';
		if (!empty($post)) {
			$isPublication = get_post_meta($post->ID, "is_ntzcrm_publication", true);  // If page is not publication
			if (empty($isPublication) || $isPublication == "no") {
				$isAccessContent = true;
			}
			$isLoginRequired = get_post_meta($post->ID, "is_ntzcrm_login_required", true);

			$userId = get_current_user_id();
			if (!empty($userId)) { // user logged In with all pos access
				$isAllAccess = get_user_meta($userId, 'all_access', true); // user have all access
				if (!empty($isAllAccess) && $isAllAccess == "yes") {
					$isAccessContent = true;
				}
			} else {
				$limitedContent = $this->getPartialViewContent('ntzcrm_login_partial_view_text', $isLoginRequired, $post);
			}

			if ($isAccessContent == false && is_singular($post) == false) { // is post detail page
				$isAccessContent = true;
			}
			$postTags = ntzcrm_dbquery::_getPostTagList($post->ID);

			// no login required  + user not logged in || user logged in + tags are empty
			if ($isAccessContent == false && !empty($isLoginRequired) && $isLoginRequired == "no" && empty($postTags)) {
				$isAccessContent = true;
			}

			// no login required  + user should logged in + tags are empty
			if ($isAccessContent == false && !empty($isLoginRequired) && $isLoginRequired == "yes" && !empty($userId) && empty($postTags)) {
				$isAccessContent = true;
			}
			if ($isAccessContent == false && !empty($userId)) {
				$limitedContent = $this->getPartialViewContent('ntzcrm_subscribe_partial_view_text', $isLoginRequired, $post, $postTags);

				if (!empty($postTags)) {
					$userTags = ntzcrm_dbquery::_getUserTagList($userId);

					if (!empty($userTags)) {
						$matched_result = array_intersect($postTags, $userTags);
						if (!empty($matched_result)) {
							$isAccessContent = true;
						}
					}
				}
			}
		}
		return ['accessStatus' => $isAccessContent, 'limitedContent' => $limitedContent];
	}

	public function partialViewContent($content)
	{
		global $post;
		$shortcodeTag = 'ntzcrm_restrict';
		if (strpos($content, '[' . $shortcodeTag) !== false) {
			$isRestrictedByShortcode = '[ntzcrm_restrict]';
		}

		$accessStatus = $this->checkPermissionForPartialView($post);

		if (!empty($isRestrictedByShortcode)) {
			$splitContent = explode('[ntzcrm_restrict]', $content);
			$partialContent = $splitContent[0];
			$content = str_replace('[ntzcrm_restrict]', '', $content);
			return ($accessStatus['accessStatus']) ? $content : $partialContent . $accessStatus['limitedContent'];
		} else {
			$maxAllowedWords = (!empty(get_option('ntzcrm_enable_partial_text_limit')) && is_numeric(get_option('ntzcrm_enable_partial_text_limit'))) ? trim(get_option('ntzcrm_enable_partial_text_limit')) : 200;
			$splitContent = explode(' ', $content);
			$partialContent = implode(' ', array_slice($splitContent, 0, $maxAllowedWords));
			return ($accessStatus['accessStatus']) ? $content : $partialContent . $accessStatus['limitedContent'];
		}
	}

	public function includeCssJs()
	{
		add_action('admin_init', array($this, 'ntzcrm_include_css'));
		add_action('in_admin_footer', array($this, 'ntzcrm_include_js'));
		add_action('wp_footer', array($this, 'ntzcrm_front_include_js'));
		add_action('wp_head', array($this, 'ntzcrm_include_fronted_css'), 99);
	}

	public function checkPermission()
	{
		add_action('wp_head', array($this, '_checkPermission'));
	}



	/*
	 * @Function: ntzcrmDefaultNavArgs
	 * To manage Membership Login, Logout & Publication Nav 
	 * Add Members Login, logout & Dashboard Navigations menu
	 * The function sets the default values for Labels for Login, logout & Dashboard nav
	 * and redirect locations on their clicks
	 * Author: Jeetendra
	 */

	private static function  ntzcrmDefaultNavArgs()
	{
		$args = array(
			'login_text' => __('Login'),
			'logout_text' => __('Logout'),
			'publication_text' => __('Dashboard'),
			'login_redirect_to' => (get_option('ntzcrm_login_url') != false & strlen(get_option('ntzcrm_login_url')) > 0) ? esc_url(get_option('ntzcrm_login_url')) : esc_url(site_url("/crm-memberships-login")),
			'logout_redirect_to' => (get_option('ntzcrm_logout_redirect_url') != false & strlen(get_option('ntzcrm_logout_redirect_url')) > 0) ? esc_url(get_option('ntzcrm_logout_redirect_url')) : esc_url(site_url("/crm-memberships-login")),
			'publication_redirect_to' => (get_option('ntzcrm_log_redirect_url') != false & strlen(get_option('ntzcrm_log_redirect_url')) > 0) ? esc_url(get_option('ntzcrm_log_redirect_url')) : esc_url(site_url("/crm-memberships-publications"))
		);
		return $args;
	}



	/*
	 * @Function: ntzcrmNavMenuItems
	 * The Function Handles Members Login, Logout and Dashboard access Control
	 * If also tracks if the user is logged in accordingly changes the Lables and redirect urls
	 * for this it takes help of ntzcrmDefaultNavArgs function 
	 * Author: Jeetendra
	 */
	public function ntzcrmNavMenuItems($items)
	{
		$instance = wp_parse_args(
			(array)	$this->ntzcrmDefaultNavArgs()
		);
		$login_text = trim($instance['login_text']);
		$logout_text = trim($instance['logout_text']);
		$publication_text = trim($instance['publication_text']);
		$login_redirect_to = strip_tags($instance['login_redirect_to']);
		$logout_redirect_to = strip_tags($instance['logout_redirect_to']);
		$publication_redirect_to = strip_tags($instance['publication_redirect_to']);

		if (empty($login_redirect_to)) {
			$login_redirect_to = esc_url(site_url());
		}
		if (empty($logout_redirect_to)) {
			$logout_redirect_to = esc_url(site_url());
		}

		if (is_user_logged_in()) {
			$ntzcrm_nav_link = '<li class="ntzcrm-navlink"><a style="margin-right: 10px;" href="' . esc_url($publication_redirect_to) . '">' . esc_html($publication_text) . '</a></li><li class="ntzcrm-navlink"><a href="' . esc_url(wp_logout_url($logout_redirect_to)) . '">' . esc_html($logout_text) . '</a></li>';
		} else {
			$ntzcrm_nav_link = '<li class="ntzcrm-navlink"><a href="' . esc_url($login_redirect_to) . '">' . esc_html($login_text) . '</a></li>';
		}

		// add the ntzcrm menu links to the end of the menu
		$items = $items . $ntzcrm_nav_link;
		return $items;
	}

	public function ntzcrmShortcode($atts)
	{

		extract(shortcode_atts(array(
			'post_id' => 0,
			'user_id' => '',
			'target' => 'same',
			'link' => 'true',
			'link_class' => '',
			'img_class' => '',
			'hide_disable' => false,
			'width' => false,
			'height' => false,
			'text' => '',
			'request' => 'encrypt',
			'context' => 'local'
		), $atts));
		if ($post_id <= 0) {
			return '';
		}
		$width_html = '';
		if ($width) {
			$width_html = ' width="' . esc_attr($width)  . '"';
		}
		$height_html = '';
		if ($height) {
			$height_html = ' height="' . esc_attr($height) . '"';
		}
		$link = true;
		$userId = $img_url = '';

		$link_target = '_self';
		$isLoginRequired = get_post_meta($post_id, "is_ntzcrm_login_required", true);
		$accessIcon = (get_post_meta($post_id, "ntzcrm_access_icon", true)) ? get_post_meta($post_id, "ntzcrm_access_icon", true) : "";
		$noAccessIcon = (get_post_meta($post_id, "ntzcrm_noaccess_icon", true)) ? get_post_meta($post_id, "ntzcrm_noaccess_icon", true) : "";
		$postTitle = get_the_title($post_id);

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
			if (!empty($postTags)) {
				$userTags = ntzcrm_dbquery::_getUserTagList($userId);
				$matched_result = array_intersect($postTags, $userTags);
				if (!empty($matched_result)) {
					$img_url = (!empty($accessIcon)) ? $accessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-enabled.png";
					$link = true;
				}
			}
		}
		$isAllowedPartialView = esc_html(get_option('ntzcrm_enable_partial_view'));
		if (!empty($isAllowedPartialView) && $isAllowedPartialView == "yes") {
			$img_url = (!empty($accessIcon)) ? $accessIcon : NTZCRM_PLUGIN_URL . "images/pub-access-enabled.png";
			$link = true;
		}

		$content_html = '';
		$text = esc_attr($text);
		if ($img_url) {
			$content_html ='<div class="ntzcrm-img-icon"><img src="' . esc_url($img_url) . '" alt="' . $text . '" class="ntzcrm-icon-img ' . esc_attr($img_class) . '" ' . esc_attr($width_html) . esc_attr($height_html) . '/></div>';
		} elseif ($text) {
			$content_html = $text;
		}
		if ($link) {
			return '<a href="' . esc_url(get_permalink($post_id)) . '" target="' . esc_html($link_target) . '" class="ntzcrm-icon-link ' . esc_attr($link_class) . '">' . $content_html . '<div class="ntzcrm-pub-lable text-center">' . esc_html($postTitlel) . '</div></a>';
		} elseif (!$link) {
			return "<div class='ntzcrm-icon-link'><div class='ntzcrm-icon'>" . $content_html . '<div class="ntzcrm-pub-lable text-center">' . esc_html($postTitlel) . '</div></div></div>';
		}
		return '<a href="' . esc_url($link) . '" target="' . esc_html($link_target). '" class="ntzcrm-icon-link ' . esc_attr($link_class) . '">' . '<div class="ntzcrm-pub-lable text-center">' . esc_html($postTitlel) . '</div></a>';
	}

	/* Include css and Js*/
	public function ntzcrm_include_fronted_css()
	{
		wp_enqueue_style('ntzcrm_custom-style', NTZCRM_PLUGIN_URL . 'css/ntzcrm_custom.css', '1.2.0', 'all');
		//wp_enqueue_style('ntzcrm_bootstrap', NTZCRM_PLUGIN_URL.'css/bootstrap.min.css','4.5.0','all'); 
		wp_enqueue_style('ntzcrm_loader-style', NTZCRM_PLUGIN_URL . 'css/ntzcrm_loader.css', '4.1.0', 'all');
	}
	public function ntzcrm_include_css()
	{
		$enablePlugin = get_option('is_actived_ntzcrm');
		if ($enablePlugin == '1' && is_admin()) {
			wp_enqueue_style('select2-style', NTZCRM_PLUGIN_URL . 'css/select2/select2.min.css', '1.2', 'all');
		}
	}

	public function ntzcrm_include_js()
	{
		$enablePlugin = get_option('is_actived_ntzcrm');
		if ($enablePlugin == '1' && is_admin()) {
			wp_enqueue_script('select2-script-js', NTZCRM_PLUGIN_URL . 'js/select2/select2.full.min.js', array('jquery'), "1.01");
			wp_enqueue_script('ntzcrm-admin-custom-js', NTZCRM_PLUGIN_URL . 'js/ntzcrm_custom.js', array('jquery'), '1.1');
		}
	}

	public function ntzcrm_front_include_js()
	{
		$enablePlugin = get_option('is_actived_ntzcrm');
		if ($enablePlugin == '1' && !is_admin()) {
			$logoutRedirectUrl = (!empty(get_option('ntzcrm_logout_redirect_url'))) ? esc_url(get_option('ntzcrm_logout_redirect_url')) : esc_url(site_url("/crm-memberships-login"));
			echo "<script type='text/javascript'> 
	        		var logouturl='" . str_replace("&amp;", "&", wp_logout_url($logoutRedirectUrl)) . "';
	        </script>";
			wp_enqueue_script('ntzcrm-custom-js', NTZCRM_PLUGIN_URL . 'js/ntzcrm_front_custom.js', array('jquery'), '1.2');
		}
	}

	public function _checkPermission()
	{
		//  global $post;
		$post_id = get_queried_object_id();
		// $page_object = get_queried_object();
		$isLoginRequired = get_post_meta($post_id, "is_ntzcrm_login_required", true);

		if (is_user_logged_in()) { //&&current_user_can('administrator')
			$userId = get_current_user_id();
			$isAllAccess = get_user_meta($userId, 'all_access', true);
			if (!empty($isAllAccess) && $isAllAccess == "yes") {
				$isLoginRequired = "no";
			}
			ntzcrm_dbquery::_svUserActivities($userId, $post_id);
		}

		$isAllowedPartialView = get_option('ntzcrm_enable_partial_view');
		if (!empty($isAllowedPartialView) && $isAllowedPartialView == "yes") {
			return true;
		}

		if (!empty($isLoginRequired) && $isLoginRequired == "yes") {
			if (!is_user_logged_in()) {
				$loginUrl = (!empty(get_option('ntzcrm_login_url'))) ? esc_url(get_option('ntzcrm_login_url')) : esc_url(site_url("/crm-memberships-login"));
				wp_safe_redirect($loginUrl);
				exit();
			} else {

				$postTags = ntzcrm_dbquery::_getPostTagList($post_id);
				$userId = get_current_user_id();
				$isTagSync = get_user_meta($userId, 'ntzcrm_user_tag_sync', true);
				if (empty($isTagSync) || $isTagSync != "yes") {
					$getData = get_user_meta($userId, '_ntzcrm_user_tag_ids', true);
					if (!empty($getData['ids'])) {
						ntzcrm_dbquery::_insertUserAccessTag($userId, $getData['ids']);
						if (!empty($isTagSync)) {
							update_user_meta($userId, 'ntzcrm_user_tag_sync', "yes");
						} else {
							add_user_meta($userId, 'ntzcrm_user_tag_sync', "yes");
						}
						$body = "$userId: This user id is synced. : tags : " . json_encode($getData['ids']);
						$this->ntzcrmUserSyncRequestLog($body);
					} else {
						if (!empty($isTagSync)) {
							update_user_meta($userId, 'ntzcrm_user_tag_sync', "no");
						} else {
							add_user_meta($userId, 'ntzcrm_user_tag_sync', "no");
						}
					}
				}
				if (!empty($postTags) && !is_front_page()) {
					$userTags = ntzcrm_dbquery::_getUserTagList($userId);
					$matched_result = array_intersect($postTags, $userTags);
					if (empty($matched_result)) {
						wp_safe_redirect(site_url('/', "302"));
						exit();
					}
				}
			}
		}
	}
	public function createJson($status, $message, $data = "")
	{
		$data = array('status' => $status, 'message' => $message, 'data' => $data);
		echo json_encode($data);
		exit();
	}

	public function sfNtzCrmMembershipRequestLog($body, $filename = "request_log.txt")
	{
		$error_log_enable = get_option('ntzcrm_enable_log');

		if ($error_log_enable == "yes") {
			$request = $this->ntzcrmRequests();
			$action = (isset($request['action'])) ? $request['action'] : '';
			$error_message = " ";
			$error_message .= "Action : " . $action . " - " . $body;
			$error_message .= "\n";
			$log_file = NTZCRM_DIR_PATH . "logs/$filename";
			ini_set("log_errors", FALSE);
			ini_set('error_log', $log_file);
			error_log($error_message);
		}
		return true;
	}

	public function ntzcrmUserSyncRequestLog($body)
	{
		$error_log_enable = get_option('ntzcrm_enable_log');
		if ($error_log_enable == "yes") {
			$error_message = " ";
			$error_message .= $body;
			$error_message .= "\n";
			$log_file = NTZCRM_DIR_PATH . "logs/user_sync_log.txt";
			ini_set("log_errors", FALSE);
			ini_set('error_log', $log_file);
			error_log($error_message);
		}
		return true;
	}

	public function sfNtzCrmMembershipErrorRequestLog($msg = "", $body = "")
	{
		$error_log_enable = get_option('ntzcrm_enable_log');
		if ($error_log_enable == "yes") {
			$request = $this->ntzcrmRequests();
			$action = (isset($request['action'])) ? $request['action'] : '';
			$error_message = " ";
			$error_message .= "Action : " . $action . " - " . json_encode($body) . ' : ' . $msg;
			$error_message .= "\n";
			$log_file = NTZCRM_DIR_PATH . "log/ntzcrm_errorlog.txt";
			ini_set("log_errors", FALSE);
			ini_set('error_log', $log_file);
			error_log($error_message);
		}
		return true;
	}

	/**/
	public function ntzcrmCheckToken($api_token = '', $key = '')
	{
		$pass = get_option('ntzcrm_api_token');
		$savkey = get_option('ntzcrm_api_key');
		if (!empty($key) && !empty($api_token) && $pass == $api_token && $key == $savkey) {
			return true;
		}
		return false;
	}

	public function ntzcrmLogin()
	{
		ob_start();
		include(NTZCRM_DIR_PATH . "includes/view/login.php");
		$fileValue = ob_get_contents();
		ob_end_clean();
		return $fileValue;
	}

	public function ntzcrmTestDesign()
	{
		ob_start();
		include(NTZCRM_DIR_PATH . "includes/view/test.design.php");
		$fileValue = ob_get_contents();
		ob_end_clean();
		return $fileValue;
	}

	public function ntzcrmPublications($attr = "")
	{
		ob_start();
		$results = ntzcrm_dbquery::_getFrontPublications($attr);
		include(NTZCRM_DIR_PATH . "includes/view/ntzcrm-publications.php");

		$fileValue = ob_get_contents();
		ob_end_clean();
		return $fileValue;
	}

	public function ntzcrmRequests($cbArray = array())
	{
		$request = array();
		if (!empty($_REQUEST)) {
			foreach ($_REQUEST as $fieldKey => $val) {
				if (!empty($cbArray) && in_array($fieldKey, $cbArray)) {
					$request[$fieldKey] = array_map('sanitize_text_field', $val);
				} else {
					if ($fieldKey == "email") {
						$request[$fieldKey] = sanitize_email($val);
					} elseif (in_array($fieldKey, array("tag_ids", "usertag", "posttag", "pubid"))) {
						$parsedArr = wp_parse_id_list($val);
						$request[$fieldKey] = array_map('sanitize_text_field', $parsedArr);
					} else {
						$request[$fieldKey] = sanitize_text_field($val);
					}
				}
			}
		}
		return $request;
	}

	public function ntzcrmJsonRequests($cbArray = array())
	{
		$request = array();
		header("Content-type: application/json");
		$body = file_get_contents('php://input');
		$request = (array)json_decode($body);
		if (!empty($request)) {

			$this->sfNtzCrmMembershipRequestLog($body);
			foreach ($request as $fieldKey => $val) {
				if (!empty($cbArray) && in_array($fieldKey, $cbArray)) {
					$request[$fieldKey] = array_map('sanitize_text_field', $val);
				} else {
					if ($fieldKey == "data") {  // Use for multi array.
						foreach ($request["data"] as $mulitKey => $dataVal) {
							$request[$fieldKey][$mulitKey] = $this->_ntzcrmMultiArrSanitize($dataVal);
						}
					} else {
						if ($fieldKey == "person_email" || $fieldKey == "email") {
							$request[$fieldKey] = sanitize_email($val);
						} elseif (in_array($fieldKey, array("tag_ids", "usertag", "posttag", "pubid"))) {
							$parsedArr = wp_parse_id_list($val);
							$request[$fieldKey] = ($val != 'all') ? array_map('sanitize_text_field', $parsedArr) : 'all';
						} else {
							$request[$fieldKey] = sanitize_text_field($val);
						}
					}
				}
			}
		}
		return $request;
	}

	public function _ntzcrmMultiArrSanitize($dataVal)
	{
		$request = array();
		$dataVal = (array)$dataVal;
		foreach ($dataVal as $mulitKey => $val) {
			if ($mulitKey == "person_email" || $mulitKey == "email") {
				$request[$mulitKey] = sanitize_email($val);
			} elseif (in_array($mulitKey, array("tag_ids", "usertag", "posttag", "pubid"))) {
				$request[$mulitKey] == ($val != 'all') ? array_map('sanitize_text_field', wp_parse_id_list($val)) : 'all';
			} else {
				$request[$mulitKey] = sanitize_text_field($val);
			}
		}
		return $request;
	}
}
