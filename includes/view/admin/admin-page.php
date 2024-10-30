<style>
	.error.ntzcrmtextred {
		color: red;
		font-size: 18px
	}

	.ntzcrmtextbold {
		font-weight: 700;
		display: block
	}

	.tab-container {
		margin-top: 20px;
		padding: .7em 2em 1em;
		width: 1000px;
		border: 1px solid #ccd0d4;
		box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
		background: #fff;
		box-sizing: border-box
	}

	.mt-2 {
		margin-top: 20px
	}

	.mt-4 {
		margin-top: 50px
	}

	.ntzcrmdisabledmenu {
		pointer-events: none
	}

	.ntzcrmdisabledmenu:hover {
		background: none;
		color: #767676
	}

	.tab-content {
		display: none;
	}

	.tab-content.current {
		display: inherit
	}

	.ntz-global-nav-menu .ntz-global-nav-menu__tab.ntz-global-nav-menu__two {
		background: none;
		color: #0073aa;
		border-bottom: 2px solid #0073aa
	}
</style>
<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap">
	<h1>CRM Memberships Settings</h1>
	<div class="wrap about__container">
		<div class="about__header1">
			<form method="post" action="options.php">
				<nav class="ntz-tabs wp-clearfix" aria-label="Secondary menu">
					<a  href="<?php echo esc_url("#");?>" class="nav-tab nav-tab-active" data-tab="tab-1"><span class="dashicons dashicons-admin-generic"></span> General Settings </a>
					<a  href="<?php echo esc_url("#");?>" class="nav-tab" data-tab="partialview"><span class="dashicons dashicons-welcome-write-blog"></span>Partial View Settings</a>
					<a  href="<?php echo esc_url("#");?>" class="nav-tab" data-tab="tab-2"><span class="dashicons dashicons-cloud"></span> Salesforce Settings</a>
					<a  href="<?php echo esc_url("#");?>" class="nav-tab" data-tab="tab-3"><span class="dashicons dashicons-chart-area"></span> User Track Settings</a>
					<a  href="<?php echo esc_url("#");?>" class="nav-tab" data-tab="tab-4"><span class="dashicons dashicons-email"></span> Mail Template</a>
				</nav>
				<div class="tab-container">
					<div id="tab-1" class="tab-content current">
						<?php settings_fields("section");
						do_settings_sections("commontheme-options"); ?>
					</div>
					<div id="partialview" class="tab-content">
						<p>Allow the post detail page and display partially content.</p>
						<?php settings_fields("section");
						do_settings_sections("partialview-options"); ?>
					</div>
					<div id="tab-2" class="tab-content">
						<p>You must first install the CRM Memberships Salesforce extension on your Salesforce ORG in order for this plugin to sync with your Salesforce account.</p>
						<ol>
							<li>Please <a href="<?php echo esc_url("mailto:sales@netesenz.com"); ?>">Contact us</a> to get the latest CRM Memberships managed package for Salesforce.</li>
							<li>Once you receive the package, navigate to <a href="<?php echo esc_url("https://login.salesforce.com/"); ?>" target="_blank">https://login.salesforce.com/</a>.</li>
							<li>Log in with your Salesforce credentials if you are not logged in.</li>
							<li>Install and configure the updated CRM Memberships Salesforce extension on your Salesforce account. Please check the <a href="<?php echo esc_url("https://ntzapps.com/crm-memberships-salesforce-integration/"); ?>" target="_blank">documentation</a> for more info</li>
						</ol>
						<br>
						<p>The API key and token specified in this page must match with the ones you entered in your Salesforce ORG.</p>
						<?php settings_fields("section");
						do_settings_sections("salesforce-settings"); ?>
					</div>
					<div id="tab-3" class="tab-content">
						<?php settings_fields("section");
						do_settings_sections("usertracking-options"); ?>
					</div>
					<div id="tab-4" class="tab-content">
						<?php settings_fields("section");
						do_settings_sections("emailtemplate-options"); ?>
					</div>
					<div class="m-0 p-0">
						<?php submit_button(); ?>
					</div>
				</div>
				<?php // wp_nonce_field( 'ntzcrm-admin-post' ); 
				?>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('a.nav-tab').click(function() {
			var tab_id = $(this).attr('data-tab');
			$('a.nav-tab').removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$('.tab-content').removeClass('current');
			//		$(this).addClass('current');
			$("#" + tab_id).addClass('current');
		})

	});
</script>