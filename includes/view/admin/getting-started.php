<style> 
.error.ntzcrmtextred{color:red;font-size:18px}
.ntzcrmtextbold{font-weight:700;display:block}
.tab-container{margin-top:20px;padding:.7em 2em 1em;width:1000px;border:1px solid #ccd0d4;box-shadow:0 1px 1px rgba(0,0,0,.04);background:#fff;box-sizing:border-box}
.mt-2{margin-top:20px}
.mt-4{margin-top:50px}
.ntzcrmdisabledmenu{pointer-events:none}
.ntzcrmdisabledmenu:hover{background:none;color:#767676}
.tab-content{display:none;padding:15px}
.tab-content.current{display:inherit}
.ntz-global-nav-menu .ntz-global-nav-menu__tab.ntz-global-nav-menu__one{background:none;color:#0073aa;border-bottom:2px solid #0073aa}
</style> 
<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap">
<h1>Getting Started</h1>
<div class="tab-container text-left"> 
			<h2>CRM Memberships</h2>
			<i>CRM Memberships plugin enables you to restrict your content to paid or authorized members only. You can use it for creating online courses, social communities, marketing funnel fulfillment, digital products like reports, premium content etc. 
			CRM Memberships plugin also allows integration of WordPress with Salesforce. For more info visit <a href="http://www.ntzapps.com">www.ntzapps.com</a> </i>
			<br><br><br>
			<h2>Components of a membership site</h2>
			<ol>
			Before you setup your membership site, you need to define what product or services you are selling. A website can contain multiple contents (like pages, posts, media etc) for a given product or service you plan to sell.<br><br>
			<li><strong>Publications</strong>: The term Publication is used to group all the contents that belongs to a unit of sale (e.g Ultimate Cookery Course). You can define more than one publication.</li>
			<li><strong>Access Tags</strong>:  Next you assign a unique <i>Access Tag</i> to the product or services you sell. Access Tags are used to control access to the publications. An Access Tag consists of a name and an numeric id (e.g Ultimate Cookery Course Access - 0001 ).</li>
			<li><strong>Subscribers</strong>: Your customers "subscribe" to the publications usually via a shopping cart or a registration form. The system allows you to associate the Access Tag for the publication to the customer.</li>
			<li><strong>Restrict Access</strong>: When the customer login into the website, CRM Membership plugin will authenticate and authorize the user by looking up the list of Access Tags assigned to the user and will enable or disable access to the membership areas accordingly.</li>
			</ol><br><br>
			<h3>Adding a new Publication</h3>
			<p>Use the Publication Wizard to create parent publication pages and their associated tags.</p><br>
			<img src="<?php echo esc_url(NTZCRM_PLUGIN_URL."images/pub-wiz.png"); ?>"/><br><br>
			<h4>Wizard allows you to: </h4>
			<ol>			
			<li>Specify the name for the publication (example: "Ultimate Cookery Course") and associate publication icons (enable/disable icons)</li>
			<li>Add new tag or use an eixsting tag from <i>Access Tags</i> page.  Associate this selected tag to the publication.</li>
			<li>Next Wizard will generate a shortcode for the publication and will add the publication to the default publications home page.<br><br>
			<img src="<?php echo esc_url(NTZCRM_PLUGIN_URL."images/get-start1.png"); ?>"/>
			</li></ol><br><br>
			<h3>Updating Publications</h3>
			<ol>
			<li>View the list of publications created by the Publication Wizard and their shortcodes later by selecting the <i>Publications</i> menu item. You can also add the shortcode to a custom page if needed. (e.g. [<?php echo NTZCRMPRIFIX;?>icon post_id='208']). This shortcode will show the enable / disable icons on the page.</li>
			<li>You can update existing publication settings by selecting <i>Edit Wizard</i></li>
			</ol><br><br>
			<h3>Publications Gallery & Login Page</h3>
			<p>This plugin comes with two default pages.</p>
			<ol>
			<li><strong>CRM Memberships - Publications</strong>: This is the default <i>Publications gallery</i> page that comes with the plugin. Any custom page can also be turned into <i>Publications gallery</i> page by inserting <strong>[ntzcrm_publications]</strong> shortcode in it's content area.</li>
			<li><strong>CRM Memberships - Login</strong>: This is the default <i>User Login</i> page.</li>
			</ol><br><br>
			<h4>Login/Logout Menus</h4>
			<ul>
			<li><strong>Adding a Login/Logout Menu</strong>: You can enable default menu by selecting <i>Add Publication Menu Item</i> from Settings page.</i>
			</ul><br><br>
			<h4>Assigning an Access Tag to the user</h4>
			<ul>
				<li>Open the user profile page for a user from Users menu. In the <i> Memberships Subscription Information</i> section, select the Access Tag that you want to associate to the user.<br><br>
				<img src="<?php echo esc_url(NTZCRM_PLUGIN_URL."images/get-start3.png"); ?>"/>
				</li><br>
				When the above user logins to the website, the plugin will authorize the access to various publications based on the user tag assignment. 
			</ul><br><br>
			<h4>Customizations & Support</h4>
			<ul>
				<li>You can create a basic membership website with above steps. For more customizations and tips, please visit <a href="https://ntzapps.com/plugin-customizations/">Customizations</a></li>
				<li>Please <a href = "mailto: sales@netesenz.com">Contact us</a> to get additional customizations and the latest CRM Memberships managed package for Salesforce.
				</li>
			</ul><br><br>
		</div>
</div>