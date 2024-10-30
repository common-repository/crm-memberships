<?php
if (function_exists('wp_enqueue_media')) {
	wp_enqueue_media();
} else {
	wp_enqueue_style('thickbox');
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
}
global $post;
$tags = ntzcrm_dbquery::_getMembershipTagsList();
$postTags = ntzcrm_dbquery::_getPostTagList($post->ID);

$isLoginRequired = get_post_meta($post->ID, "is_ntzcrm_login_required", true);
$accessIcon = get_post_meta($post->ID, "ntzcrm_access_icon", true);
$noAccessIcon = get_post_meta($post->ID, "ntzcrm_noaccess_icon", true);
$isPublication = get_post_meta($post->ID, "is_ntzcrm_publication", true);
$isFrontedPublication = get_post_meta($post->ID, "is_fronted_publication", true);

?>
<div style="padding:10px;background-color: white;border:1px solid #80808036;">
	<table class="form-table">

		<tr>
			<td>
				<div class="inline-edit-col" style="margin-top:1%">
					<label class="inline-edit-author">
						<span class="title">Is Publication</span>
						<select name="is_ntzcrm_publication" class="is_ntzcrm_publication">
							<option value="-1">— No Change —</option>
							<option <?php if (!empty($isPublication) && $isPublication == "yes") { ?> value="<?php esc_attr_e('yes');  ?>" selected <?php } else { ?> value="<?php esc_attr_e('yes');  ?>" <?php } ?>>Yes</option>
							<option <?php if (!empty($isPublication) && $isPublication == "no") { ?> value="<?php esc_attr_e('no');  ?>" selected <?php } else { ?> value="<?php esc_attr_e('no');  ?>" <?php } ?>>No</option>
						</select>
					</label>
				</div>
		</tr>
		<tr>
			<td>
				<h3>Access Permission</h3>
			</td>
		</tr>
		<tr>
			<td>
				<div class="inline-edit-col" style="margin-top:1%">
					<label class="inline-edit-author">
						<span class="title">Visitor Must Login to View this Page</span>
						<select name="is_<?php echo NTZCRMPRIFIX; ?>login_required" class="is_ntzcrm_publication">
							<option value="-1">— No Change —</option>
							<option <?php if (!empty($isLoginRequired) && $isLoginRequired == "yes") { ?> value="<?php esc_attr_e('yes');  ?>" selected <?php } else { ?> value="<?php esc_attr_e('yes');  ?>" <?php } ?>>Yes</option>
							<option <?php if (!empty($isLoginRequired) && $isLoginRequired == "no") { ?> value="<?php esc_attr_e('no');  ?>" selected <?php } else { ?> value="<?php esc_attr_e('no');  ?>" <?php } ?>>No</option>
						</select>
					</label>
				</div>
			</td>
		</tr>

		<tr style="display:none;">
			<td>
				<div class="inline-edit-col" style="margin-top:1%">
					<input type="hidden" name="ntz_crm_bulk_tag_action" value="<?php esc_attr_e('yes');  ?>">
				</div>
			</td>
		</tr>

		<tr>
			<td>
				<strong><?php esc_html_e(' Visitors must have at least one of the following tags to view this page:', NTZCRMPRIFIX); ?></strong>
				<br>
				<?php
				if (!empty($tags)) {
					$html = '
					<select class="select2" id="ntzcrm_select2_tags" name="posttag[]" multiple="multiple" placeholder=
					"Please select at least one tag." width="100%">';
					foreach ($tags as $tagId => $tagName) {
						$selected = (!empty($postTags) && in_array($tagId, $postTags)) ? ' selected="selected"' : '';
						$html .= '<option value="' .esc_attr($tagId). '"' . $selected . '>' . $tagName . '</option>';
					}
					$html .= '<select><br><small>Visitors without any of these tags will be redirected to the "Insufficient Permissions Page.</small>';
					echo $html;
				}
				?>
			</td>
		</tr>
		<tr>
			<td>
				<h3><?php esc_html_e('Product Icon', NTZCRMPRIFIX); ?></h3>
			</td>
		</tr>
		<tr>
			<td><?php esc_html_e('Enabled Icon', NTZCRMPRIFIX); ?></td>
			<td>
				<input id="ntzcrmaccessimgval" style="width: 80%;" type="text" name="ntzcrm_access_icon" size="60" value="<?php esc_attr_e($accessIcon);  ?>"><a href="<?php echo esc_url("#")?> id="ntzcrmaccess_upload">Upload</a>
			</td>
			<?php

			if (!empty($accessIcon)) { ?>
				<td>

					<img width="100px" id="ntzcrmaccessimg" src="<?php echo esc_url($accessIcon); ?>" height="100" width="300" />
				</td>
			<?php } ?>
		</tr>

		<tr>
			<td><?php esc_html_e('Disabled Icon', NTZCRMPRIFIX); ?></td>
			<td>
				<input style="width: 80%;" id="ntzcrmnoaccessimgval" type="text" name="ntzcrm_noaccess_icon" size="60" value="<?php esc_attr_e($noAccessIcon);  ?>">
				<a href="<?php echo esc_url("#"); ?>" id="ntzcrmnoaccess_upload">Upload</a>
			</td>
			<?php
			if (!empty($noAccessIcon)) { ?>
				<td>
					<img width="100px" id="ntzcrmnoaccessimg" src="<?php echo esc_url($noAccessIcon); ?>" height="<?php esc_attr_e('100');  ?>" width="<?php esc_attr_e('300');  ?>" />
				</td>
			<?php } ?>
		</tr>
		<tr>
			<td colspan="12">
				<div class="components-base-control__field mt-2">
					<h3 class="components-base-control__label" for="inspector-text-control-0"><?php esc_html_e('Display publication at home page', NTZCRMPRIFIX); ?></h3>
					<p>do you want to display the publication icons in a dashboard page or front page.</p>
					<input type="checkbox" name="is_fronted_publication" class="ntzcrmcheckbox" <?php if (!empty($isFrontedPublication) && $isFrontedPublication == "yes") { ?> value="<?php esc_attr_e('yes');  ?>" checked="checked" <?php } ?>>Yes
				</div>
			</td>
		</tr>
	</table>
</div>


<script>
	jQuery(document).ready(function($) {
		$('#ntzcrmaccess_upload').click(function(e) {
			e.preventDefault();
			var custom_uploader = wp.media({
					title: 'Enabled Icon',
					button: {
						text: 'Upload Image'
					},
					multiple: false // Set this to true to allow multiple files to be selected
				})
				.on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					$('#ntzcrmaccessimg').attr('src', attachment.url);
					$('#ntzcrmaccessimgval').val(attachment.url);

				})
				.open();
		});

		$(".ntzcrmcheckbox").click(function() {
			if ($(this).prop('checked') == true) {
				$(this).val("yes");
			} else {
				$(this).val("no");
			}
		});
	});

	jQuery(document).ready(function($) {
		$('#ntzcrmnoaccess_upload').click(function(e) {
			e.preventDefault();
			var custom_uploader = wp.media({
					title: 'Disabled Icon',
					button: {
						text: 'Upload Image'
					},
					multiple: false // Set this to true to allow multiple files to be selected
				})
				.on('select', function() {
					var attachment = custom_uploader.state().get('selection').first().toJSON();
					$('#ntzcrmnoaccessimg').attr('src', attachment.url);
					$('#ntzcrmnoaccessimgval').val(attachment.url);

				})
				.open();
		});
	});
</script>