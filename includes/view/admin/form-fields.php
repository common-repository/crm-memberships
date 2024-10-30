<?php
function ntzcrm_enable_log()
{
	if (get_option('ntzcrm_enable_log') == "yes") {
		echo '<input type="checkbox" name="ntzcrm_enable_log" id="ntzcrm_enable_log" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrm_enable_log" id="ntzcrm_enable_log" value="yes"/>';
	}
	echo "<i><small>Logs are generated in plugin's log folder.</small></i>";
}

function ntzcrm_enable_partial_view()
{
	if (get_option('ntzcrm_enable_partial_view') == "yes") {
		echo '<input type="checkbox" name="ntzcrm_enable_partial_view" id="ntzcrm_enable_partial_view" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrm_enable_partial_view" id="ntzcrm_enable_partial_view" value="yes"/>';
	}
	echo "<i><small>Partial content allow at post detail.</small></i>";
}

function ntzcrm_enable_partial_text_limit()
{
	echo '<input type="text" maxlength="5" name="ntzcrm_enable_partial_text_limit" id="wordlmt"  value="' . esc_attr(get_option('ntzcrm_enable_partial_text_limit')) . '" placeholder="Enter word limit"  onkeypress="return ntzIsNumber(event)"  size="60" />';
	echo "<i><lable>Set word limit to allow at post detail. Default word limit is 200.</lable></i>";
}

function ntzcrm_login_partial_view_text()
{
	$content = get_option('ntzcrm_login_partial_view_text');  // pH, pH_min
	wp_editor($content, 'ntzcrm_login_partial_view_text', array('theme_advanced_buttons1' => 'bold, italic, ul', "media_buttons" => false, "textarea_rows" => 8, "tabindex" => 8, "textarea_cols" => 8));
	//echo "<br/><small>To Do: Write instructions text</small>";
}

function ntzcrm_subscribe_partial_view_text()
{
	$content = get_option('ntzcrm_subscribe_partial_view_text');  // pH, pH_min
	wp_editor($content, 'ntzcrm_subscribe_partial_view_text', array('theme_advanced_buttons1' => 'bold, italic, ul', "media_buttons" => false, "textarea_rows" => 8, "tabindex" => 8, "textarea_cols" => 8));
	//echo "<br/><small>To Do: Write instructions text</small>";
}

function ntzcrm_subscribe_button_link()
{
 
	echo '<input type="text" maxlength="200" name="ntzcrm_subscribe_button_link" value="' . esc_attr(get_option('ntzcrm_subscribe_button_link')) . '" placeholder="Enter default plan link." size="60" />';
	echo "<i><lable>Enter default subscribe plan link. Ex. ".site_url('/')."products </lable></i>";
	//echo "<br/><small>To Do: Write instructions text</small>";
}


function ntzcrm_enable_change_password()
{
	if (get_option('ntzcrm_enable_change_password') == "yes") {
		echo '<input type="checkbox" name="ntzcrm_enable_change_password" id="ntzcrm_enable_change_password" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrm_enable_change_password" id="ntzcrm_enable_change_password" value="yes"/>';
	}
	echo "<i><small>Prompt change password on new user creation.</small></i>";
}

function ntzcrmEnableTosendCreatePasswordMailOnUserCreation()
{
	if (get_option('ntzcrmEnableTosendCreatePasswordMailOnUserCreation') == "yes") {
		echo '<input type="checkbox" name="ntzcrmEnableTosendCreatePasswordMailOnUserCreation" id="ntzcrmEnableTosendCreatePasswordMailOnUserCreation" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrmEnableTosendCreatePasswordMailOnUserCreation" id="ntzcrmEnableTosendCreatePasswordMailOnUserCreation" value="yes"/>';
	}
	echo "<i><small>Send email to create password on user creation.</small></i>";
}

function ntzcrmEnableTosendWelcomeMailOnUserCreation()
{
	if (get_option('ntzcrmEnableTosendWelcomeMailOnUserCreation') == "yes") {
		echo '<input type="checkbox" name="ntzcrmEnableTosendWelcomeMailOnUserCreation" id="ntzcrmEnableTosendWelcomeMailOnUserCreation" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrmEnableTosendWelcomeMailOnUserCreation" id="ntzcrmEnableTosendWelcomeMailOnUserCreation" value="yes"/>';
	}
	echo "<i><small>Send email to welcome mail on user creation.</small></i>";
}

function ntzcrm_disabled_post_tracking()
{
	if (get_option('ntzcrm_disabled_post_tracking') == "yes") {
		echo '<input type="checkbox" name="ntzcrm_disabled_post_tracking" id="ntzcrm_disabled_post_tracking" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrm_disabled_post_tracking" id="ntzcrm_disabled_post_tracking" value="yes"/>';
	}
	echo "<lable>Disable page view tracking</lable><p><i>Check the above checkbox to disable the user tracking in user profile page.</i></p>";
}

function ntzcrm_user()
{
	echo '<input type="text" name="ntzcrm_user" id="ntzcrm_user" value="' . esc_attr(get_option('ntzcrm_user')) . '" placeholder="Enter salesforce user" size="60" />';
}

function ntzcrm_password()
{
	echo '<input type="text" name="ntzcrm_password" id="ntzcrm_password" value="' . esc_attr(get_option('ntzcrm_password')) . '" placeholder="Enter salesforce password" size="60" />';
}

function ntzcrm_api_key()
{
	echo '<input type="password" name="ntzcrm_api_key" id="ntzcrm_api_key" value="' . esc_attr(get_option('ntzcrm_api_key')) . '" placeholder="Enter salesforce ntzcrm api key" size="60" /><br><small>Note: Your ntzcrm api key.</small>';
}

function ntzcrm_api_token()
{
	echo '<input type="password" name="ntzcrm_api_token" id="ntzcrm_api_token" value="' . esc_attr(get_option('ntzcrm_api_token')) . '" placeholder="Enter salesforce ntzcrm api token" size="60" /><br><small>Note: This unique token string is used to ensure the request is sent from your Salesforce ORG</small>';
}

function ntzcrm_infu_form_url()
{
	echo '<input type="text" name="ntzcrm_infu_form_url" id="ntzcrm_infu_form_url" value="' . esc_attr(get_option('ntzcrm_infu_form_url')) . '" placeholder="Enter infusion form url" size="60" />';
}

function ntzcrm_service_url()
{
	echo '<input type="text" name="ntzcrm_service_url" id="ntzcrm_service_url" value="' . esc_attr(get_option('ntzcrm_service_url')) . '" placeholder="Enter CRM Token Validation URL" size="60" />';
}


function ntzcrm_log_redirect_url()
{
	echo '<input type="text" name="ntzcrm_log_redirect_url" id="ntzcrm_log_redirect_url" value="' . esc_attr(get_option('ntzcrm_log_redirect_url')) . '" placeholder="Enter redirect page url after login." size="60" /><br><small>Default redirect url is ' . site_url("/crm-memberships-publications/") . '</small>';
}


function ntzcrm_logout_redirect_url()
{
	echo '<input type="text" name="ntzcrm_logout_redirect_url" id="ntzcrm_logout_redirect_url" value="' . esc_attr(get_option('ntzcrm_logout_redirect_url')) . '" placeholder="Enter redirect page url after logout." size="60" /><br><small>Default redirect url is ' . site_url("/crm-memberships-login/") . '</small>';
}

function ntzcrm_login_url()
{
	echo '<input type="text" name="ntzcrm_login_url" id="ntzcrm_login_url" value="' . esc_attr(get_option('ntzcrm_login_url')) . '" placeholder="Enter login url page." size="60" /><br><small>Default redirect url is ' . site_url("/crm-memberships-login/") . '</small>';
}

function ntzcrm_changepassword_url()
{
	echo '<input type="text" name="ntzcrm_changepassword_url" id="ntzcrm_changepassword_url" value="' . esc_attr(get_option('ntzcrm_changepassword_url')) . '" placeholder="Enter login url page." size="60" /><br><small>Default redirect url is ' . site_url("/crm-memberships-login/?action=changepassword") . '</small>';
}

function ntzcrm_opt_default_navlinks()
{
	if (get_option('ntzcrm_opt_default_navlinks') == "yes") {
		echo '<input type="checkbox" name="ntzcrm_opt_default_navlinks" id="ntzcrm_opt_default_navlinks" value="yes" checked/>';
	} else {
		echo '<input type="checkbox" name="ntzcrm_opt_default_navlinks" id="ntzcrm_opt_default_navlinks" value="yes"/>';
	}
}


function ntzcrm_publ_welcome_title()
{
	echo '<input type="text" name="ntzcrm_publ_welcome_title" id="ntzcrm_publ_welcome_title" value="' . esc_attr(get_option('ntzcrm_publ_welcome_title')) . '" placeholder="Enter Publication Page Welcome Title." size="60" />';
}


function ntzcrm_publ_welcome_text()
{
	echo '<textarea id="ntzcrm_publ_welcome_text" name="ntzcrm_publ_welcome_text" rows="6" cols="60" size="60" " placeholder="Enter Publication Page Welcome Text." >' . esc_attr(get_option('ntzcrm_publ_welcome_text')) . '</textarea>';
}

function ntzcrmCreatePasswordMailTemplate()
{
	$content = get_option('ntzcrmCreatePasswordMailTemplate');  // pH, pH_min
	wp_editor($content, 'ntzcrmCreatePasswordMailTemplate', array('theme_advanced_buttons1' => 'bold, italic, ul', "media_buttons" => false, "textarea_rows" => 8, "tabindex" => 8, "textarea_cols" => 8));
	echo "<br/><small>Please Use <b>#USERNAME#</b> for dynamic user name and <b>#CREATEPASSWORDLINK#</b> to add dynamic create password link.</small>";
}

function ntzcrm_resetpassword_mail_template()
{
	$content = get_option('ntzcrm_resetpassword_mail_template');  // pH, pH_min
	wp_editor($content, 'ntzcrm_resetpassword_mail_template', array('theme_advanced_buttons1' => 'bold, italic, ul', "media_buttons" => false, "textarea_rows" => 8, "tabindex" => 8, "textarea_cols" => 8));
	echo "<br/><small>Please Use <b>#USERNAME#</b> for dynamic  user name and <b>#RESTPASSWORDLINK#</b> to add dynamic reset password link.</small>";
}

function ntzcrm_welcome_mail_template()
{
	$content = get_option('ntzcrm_welcome_mail_template');  // pH, pH_min
	wp_editor($content, 'ntzcrm_welcome_mail_template', array('theme_advanced_buttons1' => 'bold, italic, ul', "media_buttons" => false, "textarea_rows" => 10, "tabindex" => 10, "textarea_cols" => 10));
	echo "<br/><small>Please Use <b>#USERNAME#</b> for dynamic  user name and <b>#LOGINBTN#</b> for dynamic login button .</small>";
}

function ntzcrm_infu_form_id()
{
	echo '<input type="text" name="ntzcrm_infu_form_id" id="ntzcrm_infu_form_id" value="' . esc_attr(get_option('ntzcrm_infu_form_id')) . '" placeholder="Enter infusion form id" size="60" />';
}

function ntzcrm_logo()
{
	if (function_exists('wp_enqueue_media')) {
		wp_enqueue_media();
	} else {
		wp_enqueue_style('thickbox');
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
	}
?>
	<input id="ntzcrmlogo" type="text" name="ntzcrm_logo" size="60" value="<?php echo esc_attr(get_option('ntzcrm_logo')); ?>">

	<a href="#" id="ntzcrmlogo_upload" class="button button-primary"><span class="dashicons dashicons-paperclip"></span> Upload</a>
	<?php
	$logo = (!empty(get_option('ntzcrm_logo'))) ? get_option('ntzcrm_logo') : '';
	if (!empty($logo)) { ?>
		</td>
		</tr>
		<tr>
			<th scope="row">Uploaded Logo</th>
			<td>
				<img id="ntzcrmlogoimg" src="<?php echo esc_url($logo); ?>" width="300" />
			<?php } ?>

			<script>
				jQuery(document).ready(function($) {
					$('#ntzcrmlogo_upload').click(function(e) {
						e.preventDefault();
						var custom_uploader = wp.media({
								title: 'Banner Logo',
								button: {
									text: 'Upload Image'
								},
								multiple: false // Set this to true to allow multiple files to be selected
							})
							.on('select', function() {
								var attachment = custom_uploader.state().get('selection').first().toJSON();
								$('#ntzcrmlogoimg').attr('src', attachment.url);
								$('#ntzcrmlogo').val(attachment.url);

							})
							.open();
					});
				});
			</script>
		<?php }
