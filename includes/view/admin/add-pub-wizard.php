<?php
if (function_exists('wp_enqueue_media')) {
	wp_enqueue_media();
} else {
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}


$postPermission = new NtzCrmPostPermission();
$request = $postPermission->ntzcrmRequests();
$postTitle = $noAccessIcon = $accessIcon = $isLoginRequired = $pid = "";
if (isset($request['pid']) && is_numeric($request['pid'])) {
	$pid = sanitize_text_field(trim($request['pid']));
	$post = get_post($pid);
	$postTitle = (!empty($post->post_title)) ? trim($post->post_title)  : "";
	$isLoginRequired = get_post_meta($pid, "is_" . NTZCRMPRIFIX . "login_required", true);
	$accessIcon = get_post_meta($pid, NTZCRMPRIFIX . "access_icon", true);
	$noAccessIcon = get_post_meta($pid, NTZCRMPRIFIX . "noaccess_icon", true);
}
?>
<style>
	.error.ntzcrmtextred {
		color: red;
		font-size: 18px
	}

	.ntzcrmtextbold {
		font-weight: 700;
		display: block
	}

	.ntzcrmpublicationcard {
		/* position: absolute; */
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

	.ntz-global-nav-menu .ntz-global-nav-menu__tab.ntz-global-nav-menu__fou {
		background: none;
		color: #0073aa;
		border-bottom: 2px solid #0073aa
	}
</style>
<?php include_once 'ntzcrm-header.php'; ?>
<div class="ntz-wrap wrap">
	<h1><?php esc_html_e('Publication Wizard'); ?></h1>
	<div class="wrap-container">
		<div class="about__header1">

			<nav class="ntz-tabs wp-clearfix" aria-label="Secondary menu">
				<?php $activeMenu = (isset($request['page']) && !isset($request['step']) && $request['page'] == "add-publication-wizard") ? "nav-tab-active" : "";

				$key = "step";
				$widzUrl = preg_replace('~(\?|&)' . $key . '=[^&]*~', '$1', trim($_SERVER['QUERY_STRING']));
				?>
				<a href="<?php echo esc_url(admin_url() . "admin.php?" . $widzUrl); ?>" class="nav-tab <?php echo $activeMenu; ?>"><span class="dashicons dashicons-welcome-add-page"></span> <?php esc_html_e('Publication Details', NTZCRMPRIFIX); ?> </a>
				<?php $activeMenu = (isset($request['page']) && $request['page'] == "add-publication-wizard" && isset($request['step']) && $request['step'] == "2") ? "nav-tab-active" : "";
				$disabledClass = (!isset($request['pid'])) ? "ntzcrmdisabledmenu" : ""; ?>
				<a <?php if (isset($request['pid'])) { ?> href="<?php echo esc_url(admin_url() . "admin.php?page=add-publication-wizard&pid=" . trim($request['pid']) . "&step=2"); ?>" <?php } ?> class="nav-tab <?php echo $activeMenu . " " . $disabledClass; ?>" aria-current="page"><span class="dashicons dashicons-tag"></span> <?php esc_html_e('Access Tags', NTZCRMPRIFIX); ?></a>

				<?php $activeMenu = (isset($request['page']) && $request['page'] == "add-publication-wizard" && isset($request['step']) && $request['step'] == "3") ? "nav-tab-active" : "";
				$disabledClass = (!isset($request['pid'])) ? "ntzcrmdisabledmenu" : ""; ?>
				<a <?php if (isset($request['pid'])) { ?> href="<?php echo esc_url(admin_url() . "admin.php?page=add-publication-wizard&pid=" . trim($request['pid']) . "&step=3"); ?>" <?php } ?> class="nav-tab <?php echo $activeMenu . " " . $disabledClass; ?>" aria-current="page"><span class="dashicons dashicons-media-text"></span> <?php esc_html_e('Publication Shortcode', NTZCRMPRIFIX); ?> </a>


			</nav>
		</div>
		<?php if (isset($request['err'])) { ?><div class="mt-2 errorsection"><label class="error ntzcrmtextred">
				<?php echo $request['err']; ?></label> <a style="float: right;" href="<?php echo esc_url("javascript::void(0)")?>" id="ntzcrmclose" class="button">X</a></div>
		<?php } ?>
		<div class="tab-container ntzcrmpublicationcard">
			<div class="publication-tab-content">
				<?php if (!isset($request['step']) || (isset($request['step']) && $request['step'] == "1")) { ?>
					<form action="" method="POST">
						<div class="components-base-control__field">
							<h3 class="components-base-control__label" for="inspector-text-control-0"><?php esc_html_e('Publication Name', NTZCRMPRIFIX); ?></h3>
							<input class="components-text-control__input" type="text" id="inspector-text-control-0" name="title" placeholder="Please enter the publication name. (E.g. Ultimate Cookery Course)" value="<?php echo esc_attr($postTitle); ?>" required="required">
						</div>
						<div class="components-base-control__field mt-2">
							<h3 class="components-base-control__label" for="inspector-text-control-0"> <?php esc_html_e('Publication Icons', NTZCRMPRIFIX); ?></h3>
							<div class="custom-upload-box">
								<label class="ntzcrmtextbold"><?php esc_html_e('Enabled Icon', NTZCRMPRIFIX); ?></label>
								<div class="custom-upload-input">
									<input id="ntzcrmaccessimgval" type="text" name="<?php echo NTZCRMPRIFIX; ?>access_icon" size="60" value="<?php echo esc_attr($accessIcon); ?>">
									<a href="<?php echo esc_url("#"); ?>" class="upload-button button button-primary" id="ntzcrmaccess_upload"><?php esc_html_e('Upload', NTZCRMPRIFIX); ?></a>
								</div>
							</div>
							<div class="custom-upload-box mt-2">
								<label class="ntzcrmtextbold"><?php esc_html_e('Disabled Icon', NTZCRMPRIFIX); ?></label>
								<div class="custom-upload-input">
									<input id="ntzcrmnoaccessimgval" type="text" name="<?php echo NTZCRMPRIFIX; ?>noaccess_icon" size="60" value="<?php echo esc_attr($noAccessIcon); ?>">
									<a href="<?php echo esc_url("#"); ?>" class="upload-button button button-primary" id="ntzcrmnoaccess_upload"><?php esc_html_e('Upload', NTZCRMPRIFIX); ?></a>
								</div>
							</div>
						</div> 
						<div class="components-base-control__field mt-2">
							<h3 class="components-base-control__label" for="inspector-text-control-0"> <?php esc_html_e(' Visitors must login to view this page.', NTZCRMPRIFIX); ?></h3>
							<input type="radio" name="is_<?php echo NTZCRMPRIFIX; ?>login_required" value="yes" <?php if (!empty($isLoginRequired) && $isLoginRequired == "yes") { ?> checked="checked" <?php } ?>>Yes
							<input type="radio" name="is_<?php echo NTZCRMPRIFIX; ?>login_required" value="no" <?php if (!empty($isLoginRequired) && $isLoginRequired == "no") { ?> checked="checked" <?php } ?>> No
						</div> 
						<div class="components-base-control__field mt-2">
							<?php $redirectUrl = admin_url() . "admin.php?" . $_SERVER['QUERY_STRING']; ?>
							<input type="hidden" name="_wp_http_referer" value="<?php echo $redirectUrl; ?>">
							<input type="hidden" name="next_step" value="2">
							<input type="hidden" name="pid" value="<?php echo $pid; ?>">
							<input type="submit" name="save" class="dm-custom-button button button-primary" value="<?php echo esc_attr("Next"); ?>">
						</div>
						<?php wp_nonce_field('ntzcrm-pub-wizard-post'); ?>
					</form>
				<?php  } ?>
				<?php if (isset($request['step']) && $request['step'] == "2") { ?>
					<p><?php esc_html_e('Next you will need to assign an Access Tag for the publication you selected. Access Tag determines which content area website vistors will have access to'); ?></p>
					<form action="" method="POST" style="width:80%;display: inline-block;" id="ntzcrm-tag">
						<div class="components-base-control__field">
							<h3 class="components-base-control__label" for="inspector-text-control-0">Add New Access Tag</h3>
							<p><i><?php esc_html_e('Access Tags are used to hide or show different content on page or post. If you already have a tag, you can skip this create section and select that tag in the below section.'); ?></i></p>

							<div class="add_new_access_tag">
								<input placeholder="Please enter the tag name. (E.g. Ultimate Cookery Course Access Tag)" id="tag_name" type="text" name="tag_name" size="60" value="" style="margin: 1% 0;" required />
								<input placeholder="Please Enter Subscription Url" id="plan_link" type="text" name="plan_link" size="60" value="" style="margin: 1% 0;"  />
								<span id="loader" class="add_new_access_tag_load"><img src="<?php echo esc_url(NTZCRM_PLUGIN_URL . "images/3.gif") ?>" alt=""> </span>
								<br>
								<input type="submit" style="position:relative;" name="save" id="ntzcrm-tag-submit" class="button button-primary" value="Add/Edit Tag">
							</div>

						</div>
						<input name="ntzcrm-pub-wizard-post" type="hidden" value="<?php echo wp_create_nonce('ntzcrm-pub-wizard-post'); ?>" />
						<?php wp_nonce_field('ntzcrm-pub-addtag-post'); ?>
					</form> </br></br></br>
					<form action="" method="POST">
						<div class="components-base-control__field">
							<h3 class="components-base-control__label" for="inspector-text-control-0"><?php esc_html_e('Assign Access Tags', NTZCRMPRIFIX); ?></h3>
							<p><i><?php esc_html_e('Select a tag for the publication.'); ?></i></p>
							<?php
							$tags = ntzcrm_dbquery::_getMembershipTagsList();
							$postTags = ntzcrm_dbquery::_getPostTagList($pid);
							$html = '<select class="select2" id="nz_select2_tags" name="posttag[]" multiple="multiple" placeholder=
							"Please select at least one tag." >';
							if (!empty($tags)) {
								foreach ($tags as $tagId => $tagName) {
									$selected = (!empty($postTags) && in_array($tagId, $postTags)) ? ' selected="selected"' : '';
									$html .= '<option value="' . $tagId . '"' . $selected . '>' . $tagName . '</option>';
								}
							} else {
								$html .= '<option value="">Tag Not found.</option>';
							}
							$html .= '<select><br><small>Visitors without any of these tags will be redirected to the "Insufficient Permissions" page. You can define new Access Tags from the below section. </small>';
							echo $html;  ?>
						</div>
						<div class="components-base-control__field mt-2">
							<?php $redirectUrl = admin_url() . "admin.php?" . $_SERVER['QUERY_STRING']; ?>
							<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($redirectUrl); ?>">
							<input type="hidden" name="step" value="<?php echo esc_attr($request['step']); ?>">
							<input type="hidden" name="next_step" value="<?php echo 3; ?>">
							<input type="hidden" name="pid" value="<?php echo esc_attr($pid); ?>">
							<input type="submit" name="save" id="ntzcrm-submit" class="dm-custom-button button button-primary" value="<?php echo esc_attr("Next"); ?>">
						</div>
						<?php wp_nonce_field('ntzcrm-pub-tag-post'); ?>
					</form>
				<?php  } ?>
				<?php if (isset($request['step']) && $request['step'] == "3") { ?>
					<div class="components-base-control__field">
						<h3 class="components-base-control__label" for="inspector-text-control-0"><?php esc_html_e('Publication Short code', NTZCRMPRIFIX); ?></h3>
						<p><?php esc_html_e('You can use this shortcode to display the publication icons in any page. You can also get this shortcode later from Publications menu.'); ?></p>

						<div class="add_new_access_tag">
							<input type="text" value="<?php echo "[" . NTZCRMPRIFIX . "icon post_id='$pid']"; ?>" class="components-text-control__input" id="ntzcrmClipInput">
							<div class="ntzcrmtooltip">
								<button class="button button-primary" onclick="ntzcrmclipboard()">
									<span class="tooltiptext" id="ntzcrmTooltip">Copy</span>
								</button>
							</div>
						</div>
					</div><br><br>
					<form action="" method="POST">
						<div class="components-base-control__field mt-2">
							<h3 class="components-base-control__label" for="inspector-text-control-0"><?php esc_html_e('Display publication in Publications home page.'); ?></h3>
							<p><?php esc_html_e('This plugin comes with a default home page for all the publications. It will automatically lists all publications using the shortcode [ntzcrm_publications]. Do you want to display the publication icons in <i>CRM Memberships – Publications</i> home page?'); ?> </p>
							<?php $isFrontedPublication = get_post_meta($pid, "is_fronted_publication", true);  ?>
							<input type="checkbox" name="is_fronted_publication" class="ntzcrmcheckbox" <?php if (!empty($isFrontedPublication) && $isFrontedPublication == "yes") { ?> value="yes" checked="checked" <?php } ?>><?php esc_html_e('Yes'); ?>
						</div>
						<div class="components-base-control__field mt-2">
							<?php $redirectUrl = admin_url() . "admin.php?" . $_SERVER['QUERY_STRING']; ?>
							<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url($redirectUrl); ?>">
							<input type="hidden" name="step" value="<?php echo esc_attr($request['step']); ?>">
							<input type="hidden" name="next_step" value="<?php echo esc_attr("3"); ?>">
							<input type="hidden" name="pid" value="<?php echo esc_attr($pid); ?>">
							<input type="submit" name="save" class="dm-custom-button button button-primary" value="<?php echo esc_attr("Finish"); ?>">
						</div>
						<?php wp_nonce_field('ntzcrm-pub-gallery-post'); ?>
					</form>
				<?php  } ?>
			</div>
		</div>
	</div>
</div>
