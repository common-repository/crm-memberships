<?php 
/**
 * Plugin Name: CRM Memberships
 * Plugin URI: https://ntzapps.com/
 * Description: CRM Memberships plugin allows restricting your content to paid or registered members only. Use it for creating online courses, marketing funnel fulfillment etc. CRM Memberships plugin also allows easy integration of WordPress with CRMs like Salesforce. 
 * Version: 2.4
 * Author: NTZApps
 * Author URI: https://ntzapps.com/
 * License: GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  ntzapps.com
 * Domain Path:  #
 */

   
/*define contstant */  
define("NTZCRM_DIR", dirname(__FILE__));
define("NTZCRM_DIR_PATH", plugin_dir_path(__FILE__)); 
define('NTZCRM_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NTZCRMPRIFIX',"ntzcrm_"); 
$urlparts = parse_url(home_url());
$domain = (!empty($urlparts))?$urlparts['host']:""; 
define('NTZCRM_DOMAIN',$domain);
include_once NTZCRM_DIR_PATH ."includes/traits//trait-ntzcrm-mail-services.php"; 
require_once(NTZCRM_DIR_PATH . "includes/installer.php"); 
require_once(NTZCRM_DIR_PATH . "includes/class/class-ntzcrm-dbquery.php");
require_once(NTZCRM_DIR_PATH . "includes/class/class-ntzcrm-post-permission.php");
require_once(NTZCRM_DIR_PATH . "includes/class/ntzcrm-admin.php");  
require_once(NTZCRM_DIR_PATH . "includes/class/class-ntzcrm-api.php"); 

// Use for Fronted dashboard code 
$postPermission=new NtzCrmPostPermission();
$postPermission->checkPermission(); 
$postPermission->add_shortcodes();
$postPermission->includeCssJs(); 


$adminObj=new ntzcrmAdmin();
$adminObj->ntzcrmInitAdminHooks();


/* Only for Login */ 
$request=$postPermission->ntzcrmRequests();
if(isset($request['action'])&&!empty($request['action'])){
    $allApiMethods=get_class_methods('NtzCrmApi'); 
    $api=new NtzCrmApi();
    if(in_array($request['action'],$allApiMethods)){
        $api->_ntzCallApi(trim($request['action']));  
    }
}
/* Plugin activation */
register_activation_hook( __FILE__, 'ntzcrm_installer' );
register_deactivation_hook(__FILE__, 'ntzcrm_deactivation');


add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'ntzcrmSettingsLink' );
function ntzcrmSettingsLink( array $links ) {
    $url = admin_url() . "admin.php?page=ntzcrm-settings";
    $settings_link = '<a href="' . $url . '">' . __('Settings', 'crm-membership') . '</a>';
      $links[] = $settings_link;
    return $links;
} 