<?php
ob_start();
include_once(NTZCRM_DIR_PATH . "includes/view/admin/form-fields.php");
ob_start();
class ntzcrmAdmin extends NtzCrmPostPermission
{
  public $isAuthed = "";
  public $isSetWarehouseExist = "";
  function __construct()
  {
    parent::__construct();
  }

  public function ntzcrmInitAdminHooks()
  {
    add_action('wp_ajax_ntzcrm_login_shortcode', array($this, 'ntzcrm_login_shortcode'));
    add_action('wp_ajax_export_subscriber', [$this, 'ntzcrm_export_subscriber']);
    add_action('admin_menu', [$this, 'ntzcrmAdminMenu']);
    add_action("admin_init", [$this, "ntzcrm_display_theme_panel_fields"]);
    add_filter('manage_users_custom_column', [$this, "ntzcrm_modify_user_table_row"], 10, 3);
    add_filter('manage_post_posts_columns', [$this, 'ntzcrmAddCustomScreenOption']); 
    add_action('wp_logout', [$this, 'ntzcrmUserLastLogout'], 1);
    add_filter('after_password_reset', [$this, 'ntzcrmRedirectAfterPasswordReset']);
    add_action('wp_login', [$this, 'ntzcrmUserLastLogin'], 10, 2);
    add_filter('login_head', [$this, 'ntzcrmCustomLoginLogo']);

    add_action('show_user_profile', [$this, 'ntzcrm_user_tag_permission']);
    add_action('edit_user_profile', [$this, 'ntzcrm_user_tag_permission']);
    add_action('user_new_form', [$this, 'ntzcrm_user_tag_permission']);

    add_action('add_meta_boxes', [$this, 'ntzcrm_post_tag_permission']);

    add_action('personal_options_update', [$this, 'ntzcrmUpdateProfileFields']);
    add_action('edit_user_profile_update', [$this, 'ntzcrmUpdateProfileFields']);
    add_action('edit_user_created_user',[$this, 'ntzcrmUpdateProfileFields']);

    add_action('save_post', [$this, 'ntzcrm_save_post_tag']);

    add_filter('manage_users_columns', [$this, 'ntzcrm_modify_user_table']);
    add_filter('manage_users_sortable_columns', [$this, 'ntzcrm_make_registered_column_sortable']);
  }


  public function ntzcrmAdminMenu($value = '')
  {
    if (is_admin() && get_option('is_actived_ntzcrm') == '1') {
      add_menu_page(__('CRM Memberships Settings', 'ntzcrm_membership'), __('CRM Memberships', 'ntzcrm_exale_membership'), 'activate_plugins', 'getting-started', [$this, 'ntzcrm_getting_started'], NTZCRM_PLUGIN_URL . 'images/ntzcrm.png', null);
      add_submenu_page('getting-started', 'Getting Started', 'Getting Started', 'manage_options', 'getting-started', [$this, 'ntzcrm_getting_started']);
      add_submenu_page('getting-started', 'Settings', 'Settings', 'manage_options', 'ntzcrm-settings', [$this, 'ntzcrm_settings']);
      add_submenu_page('getting-started', 'Publications', 'Publications', 'manage_options', 'publications', [$this, 'ntzcrm_publications']);
      add_submenu_page('getting-started', 'Publication  Wizard', 'Publication Wizard', 'manage_options', 'add-publication-wizard', [$this, 'ntzcrm_add_publication_wizard']);
      add_submenu_page('getting-started', 'Access Tags', 'Access Tags', 'manage_options', 'add-new-tag', [$this, 'ntzcrm_add_tag_form']);
      add_submenu_page('getting-started', 'Subscribers', 'Subscribers', 'manage_options', 'subscribers', [$this, 'ntzcrm_subscribers']);
    }
  }


  function ntzcrm_getting_started()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/getting-started.php");
  }
  function ntzcrm_settings()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/admin-page.php");
  }
  function ntzcrm_publications()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/publications.php");
  }

  function ntzcrm_add_publication_wizard()
  {
    $postPermission = new NtzCrmPostPermission();
    $request = $postPermission->ntzcrmRequests(); 
    if (isset($request['save'])) {
      ntzcrm_dbquery::_ntzcrmSvPublications($request);
    }
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/add-pub-wizard.php");
  }
  function ntzcrm_add_tag_form()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/add-tag-form.php");
  }
  function ntzcrm_subscribers()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/subscribers.php");
  }

  function ntzcrm_export_subscriber()
  {
    include_once(NTZCRM_DIR_PATH . "includes/view/admin/export-subscribers.php");
    exit;
  }


  public function ntzcrm_login_shortcode()
  {
    do_shortcode('[ntzcrm_login]');
    exit;
  }

  public function ntzcrm_modify_user_table_row($row_output, $column_id_attr, $user)
  {
    $date_format = 'M j, Y h:i A';
    switch ($column_id_attr) {
      case 'registration_date':
        return date($date_format, strtotime(get_the_author_meta('registered', $user)));
        // break;
      default:
    }

    return $row_output;
  }

  public function ntzcrmRemoveAdminBar()
  {
    // No need more 
    // if (!current_user_can('administrator') && !is_admin()) {
    //     show_admin_bar(false);
    // }
  } 

  public function ntzcrmAddCustomScreenOption($column_array)
  {
    $column_array['ntz_crm_bulk_edit_custom_fields'] = '';
    return $column_array;
  }

  public function ntzcrmUserLastLogout($user)
  {
    $userId = get_current_user_id();
    ntzcrm_dbquery::_svUserLogOutTime($userId);
  }

  public function ntzcrm_display_theme_panel_fields()
  {

    add_settings_field("ntzcrm_logo", "Logo For Login Page", "ntzcrm_logo", "commontheme-options", "section");

    add_settings_field("ntzcrm_login_url", "Login Page Url", "ntzcrm_login_url", "commontheme-options", "section");

    add_settings_field("ntzcrm_log_redirect_url", "Redirect Page URL After Login", "ntzcrm_log_redirect_url", "commontheme-options", "section");
    add_settings_field("ntzcrm_logout_redirect_url", "Redirect Page URL After Logout", "ntzcrm_logout_redirect_url", "commontheme-options", "section");

    add_settings_field("ntzcrm_enable_partial_view", "Enable Partial View", "ntzcrm_enable_partial_view", "partialview-options", "section");
    add_settings_field("ntzcrm_enable_partial_text_limit", "Word Limit", "ntzcrm_enable_partial_text_limit", "partialview-options", "section");

    add_settings_field("ntzcrm_login_partial_view_text", "Partial View Text for Login", "ntzcrm_login_partial_view_text", "partialview-options", "section");

    add_settings_field("ntzcrm_subscribe_button_link", "Subscribe Button Link", "ntzcrm_subscribe_button_link", "partialview-options", "section");

    add_settings_field("ntzcrm_subscribe_partial_view_text", "Partial View Text for subscribe", "ntzcrm_subscribe_partial_view_text", "partialview-options", "section");



    /* No need more Prompt change password on new user creation feature */
    // add_settings_field("ntzcrm_enable_change_password", "Prompt Change Password", "ntzcrm_enable_change_password", "commontheme-options", "section");
    add_settings_field("ntzcrm_opt_default_navlinks", "Add Publication Menu Item", "ntzcrm_opt_default_navlinks", "commontheme-options", "section");
    add_settings_field("ntzcrm_enable_log", "Enable Logs", "ntzcrm_enable_log", "commontheme-options", "section");

    add_settings_field("ntzcrm_publ_welcome_title", "Welcome Title", "ntzcrm_publ_welcome_title", "commontheme-options", "section");
    add_settings_field("ntzcrm_publ_welcome_text", "Welcome Text", "ntzcrm_publ_welcome_text", "commontheme-options", "section");

    add_settings_section("section", "", null, "commontheme-options");
    add_settings_section("section", "", null, "partialview-options");
    add_settings_section("section", "", null, "salesforce-settings");
    add_settings_section("section", "", null, "usertracking-options");
    add_settings_section("section", "", null, "emailtemplate-options");

    add_settings_field("ntzcrm_api_key", "Salesforce NTZ CRM API Key <i>(Required)</i>", "ntzcrm_api_key", "salesforce-settings", "section");
    add_settings_field("ntzcrm_api_token", "Salesforce NTZ CRM API Token <i>(Required)</i>", "ntzcrm_api_token", "salesforce-settings", "section");

    add_settings_field("ntzcrm_disabled_post_tracking", "Page View Tracking", "ntzcrm_disabled_post_tracking", "usertracking-options", "section");

    add_settings_field("ntzcrmEnableTosendWelcomeMailOnUserCreation", "Enable Welcome Mail ", "ntzcrmEnableTosendWelcomeMailOnUserCreation", "emailtemplate-options", "section");
    add_settings_field("ntzcrm_welcome_mail_template", "Welcome Template", "ntzcrm_welcome_mail_template", "emailtemplate-options", "section");

    add_settings_field("ntzcrmEnableTosendCreatePasswordMailOnUserCreation", "Enable Create Password mail ", "ntzcrmEnableTosendCreatePasswordMailOnUserCreation", "emailtemplate-options", "section");
    add_settings_field("ntzcrmCreatePasswordMailTemplate", "Create Password Template", "ntzcrmCreatePasswordMailTemplate", "emailtemplate-options", "section");

    add_settings_field("ntzcrm_resetpassword_mail_template", "Reset Password Template", "ntzcrm_resetpassword_mail_template", "emailtemplate-options", "section");




    $arg=['type' => 'string','sanitize_callback' => 'sanitize_text_field','default' => NULL];

    $integerArg=['type' => 'integer','sanitize_callback' => 'sanitize_text_field','default' => NULL];
    
    register_setting("section", "ntzcrm_enable_log",$arg);
    register_setting("section", "ntzcrm_enable_partial_view",$arg);
    register_setting("section", "ntzcrm_enable_partial_text_limit",$integerArg);
    register_setting("section", "ntzcrm_login_partial_view_text",$arg);
    register_setting("section", "ntzcrm_subscribe_button_link",$arg);
    register_setting("section", "ntzcrm_subscribe_partial_view_text",$arg);
    /* No need more Prompt change password on new user creation feature */
    // register_setting("section", "ntzcrm_enable_change_password");
    
    
    register_setting("section", "ntzcrm_logo",$arg);
    register_setting("section", "ntzcrm_opt_default_navlinks",$arg);
    register_setting("section", "ntzcrm_publ_welcome_title",$arg);
    register_setting("section", "ntzcrm_publ_welcome_text",$arg);
    register_setting("section", "ntzcrm_api_key",$arg);
    register_setting("section", "ntzcrm_api_token",$arg);
    register_setting("section", "ntzcrm_service_url",$arg);
    register_setting("section", "ntzcrm_login_url",$arg);
    register_setting("section", "ntzcrm_log_redirect_url",$arg);
    register_setting("section", "ntzcrm_logout_redirect_url",$arg);
    register_setting("section", "ntzcrm_disabled_post_tracking",$arg);
    register_setting("section", "ntzcrmEnableTosendWelcomeMailOnUserCreation",$arg);
    register_setting("section", "ntzcrm_welcome_mail_template",$arg);

    register_setting("section", "ntzcrmEnableTosendCreatePasswordMailOnUserCreation",$arg);
    register_setting("section", "ntzcrmCreatePasswordMailTemplate",$arg);
    register_setting("section", "ntzcrm_resetpassword_mail_template",$arg);
  }

  // public function sanitizeNtzcrmLogo( $input )
  // { 
  //     $postPermission = new NtzCrmPostPermission();
  //     $request = $postPermission->ntzcrmRequests();
  //     return (!empty($request['ntzcrm_logo']))?$request['ntzcrm_logo']:'';
  // }

  public function ntzcrmRedirectAfterPasswordReset($lostpassword_redirect)
  {
    $redirect = (get_option('ntzcrm_login_url') != false & strlen(get_option('ntzcrm_login_url')) > 0) ? esc_url(get_option('ntzcrm_login_url')) : esc_url(site_url("/crm-memberships-login"));
    wp_safe_redirect($redirect);
    exit; // always exit after wp_safe_redirect
  }

  public function ntzcrmUserLastLogin($user_login, $user)
  {
    ntzcrm_dbquery::_svUserLoginTime($user->ID);
  }


  public function ntzcrmCustomLoginLogo()
  {
    echo '<style type="text/css">h1 a {width:100%!important; height: 100px!important; background-image:url(' . esc_url(get_option("ntzcrm_logo")) . ') !important; margin:0 auto;} </style>';
  }

  function ntzcrm_user_tag_permission($user)
  {
    $tags = ntzcrm_dbquery::_getMembershipTagsList();
    $userTags = (!empty($user->ID))?ntzcrm_dbquery::_getUserTagList($user->ID):[];
    if (!empty($userTags)) {
      $tagids = get_user_meta($user->ID, '_ntzcrm_user_tag_ids', true);
      if (!empty($tagids['ids'])) {
        foreach ($tagids['ids'] as $key => $tid) {
          $userTags[] = $tid;
        }
      }
    }
    include_once NTZCRM_DIR_PATH . "includes/view/admin/usermeta.php";
  }


  function ntzcrm_post_tag_permission($user)
  {
    add_meta_box('custom_contact_tag_id', 'CRM Memberships Permission', [$this, 'ntzcrm_post_meta'], array("post", "page"), 'normal', 'high');
  }
  function ntzcrm_post_meta()
  {
    include_once NTZCRM_DIR_PATH . "includes/view/admin/postmeta.php";
  }



  function ntzcrmUpdateProfileFields($user_id)
  {
    $postPermission = new NtzCrmPostPermission();
    $request = $postPermission->ntzcrmRequests();
    
    if (isset($request['ntzcrm_contact_id']) && !empty($request['ntzcrm_contact_id'])) {
      if (!get_user_meta($user_id, 'ntzcrm_contact_id', true)) {
        add_user_meta($user_id, 'ntzcrm_contact_id', trim($request['ntzcrm_contact_id']));
      } else {
        update_user_meta($user_id, 'ntzcrm_contact_id', trim($request['ntzcrm_contact_id']));
      }
    }
    if (isset($request['all_access']) && !empty($request['all_access'])) {
      if (!get_user_meta($user_id, 'all_access', true)) {
        add_user_meta($user_id, 'all_access', trim($request['all_access']));
      } else {
        update_user_meta($user_id, 'all_access', trim($request['all_access']));
      }
    } else {
      if (!get_user_meta($user_id, 'all_access', true)) {
        add_user_meta($user_id, 'all_access', "no");
      } else {
        update_user_meta($user_id, 'all_access', "no");
      }
    }

    global $wpdb;

    $table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
    if (isset($request['usertag']) && !empty($request['usertag'])) {
      $tags = array_unique($request['usertag']);
      global $wpdb;
      if (!empty($tags)) {
        $values = [];
        $countTags = ntzcrm_dbquery::_checkUserTag($user_id);
        if ($countTags > 0) {
          $wpdb->delete($table_name, array('user_id' => $user_id));
        }
        foreach ($tags as $key => $tagId) {
          array_push($values, $user_id, $tagId);
          $place_holders[] = "('%d', '%d')";
        }
        $query = "INSERT INTO $table_name (user_id, tag_id) VALUES ";
        $query .= implode(', ', $place_holders);
        $wpdb->query($wpdb->prepare("$query ", $values));
      }
    } else {
      $countTags = ntzcrm_dbquery::_checkUserTag($user_id);
      if ($countTags > 0) {
        $wpdb->delete($table_name, array('user_id' => $user_id));
      }
    }
  }



  // Registration date 
  /*
 * Create a column. And maybe remove some of the default ones
 * @param array $columns Array of all user table columns {column ID} => {column Name} 
 */

  function ntzcrm_modify_user_table($columns)
  {
    // unset( $columns['posts'] ); // maybe you would like to remove default columns
    $columns['registration_date'] = 'Registration date'; // add new
    return $columns;
  }

  /*
 * Make our "Registration date" column sortable
 * @param array $columns Array of all user sortable columns {column ID} => {orderby GET-param} 
 */
  function ntzcrm_make_registered_column_sortable($columns)
  {
    return wp_parse_args(array('registration_date' => 'registered'), $columns);
  }

  public function ntzcrm_save_post_tag($post_id = "")
  {
    global $wpdb, $post;
    $postId = (!empty($post_id)) ? $post_id : $post->ID;
    $values = [];
    $postPermission = new NtzCrmPostPermission();
    $request = $postPermission->ntzcrmRequests(); 
    if (isset($request['is_ntzcrm_publication']) && !empty($request['is_ntzcrm_publication']) && ($request['is_ntzcrm_publication'] != '-1')) {

      $isPublication = (isset($request['is_ntzcrm_publication']) && 'yes' == $request['is_ntzcrm_publication']) ? 'yes' : 'no';

      if (!get_post_meta($postId, 'is_ntzcrm_publication', true)) {
        add_post_meta($postId, 'is_ntzcrm_publication', $isPublication);
      } else {
        update_post_meta($postId, 'is_ntzcrm_publication', $isPublication);
      }
    }
    
    if (isset($request['is_fronted_publication']) && !empty($request['is_fronted_publication'])) {
      if (!get_post_meta($postId, 'is_fronted_publication', true)) {
        add_post_meta($postId, 'is_fronted_publication', sanitize_text_field(trim($request['is_fronted_publication'])));
      } else {
        update_post_meta($postId, 'is_fronted_publication', sanitize_text_field(trim($request['is_fronted_publication'])));
      }
    } else {
      if (!empty($postId)) {
        if (!get_post_meta($postId, 'is_fronted_publication', true)) {
          add_post_meta($postId, 'is_fronted_publication', "no");
        } else {
          update_post_meta($postId, 'is_fronted_publication', "no");
        }
      }
    }

    if (isset($request['is_ntzcrm_login_required']) && !empty($request['is_ntzcrm_login_required']) && ($request['is_ntzcrm_login_required'] != '-1')) {
      if (!get_post_meta($postId, 'is_ntzcrm_login_required', true)) {
        add_post_meta($postId, 'is_ntzcrm_login_required', sanitize_text_field(trim($request['is_ntzcrm_login_required'])));
      } else {
        update_post_meta($postId, 'is_ntzcrm_login_required', sanitize_text_field(trim($request['is_ntzcrm_login_required'])));
      }
    } else {
    }

    if (isset($request['ntzcrm_access_icon']) && !empty($request['ntzcrm_access_icon'])) {
      if (!get_post_meta($postId, 'ntzcrm_access_icon', true)) {
        add_post_meta($postId, 'ntzcrm_access_icon', sanitize_text_field(trim($request['ntzcrm_access_icon'])));
      } else {
        update_post_meta($postId, 'ntzcrm_access_icon', sanitize_text_field(trim($request['ntzcrm_access_icon'])));
      }
    }
    
    if (isset($request['ntzcrm_noaccess_icon']) && !empty($request['ntzcrm_noaccess_icon'])) {
      if (!get_post_meta($postId, 'ntzcrm_noaccess_icon', true)) {
        add_post_meta($postId, 'ntzcrm_noaccess_icon', sanitize_text_field(trim($request['ntzcrm_noaccess_icon'])));
      } else {
        update_post_meta($postId, 'ntzcrm_noaccess_icon', sanitize_text_field(trim($request['ntzcrm_noaccess_icon'])));
      }
    } 
    if (!empty($request['ntz_crm_bulk_tag_action']) && !empty($request['ntz_crm_bulk_tag_action']) && ($request['ntz_crm_bulk_tag_action'] != '-1')) {
      if (isset($request['posttag']) && !empty($request['posttag'])) {
        $tags = array_unique($request['posttag']); 
        ntzcrm_dbquery::_insertPostTag($postId,$request['posttag']);
      }else {
        $table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
        $query = "DELETE FROM $table_name WHERE post_id = %d";
        $wpdb->query($wpdb->prepare($query,[$postId]));
      }
    }
  } 
}
