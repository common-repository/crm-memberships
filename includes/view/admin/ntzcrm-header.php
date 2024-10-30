<div class="ntz-global-nav-menu">
	<div class="ntz-title">
		<a href="admin.php?page=getting-started">
			<span class="screen-reader-text"><?php esc_html_e('CRM Memberships', NTZCRMPRIFIX); ?></span><img class="ntz-logo" src="<?php echo esc_url(NTZCRM_PLUGIN_URL."images/crm-memberships-logo.png"); ?>" alt="crm memberships">
		</a>
	</div>

	<div class="ntz-global-nav__items">
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__one" href="<?php echo admin_url('admin.php?page=getting-started'); ?>"><?php esc_html_e('Getting Started', NTZCRMPRIFIX); ?></a>
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__two" href="<?php echo admin_url('admin.php?page=ntzcrm-settings'); ?>"><?php esc_html_e('Settings', NTZCRMPRIFIX); ?></a>
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__thr" href="<?php echo admin_url('admin.php?page=publications'); ?>"><?php esc_html_e('Publications', NTZCRMPRIFIX); ?></a>
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__fou" href="<?php echo admin_url('admin.php?page=add-publication-wizard'); ?>"><?php esc_html_e('Publication Wizard', NTZCRMPRIFIX); ?></a>
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__fiv" href="<?php echo admin_url('admin.php?page=add-new-tag'); ?>"><?php esc_html_e('Access Tags', NTZCRMPRIFIX); ?></a>
		<a class="ntz-global-nav-menu__tab ntz-global-nav-menu__six" href="<?php echo admin_url('admin.php?page=subscribers'); ?>"><?php esc_html_e('Subscribers', NTZCRMPRIFIX); ?></a>
	</div>

	<div class="ntz-top-links">
		<a target="_blank" class="ntz-top-links__item" title="Knowledge Base" href="https://ntzapps.com/plugin-customizations/"><span class="dashicons dashicons-book"></span></a>
		<a target="_blank" class="ntz-top-links__item" title="Community" href="https://www.youtube.com/channel/UCWr_AH4fKjjk7548lidhyeA"><span class="dashicons dashicons-youtube"></span></a>
		<a target="_blank" class="ntz-top-links__item" title="Support" href="https://ntzapps.com/contact-us/"><span class="dashicons dashicons-phone"></span></a>
	</div>
</div>

<?php if(isset($ptitle)){ ?>
	<div class="wrap"><h1><?php esc_html_e($ptitle, NTZCRMPRIFIX); ?></h1></div>
<?php } ?>

<style >
.ntz-global-nav-menu{margin:0;padding:2px 20px 0;display:flex;background:#fff;align-items:center;border-bottom:1px solid #ddd;justify-content:space-between}
.ntz-global-nav-menu .ntz-title a:focus,.ntz-global-nav-menu .ntz-title a:hover{box-shadow:none;outline:0}
.ntz-global-nav-menu .ntz-title .ntz-logo{width:120px; }
.ntz-global-nav-menu .ntz-global-nav__items{display:flex;align-items:center}
.ntz-global-nav-menu .ntz-global-nav-menu__tab{background:transparent;border:none;color:#444;cursor:pointer;padding:24px 14px;font-size:14px;line-height:1;letter-spacing:.225px;font-weight:400;margin:0 0 -1px;max-width:100%;text-align:center;text-decoration:none;outline:none;box-shadow:none;border-bottom:2px solid #ffffff00}
.ntz-global-nav-menu .ntz-global-nav-menu__tab:hover{color:#0073aa;border-bottom:2px solid #0073aa}
.ntz-top-links{flex:auto;text-align:right;font-weight:500;margin-right:-20px}
.ntz-top-links a{text-decoration:none;padding:20px 19px;color:#7d7d7d;display:inline-block;border-left:1px solid #ddd}
.ntz-top-links a:hover,.ntz-top-links a:focus{color:#0073aa}
.ntz-menu-page-content{margin:0 auto;width:100%;font-size:14px;font-weight:400}
.ntz-global-nav-menu .ntz-title{max-width:140px;border-right:1px solid #ddd;display:flex;align-items:center;padding-right: 15px;}
body.folded .ntz-global-nav-menu{position:fixed;width:calc(100% - 75px);left:55px;top:32px;z-index:2}
.ntz-global-nav-menu{position:fixed;width:calc(100% - 200px);left:160px;top:32px;z-index:2}
.ntz-wrap{ padding-top:65px}
.ntz-wrap .tab-container { margin-top: 0; width: calc(100% - 15px); padding: 10px 30px 30px; }
.ntz-wrap .nav-tab.nav-tab-active { color: #0073aa; border-bottom: none; background: #fff; margin-bottom: 0; position: relative; top: 1px; box-shadow: none !important; z-index: 1; }
.ntz-wrap .form-table textarea { width: 100%; }
.ntz-wrap .form-table input[type="text"], .ntz-wrap .form-table input[type="email"], .ntz-wrap .form-table input[type="password"] { width: 100%; height: 40px; }
.ntz-wrap .form-table td { position: relative; padding: 15px 10px; }
.ntz-wrap .form-table th { padding: 15px 10px; line-height: 40px; }
.custom-upload-input { position: relative; }
.custom-upload-input input { width: 100%; height: 40px; }
.custom-upload-input .upload-button,
.ntz-wrap .form-table td #ntzcrmlogo_upload { position: absolute; top: 15px; bottom: 15px; right: 10px; padding: 0 20px; line-height: 38px; text-decoration: none; text-transform: uppercase; letter-spacing: 2px; border-radius: 0 4px 4px 0; }
.ntz-wrap .form-table td #ntzcrmlogo_upload span.dashicons.dashicons-paperclip { line-height: 38px; }
.custom-upload-input .upload-button { top: 0; bottom: 0; right: 0; }
.custom-upload-box label { margin-bottom: 10px; }

.ntz-wrap h2, .ntz-wrap h3, .ntz-wrap h4 { color: #1d2327; font-size: 1.3em; margin: 1em 0; }
.ntz-wrap .text-left { text-align: left; }
.ntz-wrap img { max-width: 100%; display: inline-block; }
.ntz-wrap .m-0, .ntz-wrap .m-0 .submit { margin: 0 !important; }
.ntz-wrap .p-0, .ntz-wrap .p-0 .submit { padding: 0 !important; }

.components-text-control__input { width: 100%; height: 40px; }
.add_new_access_tag { width: 100%; float: left; position: relative; }
.add_new_access_tag input[type="text"] { width: 100%; padding-right: 150px; height: 40px; }
.ntzcrmtooltip button.button,
.add_new_access_tag input[type="submit"] { width: 100px; height: 40px; position: absolute; right: -1px; top: 0; }
.add_new_access_tag_load { position: absolute; top: 3px; right: 110px; display: none; }
.dm-custom-button.button { width: 100px; height: 40px; line-height: 38px; padding: 0; text-align: center; }

.ntzcrm-col-wrap .form-wrap { background: #ffffff; padding: 30px; width: calc(100% - 80px); }
.ntzcrm-col-wrap .form-wrap h2 { margin: 0; }
.profile-php .ntz-global-nav-menu {display: none;}
div#wpfooter { display: block;}
</style > 